<?php
session_start();

// Configuration
define("DATA_DIR", __DIR__ . "/../data");
define("CACHE_DIR", __DIR__ . "/../cache");
define("CONFIG_FILE", DATA_DIR . "/config.json");
define("MAIN_CSV", DATA_DIR . "/main.csv");

// Ensure directories exist
if (!is_dir(DATA_DIR)) mkdir(DATA_DIR, 0755, true);
if (!is_dir(CACHE_DIR)) mkdir(CACHE_DIR, 0755, true);

/**
 * Get site configuration
 */
function get_config() {
    $default_config = [
        'siteName' => 'ThaiDeals',
        'siteFavicon' => '/favicon.ico',
        'themeColor' => '#ff6b00',
        'themeName' => 'orange',
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
function get_all_products($keyword = '', $category_name = '') {
    $config = get_config();
    $cache_key = md5($keyword . $category_name . serialize($config['categories']));
    // FIX: Added "/" separator between CACHE_DIR and cache_key
    $cache_file = CACHE_DIR . '/' . $cache_key . '.json';

    // Cache for 24 hours (86400 seconds) for better performance
    if (file_exists($cache_file) && (time() - filemtime($cache_file) < 86400)) {
        return json_decode(file_get_contents($cache_file), true);
    }

    $products = [];
    $csv_files_to_read = [];

    if ($category_name) {
        foreach ($config['categories'] as $cat) {
            $cat_name = is_array($cat) ? ($cat['name'] ?? '') : $cat;
            if ($cat_name === $category_name) {
                $csv_file = is_array($cat) ? ($cat['csvFile'] ?? '') : '';
                if (!empty($csv_file)) {
                    $path = DATA_DIR . '/' . $csv_file;
                    if (file_exists($path)) $csv_files_to_read[] = $path;
                }
            }
        }
        // If no specific CSV for category, fall back to main.csv
        if (empty($csv_files_to_read)) {
            $main_csv_path = DATA_DIR . '/main.csv';
            if (file_exists($main_csv_path)) $csv_files_to_read[] = $main_csv_path;
        }
    } else {
        $main_csv_path = DATA_DIR . '/main.csv';
        if (file_exists($main_csv_path)) $csv_files_to_read[] = $main_csv_path;
        foreach ($config['categories'] as $cat) {
            $csv_file = is_array($cat) ? ($cat['csvFile'] ?? '') : '';
            if (!empty($csv_file)) {
                $path = DATA_DIR . '/' . $csv_file;
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
                    'product_id'                   => $row['id'] ?? $row['product_id'] ?? uniqid(),
                    'product_name'                 => $row['name'] ?? $row['product_name'] ?? 'N/A',
                    'product_price'                => $row['price'] ?? $row['product_price'] ?? 0,
                    'product_discounted'           => $row['original_price'] ?? $row['product_discounted'] ?? 0,
                    'product_discounted_percentage'=> $row['discount'] ?? $row['product_discounted_percentage'] ?? 0,
                    'product_image'                => $row['image'] ?? $row['product_image'] ?? '',
                    'tracking_link'                => $row['url'] ?? $row['tracking_link'] ?? '#',
                    'category_name'                => $row['category'] ?? $row['category_name'] ?? '',
                    'shop_name'                    => $row['shop_name'] ?? '',
                    'rating'                       => $row['rating'] ?? 0,
                    'rating_count'                 => $row['rating_count'] ?? 0,
                    'sold_count'                   => $row['sold_count'] ?? $row['monthly_sold_count'] ?? 0,
                    'sold_count_text'              => $row['sold_count_text'] ?? '',
                    'images'                       => $row['images'] ?? '',
                    'colors'                       => $row['colors'] ?? '',
                    'sizes'                        => $row['sizes'] ?? '',
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
        // Generate SEO-friendly slug for product URL
        $p['product_slug'] = $p['product_id'] . '-' . generate_slug($p['product_name']);
    }

    if ($keyword) {
        $products = array_filter($products, fn($p) => stripos($p['product_name'], $keyword) !== false);
    }

    file_put_contents($cache_file, json_encode(array_values($products)));
    return array_values($products);
}

/**
 * Generate URL slug from Thai text
 */
function generate_slug($text) {
    // Convert to lowercase and replace spaces with hyphens
    $text = mb_strtolower(trim($text), 'UTF-8');
    $text = preg_replace('/\s+/', '-', $text);
    $text = preg_replace('/[^\p{Thai}\p{L}\p{N}\-]/u', '', $text);
    return urlencode(substr($text, 0, 100));
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
function get_random_reviews($count = 6) {
    $names = [
        'วิชัย ค.', 'สมหญิง ข.', 'สมชาย ก.', 'ประเสริฐ พ.', 'มาลี ส.',
        'กิตติพงษ์ จ.', 'นงลักษณ์ ม.', 'ธนพล ด.', 'ศิริพร ล.', 'อภิชาติ ต.',
        'ปิยะ ง.', 'มานี ส.', 'สุดา ว.', 'ชัยวัฒน์ บ.', 'อรุณี ร.'
    ];
    $comments = [
        'สินค้าคุณภาพดีมากครับ คุ้มค่ากับราคาที่จ่ายไป การจัดส่งก็รวดเร็วทันใจ แนะนำเลยครับ',
        'ได้รับสินค้าแล้วค่ะ แพ็คมาอย่างดี สินค้าตรงปกมาก สีสวยถูกใจ ใช้งานได้ดีไม่มีปัญหาอะไรเลยค่ะ',
        'คุณภาพพอใช้ได้ครับ ตามราคาที่จ่ายไป การจัดส่งช้าไปนิดหน่อยแต่โดยรวมโอเคครับ',
        'สินค้าดีมากค่ะ คุ้มค่า คุ้มราคา ส่งไวมาก แนะนำร้านนี้เลยค่ะ ไม่ผิดหวังแน่นอน',
        'ได้รับสินค้ารวดเร็ว บรรจุภัณฑ์ก็ดี สินค้าใช้งานได้ดีมากค่ะ ชอบมากเลย',
        'สินค้าตรงตามที่สั่งครับ ใช้งานได้ดี ไม่มีปัญหาอะไร แนะนำครับ',
        'ส่งไวมากครับ สินค้าคุณภาพดี ราคาไม่แพง คุ้มค่าสุดๆ',
        'แพ็คสินค้ามาดีมากครับ สินค้าไม่เสียหายเลย ใช้งานได้ดีมากครับ',
        'สินค้าสวยมากค่ะ ตรงปกทุกอย่าง ใช้งานง่าย สะดวกมากค่ะ',
        'คุ้มค่าคุ้มราคามากครับ สินค้าดี มีคุณภาพ แนะนำเลยครับ',
        'ชอบมากค่ะ สั่งซ้ำแน่นอน',
        'พอใช้ได้ ราคาถูกดี',
        'คุณภาพดี ตรงตามรูป แนะนำเลย',
        'สินค้าดีมาก คุ้มค่า ส่งเร็ว',
        'แนะนำให้ซื้อเลย ไม่ผิดหวัง',
        'ได้รับสินค้าเร็ว บรรจุภัณฑ์ดี',
    ];
    
    // Generate random dates in the past 2 years
    $reviews = [];
    $used_names = [];
    for ($i = 0; $i < $count; $i++) {
        // Ensure unique names
        do {
            $name = $names[array_rand($names)];
        } while (in_array($name, $used_names) && count($used_names) < count($names));
        $used_names[] = $name;
        
        $days_ago = rand(1, 730);
        $date = date('Y-m-d', strtotime("-{$days_ago} days"));
        
        $reviews[] = [
            'name'    => $name,
            'rating'  => rand(3, 5),
            'comment' => $comments[array_rand($comments)],
            'date'    => $date
        ];
    }
    return $reviews;
}

/**
 * Generate Random Price Comparison
 */
function get_price_comparison($base_price, $cloaked_url = '#') {
    $shops = [
        ['name' => 'ShopA Official',   'badge' => 'ร้านแนะนำ', 'badge_icon' => '🏆', 'mall' => true],
        ['name' => 'BestPrice Store',  'badge' => '',           'badge_icon' => '',   'mall' => false],
        ['name' => 'MegaDeal Shop',    'badge' => 'ขายดี',      'badge_icon' => '🔥', 'mall' => false],
        ['name' => 'ValuePlus Mall',   'badge' => '',           'badge_icon' => '',   'mall' => true],
        ['name' => 'SuperSave Outlet', 'badge' => 'ส่งไว',      'badge_icon' => '⚡', 'mall' => false],
    ];
    
    $base_val = (float)preg_replace('/[^0-9.]/', '', $base_price);
    $comparison = [];
    
    foreach ($shops as $shop) {
        $diff = rand(-10, 10);
        $comparison[] = [
            'shop_name'  => $shop['name'],
            'badge'      => $shop['badge'],
            'badge_icon' => $shop['badge_icon'],
            'mall'       => $shop['mall'],
            'rating'     => number_format(3.5 + (rand(0, 15) / 10), 1),
            'price'      => max(1, $base_val + $diff),
            'url'        => $cloaked_url,
        ];
    }
    
    usort($comparison, fn($a, $b) => $a['price'] <=> $b['price']);
    // Mark cheapest
    $comparison[0]['cheapest'] = true;
    return $comparison;
}

/**
 * Generate Random Purchase Notification Data
 */
function get_random_purchase_notification() {
    $names      = ['คุณพิมพ์', 'คุณสมศักดิ์', 'คุณอรุณี', 'คุณชัยวัฒน์', 'คุณสุภาภรณ์', 'คุณณัฐพล', 'คุณกนกวรรณ'];
    $provinces  = ['กรุงเทพฯ', 'ขอนแก่น', 'เชียงใหม่', 'ภูเก็ต', 'ชลบุรี', 'นครราชสีมา', 'สงขลา', 'อุบลราชธานี'];
    $time_ago   = ['1 นาทีที่แล้ว', '2 นาทีที่แล้ว', '5 นาทีที่แล้ว', '10 นาทีที่แล้ว', '15 นาทีที่แล้ว', '30 นาทีที่แล้ว', '1 ชั่วโมงที่แล้ว'];
    $all_products = get_all_products();
    if (empty($all_products)) {
        return [
            'customer_name' => $names[array_rand($names)],
            'province'      => $provinces[array_rand($provinces)],
            'product_name'  => 'สินค้าแนะนำ',
            'product_image' => '',
            'product_url'   => '#',
            'time_ago'      => $time_ago[array_rand($time_ago)]
        ];
    }
    $random_product = $all_products[array_rand($all_products)];
    return [
        'customer_name' => $names[array_rand($names)],
        'province'      => $provinces[array_rand($provinces)],
        'product_name'  => $random_product['product_name_display'],
        'product_image' => $random_product['product_image'],
        'product_url'   => 'product.php?id=' . $random_product['product_slug'],
        'time_ago'      => $time_ago[array_rand($time_ago)]
    ];
}

/**
 * Authentication check
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
    $files = glob(CACHE_DIR . '/*');
    if ($files) {
        foreach ($files as $file) {
            if (is_file($file)) unlink($file);
        }
    }
}
?>

/**
 * Clear all JSON cache files
 */
function clear_cache() {
    $files = glob(CACHE_DIR . '/*.json');
    foreach ($files as $file) {
        if (is_file($file)) unlink($file);
    }
}
