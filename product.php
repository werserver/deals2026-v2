<?php
require_once 'includes/functions.php';
$config = get_config();
$theme_color = $config['themeColor'] ?? '#ff6b00';

// Get product by slug (id-name format) or by id
$slug = $_GET['id'] ?? '';
$product = null;
$all_products = get_all_products();

if (!empty($slug)) {
    foreach ($all_products as $p) {
        if ($p['product_id'] == $slug || $p['product_slug'] == $slug || strpos($slug, (string)$p['product_id']) === 0) {
            $product = $p;
            break;
        }
    }
}

if (!$product && !empty($all_products)) {
    $product = $all_products[0];
}

if (!$product) {
    header('HTTP/1.0 404 Not Found');
    include 'includes/header.php';
    echo '<div class="container mx-auto px-4 py-24 text-center"><h1 class="text-2xl font-bold text-gray-400">ไม่พบสินค้า</h1><a href="index.php" class="mt-4 inline-block font-semibold" style="color:' . $theme_color . '">กลับหน้าแรก</a></div>';
    include 'includes/footer.php';
    exit;
}

$product_id   = $product['product_id'];
$product_name = $product['product_name'];
$display_name = $product['product_name_display'] ?? $product_name;
$price        = (float)preg_replace('/[^0-9.]/', '', $product['product_price']);
$original     = (float)preg_replace('/[^0-9.]/', '', $product['product_discounted']);
$discount_pct = (int)$product['product_discounted_percentage'];
$image        = $product['product_image'];
$cloaked_url  = $product['cloaked_url'] ?? $product['tracking_link'];
$category     = $product['category_name'];
$shop_name    = $product['shop_name'] ?: 'TrailQuest';
$rating       = (float)($product['rating'] ?: 4.0);
$rating_count = (int)($product['rating_count'] ?: rand(50, 500));
$sold_count   = $product['sold_count'] ?: rand(1000, 99999);

if ($original <= 0 || $original <= $price) {
    $original = round($price * (1 + rand(20, 80) / 100));
}
if ($discount_pct <= 0) {
    $discount_pct = round((($original - $price) / $original) * 100);
}

$images = [];
if (!empty($product['images'])) {
    $images = array_filter(array_map('trim', explode(',', $product['images'])));
}
if (empty($images) && !empty($image)) {
    $images = [$image];
}
// Fallback to random images if only one image exists to demonstrate the gallery
if (count($images) <= 1) {
    $placeholder_id = (int)preg_replace('/[^0-9]/', '', $product_id) % 1000;
    $images[] = "https://picsum.photos/seed/" . ($placeholder_id + 1) . "/600/600";
    $images[] = "https://picsum.photos/seed/" . ($placeholder_id + 2) . "/600/600";
    $images[] = "https://picsum.photos/seed/" . ($placeholder_id + 3) . "/600/600";
}

$colors = [];
$sizes  = [];
if (!empty($product['colors'])) {
    $colors = array_filter(array_map('trim', explode(',', $product['colors'])));
}
if (!empty($product['sizes'])) {
    $sizes = array_filter(array_map('trim', explode(',', $product['sizes'])));
}
if (empty($colors) && empty($sizes)) {
    $sample_sizes  = ['S', 'M', 'L', 'XL', 'XXL'];
    $sample_colors = ['สีดำ', 'สีขาว', 'สีแดง', 'สีน้ำเงิน', 'สีเขียว', 'สีเทา'];
    $sizes  = array_slice($sample_sizes, 0, rand(3, 5));
    $colors = array_slice($sample_colors, 0, rand(3, 4));
}

$reviews          = get_random_reviews(rand(5, 8));
$price_comparison = get_price_comparison($price, $cloaked_url);

$related = array_filter($all_products, fn($p) => $p['product_id'] !== $product_id);
$related = array_slice(array_values($related), 0, 5);

$flash_hours   = rand(2, 23);
$flash_minutes = rand(0, 59);
$flash_seconds = rand(0, 59);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($display_name); ?> | <?php echo htmlspecialchars($config['siteName']); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars(mb_substr($product_name, 0, 160)); ?>">
    <link rel="icon" type="image/x-icon" href="<?php echo htmlspecialchars($config['siteFavicon'] ?? '/favicon.ico'); ?>">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        * { font-family: 'Prompt', sans-serif !important; }
        body { line-height: 1.6; letter-spacing: 0.3px; }
        h1, h2, h3, h4, h5, h6 { letter-spacing: 0.5px; line-height: 1.3; }
        p, a, span, div { font-size: 14px; }
        .text-sm { font-size: 13px; }
        .text-base { font-size: 15px; }
        .text-lg { font-size: 17px; }
        .text-xl { font-size: 19px; }
        .text-2xl { font-size: 22px; }
        .font-semibold { font-weight: 600; }
        .font-bold { font-weight: 700; }
        .font-black { font-weight: 900; }
        :root { --primary: <?php echo $theme_color; ?>; }
        .text-primary { color: var(--primary) !important; }
        .bg-primary { background-color: var(--primary) !important; }
        .btn-primary { background-color: var(--primary); color: white; transition: all 0.2s; }
        .btn-primary:hover { opacity: 0.9; transform: translateY(-1px); }
        .btn-outline { border: 2px solid var(--primary); color: var(--primary); transition: all 0.2s; }
        .btn-outline:hover { background-color: var(--primary); color: white; }
        .announcement-bar { background: linear-gradient(90deg, var(--primary), color-mix(in srgb, var(--primary) 80%, black)); }
        .product-card:hover { transform: translateY(-4px); box-shadow: 0 20px 40px rgba(0,0,0,0.12); }
        .product-card { transition: all 0.3s ease; }
        .thumb-img { cursor: pointer; border: 2px solid transparent; border-radius: 0.75rem; transition: all 0.2s; }
        .thumb-img.active, .thumb-img:hover { border-color: var(--primary); }
        .variation-btn { padding: 0.5rem 1rem; border-radius: 999px; border: 1.5px solid #e5e7eb; font-size: 0.8125rem; font-weight: 600; cursor: pointer; transition: all 0.2s; background: white; }
        .variation-btn:hover, .variation-btn.active { border-color: var(--primary); color: var(--primary); background-color: color-mix(in srgb, var(--primary) 8%, white); }
        .star-filled { color: #f59e0b; }
        .star-empty { color: #d1d5db; }
        .flash-sale-bar { background: linear-gradient(135deg, #ef4444, #dc2626); }
        .cheapest-badge { background-color: #10b981; color: white; font-size: 0.65rem; font-weight: 700; padding: 2px 6px; border-radius: 999px; }
        @keyframes fadeOut { to { opacity: 0; transform: translateY(10px); } }
        @keyframes slideIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        @keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.5; } }
        @keyframes bounce { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-4px); } }
        .slide-in { animation: slideIn 0.4s ease-out; }
        .fade-in { animation: fadeIn 0.5s ease-out; }
        .pulse-animation { animation: pulse 2s infinite; }
        .bounce-animation { animation: bounce 1s infinite; }
        .scrollbar-hide::-webkit-scrollbar { display: none; }
        .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
        .card-hover { transition: all 0.3s ease; }
        .card-hover:hover { transform: translateY(-2px); box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
        .glow-effect { box-shadow: 0 0 20px rgba(0,0,0,0.08); }
        .smooth-transition { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
    </style>
</head>
<body class="bg-gray-50 min-h-screen flex flex-col">

    <!-- Announcement Bar -->
    <div class="announcement-bar text-white text-center py-2 px-4 text-xs font-semibold">
        <i class="fas fa-tag mr-2"></i>ดีลพิเศษวันนี้! สินค้าลดราคาสูงสุด 50% — รีบสั่งซื้อก่อนหมดเขต!
    </div>

    <!-- Header -->
    <header class="bg-white shadow-sm sticky top-0 z-50">
        <div class="container mx-auto px-4 h-16 flex items-center justify-between">
            <a href="index.php" class="flex items-center gap-2 group">
                <div class="h-9 w-9 rounded-xl flex items-center justify-center text-white shadow-md transition-transform group-hover:scale-110" style="background-color: <?php echo $theme_color; ?>;">
                    <i class="fas fa-shopping-bag text-sm"></i>
                </div>
                <span class="text-lg font-black text-gray-900 tracking-tight"><?php echo htmlspecialchars($config['siteName']); ?></span>
            </a>
            <a href="index.php" class="text-sm font-semibold text-gray-500 hover:text-gray-700 flex items-center gap-1.5 px-3 py-2 rounded-xl hover:bg-gray-100 transition-all">
                <i class="fas fa-arrow-left text-xs"></i> กลับหน้าแรก
            </a>
        </div>
    </header>

    <main class="container mx-auto px-4 py-6 max-w-5xl flex-1">
        <!-- Breadcrumb -->
        <nav class="flex items-center gap-2 text-xs text-gray-400 mb-5 flex-wrap">
            <a href="index.php" class="hover:text-gray-600 transition-colors">หน้าแรก</a>
            <i class="fas fa-chevron-right text-[10px]"></i>
            <?php if ($category): ?>
                <a href="index.php?cat=<?php echo urlencode($category); ?>" class="hover:text-gray-600 transition-colors"><?php echo htmlspecialchars($category); ?></a>
                <i class="fas fa-chevron-right text-[10px]"></i>
            <?php endif; ?>
            <span class="text-gray-500 font-medium"><?php echo htmlspecialchars(mb_substr($product_name, 0, 60, 'UTF-8')); ?>...</span>
        </nav>

        <!-- Product Detail Grid -->
        <div class="grid md:grid-cols-2 gap-8 mb-8">
            <!-- Images Gallery -->
            <div class="flex flex-col gap-4">
                <!-- Main Image with Slider Controls -->
                <div class="bg-white rounded-3xl overflow-hidden border border-gray-100 shadow-sm relative fade-in" style="aspect-ratio: 1;">
                    <?php if ($discount_pct > 0): ?>
                        <div class="absolute top-4 left-4 z-10 bg-red-500 text-white text-sm font-black px-3 py-1 rounded-full shadow-lg">-<?php echo $discount_pct; ?>%</div>
                    <?php endif; ?>
                    <a id="mainImageLink" href="<?php echo htmlspecialchars($cloaked_url); ?>" class="block w-full h-full" target="_blank" rel="noopener">
                        <?php if (!empty($images[0])): ?>
                            <img id="mainImage" src="<?php echo htmlspecialchars($images[0]); ?>"
                                 alt="<?php echo htmlspecialchars($product_name); ?>"
                                 class="w-full h-full object-contain p-6 smooth-transition cursor-pointer hover:scale-105"
                                 onerror="this.src='https://via.placeholder.com/500x500?text=No+Image'">
                        <?php else: ?>
                            <div class="w-full h-full flex items-center justify-center bg-gray-50">
                                <i class="fas fa-image text-6xl text-gray-200"></i>
                            </div>
                        <?php endif; ?>
                    </a>
                    <!-- Slider Controls -->
                    <?php if (count($images) > 1): ?>
                        <button type="button" onclick="prevImage()" class="absolute left-3 top-1/2 -translate-y-1/2 z-20 bg-white/80 hover:bg-white text-gray-800 w-10 h-10 rounded-full flex items-center justify-center transition-all shadow-lg">
                            <i class="fas fa-chevron-left text-lg"></i>
                        </button>
                        <button type="button" onclick="nextImage()" class="absolute right-3 top-1/2 -translate-y-1/2 z-20 bg-white/80 hover:bg-white text-gray-800 w-10 h-10 rounded-full flex items-center justify-center transition-all shadow-lg">
                            <i class="fas fa-chevron-right text-lg"></i>
                        </button>
                        <div class="absolute bottom-3 left-1/2 -translate-x-1/2 z-20 bg-black/50 text-white px-3 py-1 rounded-full text-xs font-semibold">
                            <span id="imageCounter">1</span> / <?php echo count($images); ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Thumbnail Gallery -->
                <?php if (count($images) > 1): ?>
                    <div class="grid grid-cols-5 gap-2">
                        <?php foreach ($images as $idx => $img): ?>
                            <a href="<?php echo htmlspecialchars($cloaked_url); ?>" target="_blank" rel="noopener" class="relative overflow-hidden rounded-2xl border-2 transition-all cursor-pointer hover:shadow-md" 
                                 onclick="setMainImage('<?php echo htmlspecialchars($img); ?>', this); return false;"
                                 style="aspect-ratio: 1; border-color: <?php echo $idx === 0 ? $theme_color : '#e5e7eb'; ?>">
                                <img src="<?php echo htmlspecialchars($img); ?>" alt="thumb <?php echo $idx+1; ?>"
                                     class="w-full h-full object-cover smooth-transition hover:scale-110"
                                     onerror="this.parentElement.style.display='none'">
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Info -->
            <div class="flex flex-col gap-4">
                <?php if ($category): ?>
                    <a href="index.php?cat=<?php echo urlencode($category); ?>"
                       class="inline-flex items-center gap-1.5 text-xs font-bold px-3 py-1.5 rounded-full w-fit hover:opacity-80 transition-all"
                       style="background-color: color-mix(in srgb, <?php echo $theme_color; ?> 12%, white); color: <?php echo $theme_color; ?>">
                        <i class="fas fa-tag text-[10px]"></i> <?php echo htmlspecialchars($category); ?>
                    </a>
                <?php endif; ?>

                <h1 class="text-xl font-black text-gray-900 leading-tight"><?php echo htmlspecialchars($display_name); ?></h1>

                <div class="flex items-center gap-2 flex-wrap">
                    <div class="flex items-center gap-0.5">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <i class="fas fa-star text-sm <?php echo $i <= round($rating) ? 'star-filled' : 'star-empty'; ?>"></i>
                        <?php endfor; ?>
                    </div>
                    <span class="text-sm font-bold text-gray-700"><?php echo number_format($rating, 1); ?></span>
                    <span class="text-sm text-gray-400">(<?php echo number_format($rating_count); ?>)</span>
                    <span class="text-sm text-gray-300">•</span>
                    <span class="text-sm text-gray-400">ขายแล้ว <?php echo number_format($sold_count); ?></span>
                </div>

                <div class="flex items-end gap-3">
                    <span class="text-4xl font-black" style="color: <?php echo $theme_color; ?>"><?php echo format_price($price); ?></span>
                    <?php if ($original > $price): ?>
                        <span class="text-lg text-gray-300 line-through font-semibold mb-1"><?php echo format_price($original); ?></span>
                        <span class="bg-red-500 text-white text-sm font-black px-2.5 py-1 rounded-full mb-1">-<?php echo $discount_pct; ?>%</span>
                    <?php endif; ?>
                </div>

                <?php if ($config['flashSaleEnabled']): ?>
                    <div class="flash-sale-bar text-white rounded-2xl p-4">
                        <div class="flex items-center gap-2 mb-2">
                            <i class="fas fa-bolt text-yellow-300"></i>
                            <span class="font-black text-sm"><i class="fas fa-zap text-yellow-300 mr-1"></i>Flash Sale — เวลาจำกัด!</span>
                        </div>
                        <div class="flex items-center gap-2 mb-2">
                            <div class="bg-white/20 rounded-lg px-3 py-1.5 text-center min-w-[52px]">
                                <span class="text-xl font-black" id="hours"><?php echo str_pad($flash_hours, 2, '0', STR_PAD_LEFT); ?></span>
                                <span class="text-xs block opacity-80">ชม.</span>
                            </div>
                            <span class="text-xl font-black opacity-60">:</span>
                            <div class="bg-white/20 rounded-lg px-3 py-1.5 text-center min-w-[52px]">
                                <span class="text-xl font-black" id="minutes"><?php echo str_pad($flash_minutes, 2, '0', STR_PAD_LEFT); ?></span>
                                <span class="text-xs block opacity-80">นาที</span>
                            </div>
                            <span class="text-xl font-black opacity-60">:</span>
                            <div class="bg-white/20 rounded-lg px-3 py-1.5 text-center min-w-[52px]">
                                <span class="text-xl font-black" id="seconds"><?php echo str_pad($flash_seconds, 2, '0', STR_PAD_LEFT); ?></span>
                                <span class="text-xs block opacity-80">วินาที</span>
                            </div>
                        </div>
                        <p class="text-xs opacity-90 flex items-center gap-1.5">
                            <i class="fas fa-fire text-yellow-300"></i> สินค้ากำลังจะหมด รีบสั่งซื้อเลย!
                        </p>
                    </div>
                <?php endif; ?>

                <div class="flex items-center gap-3 flex-wrap">
                    <div class="flex items-center gap-1.5 text-xs font-semibold text-gray-500 bg-gray-50 px-3 py-2 rounded-xl">
                        <i class="fas fa-shield-alt text-green-500"></i> สินค้าแท้ 100%
                    </div>
                    <div class="flex items-center gap-1.5 text-xs font-semibold text-gray-500 bg-gray-50 px-3 py-2 rounded-xl">
                        <i class="fas fa-truck text-blue-500"></i> จัดส่งฟรี
                    </div>
                    <div class="flex items-center gap-1.5 text-xs font-semibold text-gray-500 bg-gray-50 px-3 py-2 rounded-xl">
                        <i class="fas fa-check-circle text-purple-500"></i> สั่งซื้อง่าย
                    </div>
                </div>

                <div class="flex flex-col gap-3">
                    <a href="<?php echo htmlspecialchars($cloaked_url); ?>" target="_blank" rel="noopener"
                       class="w-full py-4 rounded-2xl font-black text-base text-center flex items-center justify-center gap-2 btn-outline">
                        <i class="fas fa-info-circle"></i> ดูรายละเอียดเพิ่มเติม
                    </a>
                    <a href="<?php echo htmlspecialchars($cloaked_url); ?>" target="_blank" rel="noopener"
                       class="w-full py-4 rounded-2xl font-black text-base text-center flex items-center justify-center gap-2 btn-primary shadow-lg">
                        <i class="fas fa-shopping-cart"></i> สั่งซื้อสินค้านี้
                    </a>
                </div>

                <div class="flex items-center gap-3">
                    <span class="text-sm font-semibold text-gray-400">แชร์:</span>
                    <a href="https://social-plugins.line.me/lineit/share?url=<?php echo urlencode('http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']); ?>"
                       target="_blank" class="h-9 w-9 rounded-xl bg-green-500 text-white flex items-center justify-center hover:opacity-80 transition-all shadow-sm">
                        <i class="fab fa-line text-sm"></i>
                    </a>
                    <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode('http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']); ?>"
                       target="_blank" class="h-9 w-9 rounded-xl bg-blue-600 text-white flex items-center justify-center hover:opacity-80 transition-all shadow-sm">
                        <i class="fab fa-facebook-f text-sm"></i>
                    </a>
                    <button onclick="copyLink()" class="h-9 w-9 rounded-xl bg-gray-100 text-gray-500 flex items-center justify-center hover:bg-gray-200 transition-all">
                        <i class="fas fa-link text-sm"></i>
                    </button>
                    <span id="copyMsg" class="text-xs text-green-500 font-semibold hidden">คัดลอกแล้ว!</span>
                </div>
            </div>
        </div>

        <!-- Variations -->
        <?php if (!empty($sizes) || !empty($colors)): ?>
            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6 mb-6">
                <h2 class="text-base font-black text-gray-900 mb-4 flex items-center gap-2">
                    <i class="fas fa-sliders-h" style="color: <?php echo $theme_color; ?>"></i> ตัวเลือกสินค้า
                </h2>
                <?php if (!empty($sizes)): ?>
                    <div class="mb-4">
                        <p class="text-sm font-semibold text-gray-500 mb-2">ขนาด</p>
                        <div class="flex flex-wrap gap-2">
                            <?php foreach ($sizes as $size): ?>
                                <button class="variation-btn" onclick="toggleVariation(this)"><?php echo htmlspecialchars($size); ?></button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
                <?php if (!empty($colors)): ?>
                    <div>
                        <p class="text-sm font-semibold text-gray-500 mb-2">สี</p>
                        <div class="flex flex-wrap gap-2">
                            <?php foreach ($colors as $color): ?>
                                <button class="variation-btn" onclick="toggleVariation(this)"><?php echo htmlspecialchars($color); ?></button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- Product Info Table -->
        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6 mb-6">
            <h2 class="text-base font-black text-gray-900 mb-4 flex items-center gap-2">
                <i class="fas fa-info-circle" style="color: <?php echo $theme_color; ?>"></i> ข้อมูลสินค้า
            </h2>
            <table class="w-full text-sm">
                <tbody class="divide-y divide-gray-50">
                    <tr><td class="py-3 pr-4 text-gray-400 font-semibold w-1/3">ชื่อสินค้า</td><td class="py-3 text-gray-800 font-medium"><?php echo htmlspecialchars($product_name); ?></td></tr>
                    <?php if ($category): ?><tr><td class="py-3 pr-4 text-gray-400 font-semibold">หมวดหมู่</td><td class="py-3 text-gray-800 font-medium"><?php echo htmlspecialchars($category); ?></td></tr><?php endif; ?>
                    <tr><td class="py-3 pr-4 text-gray-400 font-semibold">ร้านค้า</td><td class="py-3 text-gray-800 font-medium"><?php echo htmlspecialchars($shop_name); ?></td></tr>
                    <tr><td class="py-3 pr-4 text-gray-400 font-semibold">ราคาปกติ</td><td class="py-3 text-gray-400 font-medium line-through"><?php echo format_price($original); ?></td></tr>
                    <tr><td class="py-3 pr-4 text-gray-400 font-semibold">ราคาพิเศษ</td><td class="py-3 font-black text-lg" style="color: <?php echo $theme_color; ?>"><?php echo format_price($price); ?></td></tr>
                    <tr><td class="py-3 pr-4 text-gray-400 font-semibold">ส่วนลด</td><td class="py-3"><span class="bg-red-100 text-red-600 font-bold px-2 py-0.5 rounded-lg text-sm"><?php echo $discount_pct; ?>%</span></td></tr>
                </tbody>
            </table>
        </div>

        <!-- Price Comparison -->
        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6 mb-6">
            <h2 class="text-base font-black text-gray-900 mb-4 flex items-center gap-2">
                <i class="fas fa-balance-scale" style="color: <?php echo $theme_color; ?>"></i> เปรียบเทียบราคาจากหลายร้าน
            </h2>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100">
                            <th class="text-left py-3 pr-4 text-gray-400 font-semibold">ร้านค้า</th>
                            <th class="text-center py-3 px-4 text-gray-400 font-semibold">คะแนน</th>
                            <th class="text-right py-3 px-4 text-gray-400 font-semibold">ราคา</th>
                            <th class="text-center py-3 pl-4 text-gray-400 font-semibold"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <?php foreach ($price_comparison as $shop): ?>
                            <tr class="<?php echo isset($shop['cheapest']) ? 'bg-green-50/50' : ''; ?> hover:bg-gray-50 transition-colors">
                                <td class="py-3.5 pr-4">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="font-semibold text-gray-800"><?php echo htmlspecialchars($shop['shop_name']); ?></span>
                                        <?php if ($shop['badge']): ?>
                                            <span class="text-[11px] font-bold px-2 py-0.5 rounded-full"
                                                  style="background-color: color-mix(in srgb, <?php echo $theme_color; ?> 12%, white); color: <?php echo $theme_color; ?>">
                                                <?php echo $shop['badge_icon']; ?> <?php echo htmlspecialchars($shop['badge']); ?>
                                            </span>
                                        <?php endif; ?>
                                        <?php if (isset($shop['cheapest'])): ?>
                                            <span class="cheapest-badge">ถูกสุด</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <div class="flex items-center justify-center gap-1">
                                        <i class="fas fa-star star-filled text-xs"></i>
                                        <span class="font-semibold text-gray-700"><?php echo $shop['rating']; ?></span>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <span class="font-black text-base <?php echo isset($shop['cheapest']) ? 'text-green-600' : 'text-gray-800'; ?>">
                                        ฿<?php echo number_format($shop['price']); ?>
                                    </span>
                                </td>
                                <td class="py-3.5 pl-4 text-center">
                                    <!-- Sale badge links to product URL -->
                                    <a href="<?php echo htmlspecialchars($shop['url']); ?>" target="_blank" rel="noopener"
                                       class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold text-white shadow-sm hover:opacity-90 transition-all"
                                       style="background-color: <?php echo $theme_color; ?>">
                                        <i class="fas fa-external-link-alt text-[10px]"></i> ซื้อเลย
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Reviews -->
        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6 mb-8">
            <h2 class="text-base font-black text-gray-900 mb-5 flex items-center gap-2">
                <i class="fas fa-comments" style="color: <?php echo $theme_color; ?>"></i>
                รีวิวจากผู้ซื้อ (<?php echo count($reviews); ?>)
            </h2>
            <div class="space-y-4">
                <?php foreach ($reviews as $review): ?>
                    <div class="border-b border-gray-50 pb-4 last:border-0 last:pb-0">
                        <div class="flex items-start justify-between gap-4 mb-2">
                            <div class="flex items-center gap-3">
                                <div class="h-9 w-9 rounded-full flex items-center justify-center text-white text-sm font-bold flex-shrink-0"
                                     style="background-color: <?php echo $theme_color; ?>">
                                    <?php echo mb_substr($review['name'], 0, 1, 'UTF-8'); ?>
                                </div>
                                <div>
                                    <p class="font-bold text-gray-900 text-sm"><?php echo htmlspecialchars($review['name']); ?></p>
                                    <div class="flex items-center gap-0.5 mt-0.5">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="fas fa-star text-xs <?php echo $i <= $review['rating'] ? 'star-filled' : 'star-empty'; ?>"></i>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                            </div>
                            <span class="text-xs text-gray-300 flex-shrink-0"><?php echo htmlspecialchars($review['date']); ?></span>
                        </div>
                        <p class="text-sm text-gray-600 leading-relaxed ml-12"><?php echo htmlspecialchars($review['comment']); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Related Products -->
        <?php if (!empty($related)): ?>
            <div class="mb-8">
                <h2 class="text-lg font-black text-gray-900 mb-5 flex items-center gap-2">
                    <i class="fas fa-heart" style="color: <?php echo $theme_color; ?>"></i> สินค้าที่คุณอาจสนใจ
                </h2>
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-4">
                    <?php foreach ($related as $rp): ?>
                        <?php
                            $r_price    = (float)preg_replace('/[^0-9.]/', '', $rp['product_price']);
                            $r_original = (float)preg_replace('/[^0-9.]/', '', $rp['product_discounted']);
                            $r_discount = (int)$rp['product_discounted_percentage'];
                            if ($r_original <= $r_price) $r_original = round($r_price * (1 + rand(20, 60) / 100));
                            if ($r_discount <= 0) $r_discount = round((($r_original - $r_price) / $r_original) * 100);
                            $r_rating = (float)($rp['rating'] ?: 4.0);
                        ?>
                        <a href="product.php?id=<?php echo urlencode($rp['product_slug']); ?>"
                           class="product-card bg-white rounded-2xl border border-gray-100 overflow-hidden shadow-sm block">
                            <div class="relative aspect-square bg-gray-50">
                                <?php if ($r_discount > 0): ?>
                                    <div class="absolute top-2 left-2 z-10 bg-red-500 text-white text-[10px] font-black px-2 py-0.5 rounded-full">-<?php echo $r_discount; ?>%</div>
                                <?php endif; ?>
                                <?php if (!empty($rp['product_image'])): ?>
                                    <img src="<?php echo htmlspecialchars($rp['product_image']); ?>"
                                         alt="<?php echo htmlspecialchars($rp['product_name']); ?>"
                                         class="w-full h-full object-cover"
                                         onerror="this.src='https://via.placeholder.com/200x200?text=IMG'">
                                <?php else: ?>
                                    <div class="w-full h-full flex items-center justify-center"><i class="fas fa-image text-3xl text-gray-200"></i></div>
                                <?php endif; ?>
                            </div>
                            <div class="p-3">
                                <p class="text-xs font-semibold text-gray-700 line-clamp-2 mb-2 leading-tight"><?php echo htmlspecialchars($rp['product_name_display'] ?? $rp['product_name']); ?></p>
                                <div class="flex items-center gap-0.5 mb-1">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="fas fa-star text-[10px] <?php echo $i <= round($r_rating) ? 'star-filled' : 'star-empty'; ?>"></i>
                                    <?php endfor; ?>
                                    <span class="text-[10px] text-gray-400 ml-1"><?php echo number_format($r_rating, 1); ?></span>
                                </div>
                                <div class="flex items-end gap-1.5">
                                    <span class="text-sm font-black" style="color: <?php echo $theme_color; ?>">฿<?php echo number_format($r_price); ?></span>
                                    <span class="text-[11px] text-gray-300 line-through">฿<?php echo number_format($r_original); ?></span>
                                </div>
                                <?php if ($rp['sold_count']): ?>
                                    <p class="text-[10px] text-gray-300 mt-1"><?php echo number_format($rp['sold_count']); ?></p>
                                <?php endif; ?>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </main>

    <?php include 'includes/footer.php'; ?>

    <script>
        let currentImageIndex = 0;
        const images = <?php echo json_encode($images); ?>;
        const cloakedUrl = '<?php echo htmlspecialchars($cloaked_url); ?>';
        const themeColor = '<?php echo $theme_color; ?>';
        
        function setMainImage(src, el) {
            document.getElementById('mainImage').src = src;
            document.getElementById('mainImageLink').href = cloakedUrl;
            const idx = images.indexOf(src);
            if (idx !== -1) currentImageIndex = idx;
            updateImageCounter();
            document.querySelectorAll('[onclick*="setMainImage"]').forEach(thumb => {
                thumb.style.borderColor = '#e5e7eb';
            });
            el.style.borderColor = themeColor || '#ff6b00';
        }
        
        function nextImage() {
            currentImageIndex = (currentImageIndex + 1) % images.length;
            updateMainImage();
        }
        
        function prevImage() {
            currentImageIndex = (currentImageIndex - 1 + images.length) % images.length;
            updateMainImage();
        }
        
        function updateMainImage() {
            const src = images[currentImageIndex];
            document.getElementById('mainImage').src = src;
            document.getElementById('mainImageLink').href = cloakedUrl;
            updateImageCounter();
            const thumbnails = document.querySelectorAll('[onclick*="setMainImage"]');
            thumbnails.forEach((thumb, idx) => {
                thumb.style.borderColor = idx === currentImageIndex ? themeColor : '#e5e7eb';
            });
        }
        
        function updateImageCounter() {
            const counter = document.getElementById('imageCounter');
            if (counter) counter.textContent = currentImageIndex + 1;
        }
        function toggleVariation(btn) { btn.classList.toggle('active'); }
        function copyLink() {
            navigator.clipboard.writeText(window.location.href).then(() => {
                const msg = document.getElementById('copyMsg');
                msg.classList.remove('hidden');
                setTimeout(() => msg.classList.add('hidden'), 2000);
            });
        }
        let h = <?php echo $flash_hours; ?>, m = <?php echo $flash_minutes; ?>, s = <?php echo $flash_seconds; ?>;
        function updateCountdown() {
            if (s > 0) s--;
            else if (m > 0) { m--; s = 59; }
            else if (h > 0) { h--; m = 59; s = 59; }
            else { h = 23; m = 59; s = 59; }
            document.getElementById('hours').textContent   = String(h).padStart(2, '0');
            document.getElementById('minutes').textContent = String(m).padStart(2, '0');
            document.getElementById('seconds').textContent = String(s).padStart(2, '0');
        }
        setInterval(updateCountdown, 1000);
        function closeNotification() {
            const el = document.getElementById('purchaseNotification');
            if (el) { el.style.animation = 'fadeOut 0.3s ease-out forwards'; setTimeout(() => el.remove(), 300); }
        }
    </script>
</body>
</html>
