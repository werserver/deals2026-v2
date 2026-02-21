<?php
// Path to data files
define('CONFIG_FILE', __DIR__ . '/../data/config.json');
define('DATA_DIR', __DIR__ . '/../data/');

// Load configuration
function get_config() {
    $default_config = [
        'siteName' => 'ThaiDeals',
        'categories' => [],
        'keywords' => [],
        'categoryCsvMap' => [],
        'categoryCsvFileNames' => [],
        'themeColor' => '#ef4444',
        'dataSource' => 'csv',
        'csvFileName' => 'main.csv'
    ];

    if (!file_exists(CONFIG_FILE)) {
        return $default_config;
    }
    $json = file_get_contents(CONFIG_FILE);
    $config = json_decode($json, true) ?: [];
    return array_merge($default_config, $config);
}

// Save configuration
function save_config($config) {
    if (!is_dir(DATA_DIR)) {
        mkdir(DATA_DIR, 0755, true);
    }
    return file_put_contents(CONFIG_FILE, json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

// Parse CSV file
function parse_csv($filename) {
    $filepath = DATA_DIR . $filename;
    if (!file_exists($filepath)) return [];
    
    $rows = [];
    if (($handle = fopen($filepath, "r")) !== FALSE) {
        $header = fgetcsv($handle, 1000, ",");
        if (!$header) {
            fclose($handle);
            return [];
        }
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            if (count($header) == count($data)) {
                $rows[] = array_combine($header, $data);
            }
        }
        fclose($handle);
    }
    return $rows;
}

// Get all products from main CSV and category CSVs
function get_all_products($keyword = '', $category = '') {
    $config = get_config();
    $products = [];
    
    if ($category) {
        // Load category specific CSV if exists
        $cat_filename = $category . '.csv';
        if (file_exists(DATA_DIR . $cat_filename)) {
            $products = parse_csv($cat_filename);
        } else {
            // Fallback to main.csv and filter by category name
            $main_products = parse_csv('main.csv');
            $products = array_filter($main_products, function($p) use ($category) {
                return isset($p['category_name']) && $p['category_name'] == $category;
            });
        }
    } else {
        // Load main CSV for index
        $products = parse_csv('main.csv');
    }
    
    // Filter by keyword
    if ($keyword) {
        $products = array_filter($products, function($p) use ($keyword) {
            $search_text = ($p['product_name'] ?? '') . ' ' . ($p['category_name'] ?? '');
            return mb_stripos($search_text, $keyword) !== false;
        });
    }
    
    return array_values($products);
}

function format_price($price, $currency = '฿') {
    if (!is_numeric($price)) return $currency . $price;
    return $currency . number_format((float)$price, 0);
}

function slugify($text) {
    return strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $text)));
}
?>
