<?php
require_once 'includes/functions.php';
$config = get_config();
$theme_color = $config['themeColor'] ?? '#ff6b00';

$keyword  = trim($_GET['q'] ?? '');
$category = trim($_GET['cat'] ?? '');
$sort     = $_GET['sort'] ?? 'default';
$min_price = (float)($_GET['min'] ?? 0);
$max_price = (float)($_GET['max'] ?? 0);
$page     = max(1, (int)($_GET['page'] ?? 1));
$per_page = 20;

$all_products = get_all_products($keyword, $category);

// Filter by price
if ($min_price > 0) {
    $all_products = array_filter($all_products, fn($p) => (float)preg_replace('/[^0-9.]/', '', $p['product_price']) >= $min_price);
}
if ($max_price > 0) {
    $all_products = array_filter($all_products, fn($p) => (float)preg_replace('/[^0-9.]/', '', $p['product_price']) <= $max_price);
}
$all_products = array_values($all_products);

// Sort
if ($sort === 'price_asc') {
    usort($all_products, fn($a, $b) => (float)preg_replace('/[^0-9.]/', '', $a['product_price']) <=> (float)preg_replace('/[^0-9.]/', '', $b['product_price']));
} elseif ($sort === 'price_desc') {
    usort($all_products, fn($a, $b) => (float)preg_replace('/[^0-9.]/', '', $b['product_price']) <=> (float)preg_replace('/[^0-9.]/', '', $a['product_price']));
} elseif ($sort === 'discount') {
    usort($all_products, fn($a, $b) => (int)$b['product_discounted_percentage'] <=> (int)$a['product_discounted_percentage']);
}

$total_products = count($all_products);
$total_pages    = max(1, ceil($total_products / $per_page));
$page           = min($page, $total_pages);
$products       = array_slice($all_products, ($page - 1) * $per_page, $per_page);
$categories     = $config['categories'] ?? [];
$keywords       = $config['keywords'] ?? [];
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($config['siteName']); ?> — รวมสินค้าดีลพิเศษ ลดราคา โปรโมชั่นสุดคุ้ม</title>
    <meta name="description" content="แหล่งรวมดีลสินค้าโปรโมชั่นที่ดีที่สุด ช้อปคุ้มกว่าใครด้วยระบบเปรียบเทียบราคา">
    <link rel="icon" type="image/x-icon" href="<?php echo htmlspecialchars($config['siteFavicon'] ?? '/favicon.ico'); ?>">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        * { font-family: 'Prompt', sans-serif !important; }
        :root { --primary: <?php echo $theme_color; ?>; }
        .text-primary { color: var(--primary) !important; }
        .bg-primary { background-color: var(--primary) !important; }
        .announcement-bar { background: linear-gradient(90deg, var(--primary), color-mix(in srgb, var(--primary) 80%, black)); }
        .hero-gradient { background: linear-gradient(135deg, color-mix(in srgb, var(--primary) 90%, black), var(--primary), color-mix(in srgb, var(--primary) 70%, #ff9500)); }
        .product-card:hover { transform: translateY(-4px); box-shadow: 0 20px 40px rgba(0,0,0,0.12); }
        .product-card { transition: all 0.3s ease; }
        .cat-tab { padding: 0.5rem 1.25rem; border-radius: 999px; font-size: 0.8125rem; font-weight: 700; cursor: pointer; transition: all 0.2s; border: 1.5px solid transparent; white-space: nowrap; }
        .cat-tab.active { background-color: var(--primary); color: white; }
        .cat-tab:not(.active) { background: white; color: #6b7280; border-color: #e5e7eb; }
        .cat-tab:not(.active):hover { border-color: var(--primary); color: var(--primary); }
        .star-filled { color: #f59e0b; }
        .star-empty { color: #d1d5db; }
        .discount-badge { background: #ef4444; color: white; font-weight: 700; font-size: 0.7rem; padding: 2px 7px; border-radius: 999px; }
        .scrollbar-hide::-webkit-scrollbar { display: none; }
        .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
        @keyframes fadeOut { to { opacity: 0; transform: translateY(10px); } }
        @keyframes slideIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        .slide-in { animation: slideIn 0.4s ease-out; }
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
            <nav class="hidden md:flex items-center gap-5">
                <a href="index.php" class="text-sm font-bold transition-colors" style="color: <?php echo $theme_color; ?>">หน้าแรก</a>
                <?php foreach (array_slice($categories, 0, 4) as $cat): ?>
                    <?php $cn = is_array($cat) ? ($cat['name'] ?? '') : $cat; ?>
                    <a href="index.php?cat=<?php echo urlencode($cn); ?>" class="text-sm font-semibold text-gray-500 hover:text-gray-800 transition-colors"><?php echo htmlspecialchars($cn); ?></a>
                <?php endforeach; ?>
            </nav>
            <div class="flex items-center gap-2">
                <a href="admin.php" class="h-9 w-9 rounded-xl bg-gray-100 text-gray-500 hover:bg-gray-200 transition-all flex items-center justify-center" title="Admin">
                    <i class="fas fa-user-cog text-sm"></i>
                </a>
                <button id="mobileMenuBtn" class="md:hidden h-9 w-9 rounded-xl bg-gray-100 text-gray-500 hover:bg-gray-200 transition-all flex items-center justify-center">
                    <i class="fas fa-bars text-sm"></i>
                </button>
            </div>
        </div>
        <div id="mobileMenu" class="hidden md:hidden border-t border-gray-100 bg-white">
            <div class="container mx-auto px-4 py-3 flex flex-col gap-2">
                <a href="index.php" class="py-2 text-sm font-bold" style="color: <?php echo $theme_color; ?>">หน้าแรก</a>
                <?php foreach (array_slice($categories, 0, 5) as $cat): ?>
                    <?php $cn = is_array($cat) ? ($cat['name'] ?? '') : $cat; ?>
                    <a href="index.php?cat=<?php echo urlencode($cn); ?>" class="py-2 text-sm font-semibold text-gray-500"><?php echo htmlspecialchars($cn); ?></a>
                <?php endforeach; ?>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero-gradient text-white py-12 px-4">
        <div class="container mx-auto max-w-3xl text-center">
            <h1 class="text-3xl md:text-4xl font-black mb-3 leading-tight">รวมดีลสินค้าสุดคุ้ม<br>ลดราคาทุกวัน!</h1>
            <p class="text-white/80 text-sm mb-6">ค้นหาสินค้าที่ต้องการ เปรียบเทียบราคา และรับส่วนลดสูงสุด</p>
            <form method="GET" action="index.php" class="relative max-w-xl mx-auto">
                <input type="text" name="q" value="<?php echo htmlspecialchars($keyword); ?>"
                       placeholder="ค้นหาสินค้า เช่น iPhone, Nike, Samsung..."
                       class="w-full h-14 pl-6 pr-14 rounded-2xl text-gray-900 font-semibold text-sm outline-none shadow-xl placeholder-gray-400"
                       style="font-family: 'Prompt', sans-serif !important;">
                <button type="submit" class="absolute right-2 top-2 h-10 w-10 rounded-xl flex items-center justify-center text-white shadow-md"
                        style="background-color: <?php echo $theme_color; ?>">
                    <i class="fas fa-search text-sm"></i>
                </button>
            </form>
            <?php if (!empty($keywords)): ?>
                <div class="flex flex-wrap justify-center gap-2 mt-4">
                    <?php foreach (array_slice($keywords, 0, 8) as $kw): ?>
                        <a href="index.php?q=<?php echo urlencode($kw); ?>"
                           class="text-xs font-semibold bg-white/20 hover:bg-white/30 text-white px-3 py-1.5 rounded-full transition-all">
                            <?php echo htmlspecialchars($kw); ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <main class="container mx-auto px-4 py-8 flex-1 max-w-7xl">

        <!-- Category Tabs -->
        <?php if (!empty($categories)): ?>
            <div class="flex gap-2 overflow-x-auto scrollbar-hide pb-2 mb-6">
                <a href="index.php<?php echo $keyword ? '?q='.urlencode($keyword) : ''; ?>"
                   class="cat-tab <?php echo empty($category) ? 'active' : ''; ?>">ทั้งหมด</a>
                <?php foreach ($categories as $cat): ?>
                    <?php $cn = is_array($cat) ? ($cat['name'] ?? '') : $cat; ?>
                    <a href="index.php?cat=<?php echo urlencode($cn); ?><?php echo $keyword ? '&q='.urlencode($keyword) : ''; ?>"
                       class="cat-tab <?php echo $category === $cn ? 'active' : ''; ?>">
                        <?php echo htmlspecialchars($cn); ?>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Filters & Sort -->
        <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
            <p class="text-sm font-semibold text-gray-500">
                <?php if ($keyword): ?>ผลการค้นหา "<span class="text-gray-900"><?php echo htmlspecialchars($keyword); ?></span>" — <?php endif; ?>
                <?php if ($category): ?>หมวดหมู่: <span class="text-gray-900"><?php echo htmlspecialchars($category); ?></span> — <?php endif; ?>
                <span class="font-black text-gray-900"><?php echo $total_products; ?></span> สินค้า
            </p>
            <form method="GET" action="index.php" class="flex items-center gap-2 flex-wrap">
                <?php if ($keyword): ?><input type="hidden" name="q" value="<?php echo htmlspecialchars($keyword); ?>"><?php endif; ?>
                <?php if ($category): ?><input type="hidden" name="cat" value="<?php echo htmlspecialchars($category); ?>"><?php endif; ?>
                <input type="number" name="min" value="<?php echo $min_price ?: ''; ?>" placeholder="ราคาต่ำสุด"
                       class="w-28 px-3 py-2 text-xs font-semibold bg-white border border-gray-200 rounded-xl outline-none focus:border-gray-400 transition-all">
                <input type="number" name="max" value="<?php echo $max_price ?: ''; ?>" placeholder="ราคาสูงสุด"
                       class="w-28 px-3 py-2 text-xs font-semibold bg-white border border-gray-200 rounded-xl outline-none focus:border-gray-400 transition-all">
                <select name="sort" class="px-3 py-2 text-xs font-semibold bg-white border border-gray-200 rounded-xl outline-none focus:border-gray-400 transition-all" onchange="this.form.submit()">
                    <option value="default" <?php echo $sort === 'default' ? 'selected' : ''; ?>>เรียงตามค่าเริ่มต้น</option>
                    <option value="price_asc" <?php echo $sort === 'price_asc' ? 'selected' : ''; ?>>ราคาต่ำ → สูง</option>
                    <option value="price_desc" <?php echo $sort === 'price_desc' ? 'selected' : ''; ?>>ราคาสูง → ต่ำ</option>
                    <option value="discount" <?php echo $sort === 'discount' ? 'selected' : ''; ?>>ส่วนลดมากสุด</option>
                </select>
                <button type="submit" class="px-4 py-2 text-xs font-bold text-white rounded-xl transition-all hover:opacity-90" style="background-color: <?php echo $theme_color; ?>">
                    <i class="fas fa-filter mr-1"></i> กรอง
                </button>
            </form>
        </div>

        <!-- Products Grid -->
        <?php if (empty($products)): ?>
            <div class="text-center py-24">
                <i class="fas fa-search text-5xl text-gray-200 mb-4"></i>
                <h2 class="text-xl font-black text-gray-400 mb-2">ไม่พบสินค้า</h2>
                <p class="text-gray-300 text-sm mb-6">ลองค้นหาด้วยคำอื่น หรือเปลี่ยนหมวดหมู่</p>
                <a href="index.php" class="inline-flex items-center gap-2 px-6 py-3 text-white rounded-xl font-bold text-sm hover:opacity-90 transition-all"
                   style="background-color: <?php echo $theme_color; ?>">
                    <i class="fas fa-home"></i> กลับหน้าแรก
                </a>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                <?php foreach ($products as $p): ?>
                    <?php
                        $p_price    = (float)preg_replace('/[^0-9.]/', '', $p['product_price']);
                        $p_original = (float)preg_replace('/[^0-9.]/', '', $p['product_discounted']);
                        $p_discount = (int)$p['product_discounted_percentage'];
                        if ($p_original <= $p_price) $p_original = round($p_price * (1 + rand(20, 60) / 100));
                        if ($p_discount <= 0) $p_discount = round((($p_original - $p_price) / $p_original) * 100);
                        $p_rating = (float)($p['rating'] ?: 4.0);
                        $p_rating_count = (int)($p['rating_count'] ?: rand(10, 500));
                    ?>
                    <a href="product.php?id=<?php echo urlencode($p['product_slug']); ?>"
                       class="product-card bg-white rounded-2xl border border-gray-100 overflow-hidden shadow-sm block group">
                        <div class="relative aspect-square bg-gray-50 overflow-hidden">
                            <?php if ($p_discount > 0): ?>
                                <div class="absolute top-2 left-2 z-10 discount-badge">-<?php echo $p_discount; ?>%</div>
                            <?php endif; ?>
                            <?php if (!empty($p['product_image'])): ?>
                                <img src="<?php echo htmlspecialchars($p['product_image']); ?>"
                                     alt="<?php echo htmlspecialchars($p['product_name']); ?>"
                                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                                     loading="lazy"
                                     onerror="this.src='https://via.placeholder.com/300x300?text=No+Image'">
                            <?php else: ?>
                                <div class="w-full h-full flex items-center justify-center">
                                    <i class="fas fa-image text-4xl text-gray-200"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="p-3">
                            <p class="text-xs font-semibold text-gray-800 line-clamp-2 mb-2 leading-tight group-hover:opacity-70 transition-opacity">
                                <?php echo htmlspecialchars($p['product_name_display'] ?? $p['product_name']); ?>
                            </p>
                            <div class="flex items-center gap-0.5 mb-1.5">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="fas fa-star text-[10px] <?php echo $i <= round($p_rating) ? 'star-filled' : 'star-empty'; ?>"></i>
                                <?php endfor; ?>
                                <span class="text-[10px] text-gray-400 ml-1"><?php echo number_format($p_rating, 1); ?></span>
                                <span class="text-[10px] text-gray-300 ml-0.5">(<?php echo $p_rating_count; ?>)</span>
                            </div>
                            <div class="flex items-end gap-1.5 mb-1">
                                <span class="text-sm font-black" style="color: <?php echo $theme_color; ?>">฿<?php echo number_format($p_price); ?></span>
                                <span class="text-[11px] text-gray-300 line-through">฿<?php echo number_format($p_original); ?></span>
                            </div>
                            <?php if ($p['sold_count']): ?>
                                <p class="text-[10px] text-gray-300"><?php echo number_format($p['sold_count']); ?></p>
                            <?php endif; ?>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div class="mt-10 flex justify-center items-center gap-2">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page-1; ?>&<?php echo http_build_query(array_filter(['q'=>$keyword,'cat'=>$category,'sort'=>$sort,'min'=>$min_price,'max'=>$max_price])); ?>"
                           class="h-10 w-10 flex items-center justify-center rounded-xl bg-white border border-gray-200 text-gray-500 hover:bg-gray-50 transition-all">
                            <i class="fas fa-chevron-left text-xs"></i>
                        </a>
                    <?php endif; ?>
                    <?php for ($i = max(1, $page-2); $i <= min($total_pages, $page+2); $i++): ?>
                        <a href="?page=<?php echo $i; ?>&<?php echo http_build_query(array_filter(['q'=>$keyword,'cat'=>$category,'sort'=>$sort,'min'=>$min_price,'max'=>$max_price])); ?>"
                           class="h-10 w-10 flex items-center justify-center rounded-xl font-bold text-sm transition-all <?php echo $i === $page ? 'text-white shadow-md' : 'bg-white border border-gray-200 text-gray-500 hover:bg-gray-50'; ?>"
                           style="<?php echo $i === $page ? 'background-color: '.$theme_color.';' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                    <?php if ($page < $total_pages): ?>
                        <a href="?page=<?php echo $page+1; ?>&<?php echo http_build_query(array_filter(['q'=>$keyword,'cat'=>$category,'sort'=>$sort,'min'=>$min_price,'max'=>$max_price])); ?>"
                           class="h-10 w-10 flex items-center justify-center rounded-xl bg-white border border-gray-200 text-gray-500 hover:bg-gray-50 transition-all">
                            <i class="fas fa-chevron-right text-xs"></i>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-100 pt-12 pb-8 mt-auto">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-10 mb-10">
                <div class="col-span-1 md:col-span-2">
                    <a href="index.php" class="flex items-center gap-2 mb-4">
                        <div class="h-9 w-9 rounded-xl flex items-center justify-center text-white shadow-md" style="background-color: <?php echo $theme_color; ?>;">
                            <i class="fas fa-shopping-bag text-sm"></i>
                        </div>
                        <span class="text-lg font-black text-gray-900 tracking-tight"><?php echo htmlspecialchars($config['siteName']); ?></span>
                    </a>
                    <p class="text-gray-400 text-sm leading-relaxed max-w-sm">แหล่งรวมดีลสินค้าโปรโมชั่นที่ดีที่สุดจากทุกแพลตฟอร์ม ช้อปคุ้มกว่าใครด้วยระบบเปรียบเทียบราคาและรีวิวที่เชื่อถือได้</p>
                </div>
                <div>
                    <h4 class="text-sm font-bold text-gray-900 uppercase tracking-widest mb-4">เมนูหลัก</h4>
                    <ul class="space-y-3">
                        <li><a href="index.php" class="text-sm text-gray-400 hover:text-gray-700 transition-colors flex items-center gap-2"><i class="fas fa-home text-xs"></i> หน้าแรก</a></li>
                        <li><a href="index.php" class="text-sm text-gray-400 hover:text-gray-700 transition-colors flex items-center gap-2"><i class="fas fa-th-large text-xs"></i> หมวดหมู่สินค้า</a></li>
                        <li><a href="#" class="text-sm text-gray-400 hover:text-gray-700 transition-colors flex items-center gap-2"><i class="fas fa-info-circle text-xs"></i> เกี่ยวกับเรา</a></li>
                        <li><a href="#" class="text-sm text-gray-400 hover:text-gray-700 transition-colors flex items-center gap-2"><i class="fas fa-envelope text-xs"></i> ติดต่อเรา</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-sm font-bold text-gray-900 uppercase tracking-widest mb-4">หมวดหมู่ยอดนิยม</h4>
                    <ul class="space-y-3">
                        <?php foreach (array_slice($categories, 0, 5) as $cat): ?>
                            <?php $cn = is_array($cat) ? ($cat['name'] ?? '') : $cat; ?>
                            <li><a href="index.php?cat=<?php echo urlencode($cn); ?>" class="text-sm text-gray-400 hover:text-gray-700 transition-colors"><?php echo htmlspecialchars($cn); ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
            <div class="pt-6 border-t border-gray-100 flex flex-col md:flex-row justify-between items-center gap-4">
                <p class="text-xs text-gray-300">&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($config['siteName']); ?> — All rights reserved.</p>
                <div class="flex gap-4 items-center">
                    <a href="#" class="h-8 w-8 rounded-lg bg-gray-50 text-gray-400 hover:bg-blue-500 hover:text-white transition-all flex items-center justify-center"><i class="fab fa-facebook-f text-xs"></i></a>
                    <a href="#" class="h-8 w-8 rounded-lg bg-gray-50 text-gray-400 hover:bg-green-500 hover:text-white transition-all flex items-center justify-center"><i class="fab fa-line text-xs"></i></a>
                    <a href="#" class="h-8 w-8 rounded-lg bg-gray-50 text-gray-400 hover:bg-pink-500 hover:text-white transition-all flex items-center justify-center"><i class="fab fa-instagram text-xs"></i></a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Purchase Notification Badge -->
    <?php
    $notification = get_random_purchase_notification();
    if ($notification && !empty($notification['product_name'])):
    ?>
    <div id="purchaseNotification" class="fixed bottom-6 left-6 z-40 max-w-xs slide-in">
        <a href="<?php echo htmlspecialchars($notification['product_url']); ?>" class="block">
            <div class="bg-white rounded-2xl shadow-2xl border border-gray-100 overflow-hidden hover:shadow-3xl transition-all duration-300">
                <div class="p-4">
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0">
                            <?php if (!empty($notification['product_image'])): ?>
                                <img src="<?php echo htmlspecialchars($notification['product_image']); ?>"
                                     alt="product" class="w-14 h-14 rounded-xl object-cover bg-gray-50"
                                     onerror="this.src='https://via.placeholder.com/56x56?text=IMG'">
                            <?php else: ?>
                                <div class="w-14 h-14 rounded-xl bg-gray-100 flex items-center justify-center">
                                    <i class="fas fa-shopping-bag text-gray-300"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-[10px] font-bold uppercase tracking-wider mb-0.5" style="color: <?php echo $theme_color; ?>">ซื้อสินค้าแล้ว</p>
                            <p class="text-xs font-bold text-gray-900 mb-1"><?php echo htmlspecialchars($notification['customer_name']); ?> จาก<?php echo htmlspecialchars($notification['province']); ?></p>
                            <p class="text-[11px] text-gray-500 line-clamp-2 mb-2"><?php echo htmlspecialchars($notification['product_name']); ?></p>
                            <div class="flex items-center justify-between">
                                <span class="text-[10px] text-gray-300"><?php echo htmlspecialchars($notification['time_ago']); ?></span>
                                <button onclick="event.preventDefault(); closeNotification()" class="text-gray-300 hover:text-gray-500 transition-colors ml-2">
                                    <i class="fas fa-times text-xs"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div>
    <?php endif; ?>

    <script>
        document.getElementById('mobileMenuBtn')?.addEventListener('click', function() {
            document.getElementById('mobileMenu').classList.toggle('hidden');
        });
        function closeNotification() {
            const el = document.getElementById('purchaseNotification');
            if (el) { el.style.animation = 'fadeOut 0.3s ease-out forwards'; setTimeout(() => el.remove(), 300); }
        }
        setTimeout(() => closeNotification(), 8000);
    </script>
</body>
</html>
