<?php
session_start();

// Configuration
define('DATA_DIR', __DIR__ . '/../data/');
define('CACHE_DIR', __DIR__ . '/../cache/');
define('CONFIG_FILE', DATA_DIR . 'config.json');

// Ensure directories exist
if (!is_dir(DATA_DIR)) mkdir(DATA_DIR, 0755, true);
if (!is_dir(CACHE_DIR)) mkdir(CACHE_DIR, 0755, true);

/**
 * Get default site configuration
 */
function get_default_config() {
    return [
        'siteName' => 'ThaiDeals',
        'siteFavicon' => '/favicon.ico',
        'themeColor' => '#4f46e5',
        'themeName' => 'indigo',
        'categories' => [],
        'keywords' => [],
        'cloakingBaseUrl' => 'https://goeco.mobi/?token=QlpXZyCqMylKUjZiYchwB',
        'cloakingToken' => 'QlpXZyCqMylKUjZiYchwB',
        'flashSaleEnabled' => true,
        'aiReviewsEnabled' => false,
        'prefixWordsEnabled' => true,
        'prefixWords' => ['ถูกที่สุด', 'ลดราคา', 'ส่วนลดพิเศษ', 'ขายดี', 'แนะนำ', 'คุ้มสุดๆ', 'ราคาดี', 'โปรโมชั่น', 'สุดคุ้ม', 'ห้ามพลาด', 'ราคาถูก', 'ดีลเด็ด', 'ลดแรง', 'ยอดนิยม', 'ราคาพิเศษ']
    ];
}

/**
 * Get site configuration
 */
function get_config() {
    $default_config = get_default_config();
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
function get_all_products($keyword = '', $category_name = '') {
    $config = get_config();
    $cache_key = md5($keyword . $category_name . serialize($config['categories']));
    $cache_file = CACHE_DIR . $cache_key . '.json';

    if (file_exists($cache_file) && (time() - file_mtime($cache_file) < 3600)) {
        return json_decode(file_get_contents($cache_file), true);
    }

    $products = [];
    $csv_files_to_read = [];

    if ($category_name) {
        foreach ($config['categories'] as $cat) {
            if ($cat['name'] === $category_name && !empty($cat['csvFile'])) {
                $path = DATA_DIR . $cat['csvFile'];
                if (file_exists($path)) $csv_files_to_read[] = $path;
            }
        }
    } else {
        $main_csv_path = DATA_DIR . 'main.csv';
        if (file_exists($main_csv_path)) $csv_files_to_read[] = $main_csv_path;
        foreach ($config['categories'] as $cat) {
            if (!empty($cat['csvFile'])) {
                $path = DATA_DIR . $cat['csvFile'];
                if (file_exists($path) && !in_array($path, $csv_files_to_read)) $csv_files_to_read[] = $path;
            }
        }
    }

    foreach ($csv_files_to_read as $file) {
        if (($handle = fopen($file, "r")) !== FALSE) {
            $header = fgetcsv($handle);
            if ($header && strpos($header[0], "\xEF\xBB\xBF") === 0) $header[0] = substr($header[0], 3);

            while (($data = fgetcsv($handle)) !== FALSE) {
                if (!$header || count($header) !== count($data)) continue;
                $row = array_combine($header, $data);
                $products[] = [
                    'product_id' => $row['id'] ?? $row['product_id'] ?? uniqid(),
                    'product_name' => $row['name'] ?? $row['product_name'] ?? 'N/A',
                    'product_price' => $row['price'] ?? $row['product_price'] ?? 0,
                    'product_discounted' => $row['original_price'] ?? $row['product_discounted'] ?? 0,
                    'product_discounted_percentage' => $row['discount'] ?? $row['product_discounted_percentage'] ?? 0,
                    'product_image' => $row['image'] ?? $row['product_image'] ?? '',
                    'tracking_link' => $row['url'] ?? $row['tracking_link'] ?? '#',
                    'category_name' => $row['category'] ?? $row['category_name'] ?? 'N/A'
                ];
            }
            fclose($handle);
        }
    }

    foreach ($products as &$p) {
        if ($config['prefixWordsEnabled'] && !empty($config['prefixWords'])) {
            $p['product_name_display'] = $config['prefixWords'][array_rand($config['prefixWords'])] . ' ' . $p['product_name'];
        } else {
            $p['product_name_display'] = $p['product_name'];
        }
        if (!empty($config['cloakingBaseUrl'])) {
            $p['cloaked_url'] = $config['cloakingBaseUrl'] . '&url=' . urlencode($p['tracking_link']) . '&source=api_product';
        } else {
            $p['cloaked_url'] = $p['tracking_link'];
        }
    }

    if ($keyword) {
        $products = array_filter($products, fn($p) => stripos($p['product_name'], $keyword) !== false);
    }

    file_put_contents($cache_file, json_encode(array_values($products)));
    return array_values($products);
}

/**
 * Format price for display
 */
function format_price($price) {
    return '฿' . number_format((float)preg_replace('/[^0-9.]/', '', $price));
}

/**
 * Generate Random Reviews
 */
function get_random_reviews($count = 5) {
    $names = ['วิชัย ค.', 'สมหญิง ข.', 'สมชาย ก.', 'ประเสริฐ พ.', 'มาลี ส.'];
    $comments = ['สินค้าคุณภาพดีมากครับ', 'ได้รับสินค้าแล้วค่ะ แพ็คมาอย่างดี', 'คุณภาพพอใช้ได้ครับ', 'สินค้าดีมากค่ะ คุ้มค่า', 'ส่งไวมากครับ สินค้าคุณภาพดี'];
    $reviews = [];
    for ($i = 0; $i < $count; $i++) {
        $reviews[] = ['name' => $names[array_rand($names)], 'rating' => rand(4, 5), 'comment' => $comments[array_rand($comments)], 'date' => date('Y-m-d', time() - rand(0, 365) * 86400)];
    }
    return $reviews;
}

/**
 * Generate Random Price Comparison
 */
function get_price_comparison($base_price) {
    $shops = [['name' => 'ShopA Official', 'badge' => 'ร้านแนะนำ'], ['name' => 'BestPrice Store'], ['name' => 'MegaDeal Shop'], ['name' => 'ValuePlus Mall'], ['name' => 'SuperSave Outlet']];
    $comparison = [];
    $base_val = (float)preg_replace('/[^0-9.]/', '', $base_price);
    foreach ($shops as $shop) {
        $comparison[] = ['shop_name' => $shop['name'], 'badge' => $shop['badge'] ?? '', 'rating' => number_format(4 + (rand(0, 10) / 10), 1), 'price' => $base_val + rand(-50, 50)];
    }
    usort($comparison, fn($a, $b) => $a['price'] <=> $b['price']);
    return $comparison;
}

/**
 * Authentication
 */
function is_logged_in() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

function login($username, $password) {
    if ($username === 'admin' && $password === 'sofaraway') {
        $_SESSION['admin_logged_in'] = true;
        return true;
    }
    return false;
}

function logout() {
    unset($_SESSION['admin_logged_in']);
    session_destroy();
}

/**
 * Clear cache
 */
function clear_cache() {
    $files = glob(CACHE_DIR . '*');
    foreach ($files as $file) {
        if (is_file($file)) unlink($file);
    }
}
?>
