<?php
// Path to data files
define('CONFIG_FILE', __DIR__ . '/../data/config.json');
define('DATA_DIR', __DIR__ . '/../data/');

// Load configuration
function get_config() {
    if (!file_exists(CONFIG_FILE)) {
        return [
            'siteName' => 'ThaiDeals',
            'categories' => [],
            'keywords' => [],
            'categoryCsvMap' => []
        ];
    }
    $json = file_get_contents(CONFIG_FILE);
    return json_decode($json, true) ?: [];
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
    
    // Load main CSV
    $main_products = parse_csv('main.csv');
    $products = array_merge($products, $main_products);
    
    // Load category CSVs if needed
    if ($category && isset($config['categoryCsvMap'][$category])) {
        $cat_products = parse_csv($config['categoryCsvMap'][$category]);
        $products = array_merge($products, $cat_products);
    } elseif (!$category) {
        // If no specific category, maybe load some from all categories? 
        // For simplicity, let's just use main.csv as the primary source for index
    }
    
    // Filter by category
    if ($category) {
        $products = array_filter($products, function($p) use ($category) {
            return isset($p['category_name']) && $p['category_name'] == $category;
        });
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
    return $currency . number_format((float)$price, 0);
}

function slugify($text) {
    return strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $text)));
}
?>
