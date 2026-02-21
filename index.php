<?php 
include_once 'includes/header.php'; 
$keyword = $_GET['q'] ?? '';
$products = get_all_products($keyword);
?>

<main class="container mx-auto px-4 py-8 space-y-10">
    <!-- Hero section -->
    <div class="rounded-3xl bg-gradient-to-r from-red-500 to-orange-500 px-8 py-12 text-center text-white shadow-xl relative overflow-hidden">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_30%_50%,rgba(255,255,255,0.15),transparent_60%)]"></div>
        <h1 class="text-3xl md:text-4xl font-bold relative mb-4">
            🛒 <?php echo htmlspecialchars($config['siteName'] ?? 'ThaiDeals'); ?> — สินค้าดีลพิเศษ
        </h1>
        <p class="text-lg text-white/80 relative mb-8">
            รวมสินค้าลดราคา โปรโมชั่นสุดคุ้ม จากร้านค้าชั้นนำ อัปเดตทุกวัน
        </p>
        <form action="index.php" method="GET" class="max-w-2xl mx-auto relative">
            <div class="relative group">
                <input type="text" name="q" value="<?php echo htmlspecialchars($keyword); ?>" 
                       placeholder="ค้นหาสินค้า ดีลเด็ด โปรโมชั่น..." 
                       class="w-full pl-12 pr-4 py-4 rounded-2xl border-0 bg-white text-gray-900 shadow-lg focus:ring-4 focus:ring-white/20 outline-none transition-all">
                <i class="fas fa-search absolute left-5 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-primary transition-colors"></i>
                <button type="submit" class="absolute right-3 top-1/2 -translate-y-1/2 bg-primary text-white px-6 py-2 rounded-xl font-medium hover:bg-red-600 transition-colors">
                    ค้นหา
                </button>
            </div>
        </form>
    </div>

    <!-- Categories -->
    <?php if (!empty($config['categories'])): ?>
    <section class="space-y-4">
        <h2 class="text-lg font-bold flex items-center gap-2 text-gray-800">
            <i class="fas fa-tags text-primary"></i> หมวดหมู่สินค้า
        </h2>
        <div class="flex flex-wrap gap-3">
            <?php foreach ($config['categories'] as $cat): ?>
                <a href="category.php?name=<?php echo urlencode($cat); ?>" 
                   class="bg-white border border-gray-200 hover:border-primary hover:text-primary px-5 py-2.5 rounded-2xl text-sm font-medium transition-all shadow-sm hover:shadow-md">
                    <?php echo htmlspecialchars($cat); ?>
                </a>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <!-- Product Grid -->
    <section class="space-y-6">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-bold text-gray-900">
                <?php echo $keyword ? 'ผลการค้นหา "' . htmlspecialchars($keyword) . '"' : 'สินค้าแนะนำ'; ?>
            </h2>
            <span class="text-sm text-gray-500"><?php echo count($products); ?> รายการ</span>
        </div>

        <?php if (empty($products)): ?>
            <div class="text-center py-20 bg-white rounded-3xl border border-dashed border-gray-300">
                <i class="fas fa-box-open text-4xl text-gray-300 mb-4"></i>
                <p class="text-gray-500">ไม่พบสินค้าที่คุณกำลังมองหา</p>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-6">
                <?php foreach ($products as $p): ?>
                    <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden shadow-sm hover:shadow-xl transition-all group flex flex-col">
                        <div class="aspect-square relative overflow-hidden bg-gray-50">
                            <img src="<?php echo htmlspecialchars($p['product_image'] ?? ''); ?>" 
                                 alt="<?php echo htmlspecialchars($p['product_name'] ?? ''); ?>"
                                 class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                            <?php if (isset($p['product_discounted_percentage']) && $p['product_discounted_percentage'] > 0): ?>
                                <div class="absolute top-3 left-3 bg-red-500 text-white text-xs font-bold px-2 py-1 rounded-lg shadow-lg">
                                    ลด <?php echo $p['product_discounted_percentage']; ?>%
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="p-4 flex flex-col flex-grow space-y-3">
                            <h3 class="text-sm font-medium text-gray-800 line-clamp-2 min-h-[2.5rem] group-hover:text-primary transition-colors">
                                <?php echo htmlspecialchars($p['product_name'] ?? ''); ?>
                            </h3>
                            <div class="flex items-baseline gap-2">
                                <span class="text-lg font-bold text-primary">
                                    <?php echo format_price($p['product_discounted'] ?? $p['product_price'] ?? 0); ?>
                                </span>
                                <?php if (isset($p['product_discounted']) && $p['product_discounted'] < $p['product_price']): ?>
                                    <span class="text-xs text-gray-400 line-through">
                                        <?php echo format_price($p['product_price']); ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            <a href="product.php?id=<?php echo urlencode($p['product_id'] ?? ''); ?>" 
                               class="mt-auto w-full bg-gray-50 text-gray-900 py-2.5 rounded-xl text-xs font-bold text-center hover:bg-primary hover:text-white transition-all">
                                ดูรายละเอียด
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</main>

<?php include_once 'includes/footer.php'; ?>
