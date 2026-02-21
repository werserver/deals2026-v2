<?php
session_start();

// Configuration
define('DATA_DIR', __DIR__ . '/../data');
define('CACHE_DIR', __DIR__ . '/../cache');
define('CONFIG_FILE', DATA_DIR . '/config.json');
define('MAIN_CSV', DATA_DIR . '/main.csv');

// Ensure directories exist
if (!is_dir(DATA_DIR)) mkdir(DATA_DIR, 0755, true);
if (!is_dir(CACHE_DIR)) mkdir(CACHE_DIR, 0755, true);

/**
 * Get site configuration
 */
function get_config() {
    $default_config = [
        'siteName' => 'ThaiDeals',
        'favicon' => '/favicon.ico',
        'themeColor' => '#4f46e5', // Default Indigo
        'categories' => [],
        'keywords' => [],
        'categoryCsvFileNames' => [],
        'cloakingBaseUrl' => 'https://goeco.mobi/?token=QlpXZyCqMylKUjZiYchwB',
        'cloakingToken' => 'QlpXZyCqMylKUjZiYchwB',
        'flashSaleEnabled' => true,
        'aiReviewsEnabled' => false,
        'prefixWordsEnabled' => true,
        'prefixWords' => ['ถูกที่สุด', 'ลดราคา', 'ส่วนลดพิเศษ', 'ขายดี', 'แนะนำ', 'คุ้มสุดๆ', 'ราคาดี', 'โปรโมชั่น', 'สุดคุ้ม', 'ห้ามพลาด', 'ราคาถูก', 'ดีลเด็ด', 'ลดแรง', 'ยอดนิยม', 'ราคาพิเศษ']
    ];

    if (!file_exists(CONFIG_FILE)) {
        file_put_contents(CONFIG_FILE, json_encode($default_config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        return $default_config;
    }
    
    $json = file_get_contents(CONFIG_FILE);
    $config = json_decode($json, true) ?: [];
    return array_merge($default_config, $config);
}

/**
 * Save site configuration
 */
function save_config($config) {
    clear_cache();
    return file_put_contents(CONFIG_FILE, json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

/**
 * Get all products with caching and flexible CSV mapping
 */
function get_all_products($keyword = '', $category = '') {
    $csv_file = MAIN_CSV;
    if ($category) {
        $cat_file = DATA_DIR . '/' . $category . '.csv';
        if (file_exists($cat_file)) {
            $csv_file = $cat_file;
        }
    }

    if (!file_exists($csv_file)) return [];

    // Simple Caching based on file modification time
    $cache_key = md5($csv_file);
    $cache_file = CACHE_DIR . '/' . $cache_key . '.cache';
    $file_mtime = filemtime($csv_file);

    $products = [];
    if (file_exists($cache_file) && filemtime($cache_file) > $file_mtime) {
        $products = json_decode(file_get_contents($cache_file), true);
    } else {
        if (($handle = fopen($csv_file, "r")) !== FALSE) {
            $header = fgetcsv($handle);
            
            // Clean BOM if exists
            if ($header && strpos($header[0], "\xEF\xBB\xBF") === 0) {
                $header[0] = substr($header[0], 3);
            }

            while (($data = fgetcsv($handle)) !== FALSE) {
                if (!$header || count($header) !== count($data)) continue;
                $row = array_combine($header, $data);
                
                // Map flexible CSV structure to internal format
                $products[] = [
                    'product_id' => $row['id'] ?? $row['product_id'] ?? '',
                    'product_name' => $row['name'] ?? $row['product_name'] ?? '',
                    'product_price' => $row['price'] ?? $row['product_price'] ?? 0,
                    'product_discounted' => $row['original_price'] ?? $row['product_discounted'] ?? '',
                    'product_discounted_percentage' => $row['discount'] ?? $row['product_discounted_percentage'] ?? '',
                    'product_image' => $row['image'] ?? $row['product_image'] ?? '',
                    'tracking_link' => $row['url'] ?? $row['tracking_link'] ?? '#',
                    'category_name' => $row['category'] ?? $row['category_name'] ?? ''
                ];
            }
            fclose($handle);
        }
        // Save to cache
        file_put_contents($cache_file, json_encode($products));
    }

    // Apply Prefix Words and URL Cloaking
    $config = get_config();
    foreach ($products as &$p) {
        // Prefix Words
        if ($config['prefixWordsEnabled'] && !empty($config['prefixWords'])) {
            $random_prefix = $config['prefixWords'][array_rand($config['prefixWords'])];
            $p['product_name_display'] = "{$random_prefix} {$p['product_name']}";
        } else {
            $p['product_name_display'] = $p['product_name'];
        }

        // URL Cloaking
        if (!empty($config['cloakingBaseUrl']) && !empty($config['cloakingToken'])) {
            $encoded_url = urlencode($p['tracking_link']);
            $p['cloaked_url'] = "{$config['cloakingBaseUrl']}&url={$encoded_url}&source=api_product";
        } else {
            $p['cloaked_url'] = $p['tracking_link'];
        }
    }

    // Filter by keyword and category (if not already filtered by file)
    if ($keyword || $category) {
        $products = array_filter($products, function($p) use ($keyword, $category) {
            $match_keyword = true;
            if ($keyword) {
                $match_keyword = (stripos($p['product_name'], $keyword) !== false) || (stripos($p['category_name'], $keyword) !== false);
            }
            
            $match_category = true;
            if ($category) {
                $match_category = ($p['category_name'] == $category);
            }
            
            return $match_keyword && $match_category;
        });
    }

    return array_values($products);
}

/**
 * Format price for display
 */
function format_price($price) {
    if (is_numeric($price)) {
        return '฿' . number_format((float)$price, 0);
    }
    return $price;
}

/**
 * Authentication check
 */
function is_logged_in() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

/**
 * Clear cache
 */
function clear_cache() {
    $files = glob(CACHE_DIR . '/*');
    foreach ($files as $file) {
        if (is_file($file)) unlink($file);
    }
}
?>
