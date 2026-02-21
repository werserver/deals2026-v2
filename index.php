<?php 
include_once 'includes/functions.php';
$config = get_config();
$keyword = $_GET['q'] ?? '';
$products = get_all_products($keyword);
include_once 'includes/header.php';
?>

<main class="container mx-auto px-4 py-8 space-y-10">
    <!-- Hero section -->
    <div class="rounded-3xl bg-gradient-to-r from-red-500 to-orange-500 px-8 py-12 text-center text-white shadow-xl relative overflow-hidden" style="background: linear-gradient(to right, <?php echo $config['themeColor']; ?>, <?php echo $config['themeColor']; ?>dd);">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_30%_50%,rgba(255,255,255,0.15),transparent_60%)]"></div>
        <h1 class="text-3xl md:text-4xl font-bold relative mb-4">
            🛒 <?php echo htmlspecialchars($config['siteName']); ?> — สินค้าดีลพิเศษ
        </h1>
        <p class="text-lg text-white/80 relative mb-8">
            รวมสินค้าลดราคา โปรโมชั่นสุดคุ้ม จากร้านค้าชั้นนำ อัปเดตทุกวัน
        </p>
        <form action="index.php" method="GET" class="max-w-2xl mx-auto relative">
            <div class="relative group">
                <input type="text" name="q" value="<?php echo htmlspecialchars($keyword); ?>" 
                       placeholder="ค้นหาสินค้า ดีลเด็ด โปรโมชั่น..." 
                       class="w-full pl-12 pr-4 py-4 rounded-2xl border-0 bg-white text-gray-900 shadow-lg focus:ring-4 focus:ring-white/20 outline-none transition-all">
                <div class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
                    <i class="fas fa-search text-xl"></i>
                </div>
                <button type="submit" class="absolute right-2 top-1/2 -translate-y-1/2 bg-gray-900 text-white px-6 py-2.5 rounded-xl font-bold hover:bg-black transition-all">
                    ค้นหา
                </button>
            </div>
        </form>
        
        <?php if (!empty($config['keywords'])): ?>
            <div class="mt-6 flex flex-wrap justify-center gap-2 relative">
                <span class="text-sm text-white/60 py-1">คำค้นหายอดนิยม:</span>
                <?php foreach ($config['keywords'] as $kw): ?>
                    <a href="index.php?q=<?php echo urlencode($kw); ?>" class="text-sm bg-white/10 hover:bg-white/20 px-3 py-1 rounded-full transition-colors">
                        <?php echo htmlspecialchars($kw); ?>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Categories -->
    <?php if (!empty($config['categories'])): ?>
    <section class="space-y-4">
        <div class="flex items-center gap-2 text-xl font-bold text-gray-900">
            <i class="fas fa-tags text-primary" style="color: <?php echo $config['themeColor']; ?>"></i>
            <h2>หมวดหมู่สินค้า</h2>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
            <?php foreach ($config['categories'] as $cat): ?>
                <a href="category.php?name=<?php echo urlencode($cat); ?>" 
                   class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md hover:border-primary transition-all text-center group"
                   style="--hover-color: <?php echo $config['themeColor']; ?>">
                    <div class="h-12 w-12 bg-gray-50 rounded-xl flex items-center justify-center mx-auto mb-3 group-hover:bg-primary/10 transition-colors">
                        <i class="fas fa-folder text-gray-400 group-hover:text-primary transition-colors" style="color: <?php echo $config['themeColor']; ?>88"></i>
                    </div>
                    <span class="font-bold text-gray-700 group-hover:text-primary transition-colors"><?php echo htmlspecialchars($cat); ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <!-- Products Grid -->
    <section class="space-y-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2 text-xl font-bold text-gray-900">
                <i class="fas fa-fire text-orange-500"></i>
                <h2><?php echo $keyword ? 'ผลการค้นหา: ' . htmlspecialchars($keyword) : 'สินค้าแนะนำ'; ?></h2>
            </div>
            <span class="text-sm text-gray-500 font-medium"><?php echo count($products); ?> รายการ</span>
        </div>

        <?php if (empty($products)): ?>
            <div class="text-center py-20 bg-white rounded-3xl border border-dashed border-gray-200">
                <div class="text-6xl mb-4 text-gray-200"><i class="fas fa-search"></i></div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">ไม่พบสินค้าที่ต้องการ</h3>
                <p class="text-gray-500">ลองใช้คำค้นหาอื่น หรือเลือกดูตามหมวดหมู่สินค้า</p>
                <a href="index.php" class="inline-block mt-6 text-primary font-bold hover:underline" style="color: <?php echo $config['themeColor']; ?>">กลับหน้าแรก</a>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-6">
                <?php foreach ($products as $index => $p): ?>
                    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm hover:shadow-xl transition-all overflow-hidden group flex flex-col h-full">
                        <div class="relative aspect-square overflow-hidden bg-gray-50">
                            <img src="<?php echo htmlspecialchars($p['product_image'] ?? ''); ?>" 
                                 alt="<?php echo htmlspecialchars($p['product_name'] ?? ''); ?>"
                                 class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                                 onerror="this.src='https://via.placeholder.com/400x400?text=No+Image'">
                            <?php if (!empty($p['product_discounted_percentage'])): ?>
                                <div class="absolute top-3 left-3 bg-red-500 text-white text-[10px] font-bold px-2 py-1 rounded-lg shadow-lg">
                                    ลด <?php echo htmlspecialchars($p['product_discounted_percentage']); ?>%
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="p-4 flex flex-col flex-grow space-y-3">
                            <h3 class="font-bold text-gray-800 line-clamp-2 text-sm h-10 group-hover:text-primary transition-colors" style="color: <?php echo $config['themeColor']; ?>dd">
                                <?php echo htmlspecialchars($p['product_name'] ?? ''); ?>
                            </h3>
                            <div class="flex items-baseline gap-2">
                                <span class="text-lg font-bold text-primary" style="color: <?php echo $config['themeColor']; ?>">
                                    <?php echo format_price($p['product_price'] ?? 0); ?>
                                </span>
                                <?php if (!empty($p['product_discounted'])): ?>
                                    <span class="text-xs text-gray-400 line-through">
                                        <?php echo format_price($p['product_discounted']); ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            <a href="product.php?id=<?php echo $index; ?>" 
                               class="mt-auto w-full py-2.5 rounded-xl border border-gray-100 bg-gray-50 text-gray-700 text-xs font-bold text-center hover:bg-primary hover:text-white hover:border-primary transition-all"
                               style="--hover-bg: <?php echo $config['themeColor']; ?>">
                                ดูรายละเอียด
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</main>

<style>
    .hover\:bg-primary:hover { background-color: <?php echo $config['themeColor']; ?> !important; }
    .hover\:border-primary:hover { border-color: <?php echo $config['themeColor']; ?> !important; }
    .hover\:text-primary:hover { color: <?php echo $config['themeColor']; ?> !important; }
</style>

<?php include_once 'includes/footer.php'; ?>
