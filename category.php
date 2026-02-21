<?php
require_once 'includes/functions.php';
$config = get_config();
$theme_color = $config['themeColor'] ?? '#ff6b00';
$category_name = trim($_GET['name'] ?? '');
if (empty($category_name)) { header('Location: index.php'); exit; }
$products = get_all_products('', $category_name);
$categories = $config['categories'] ?? [];
?>

<main class="container mx-auto px-4 py-8">
    <!-- Category Header -->
    <div class="bg-white rounded-[2.5rem] border border-gray-100 shadow-sm p-8 md:p-12 mb-12 flex flex-col md:flex-row items-center justify-between gap-8">
        <div class="flex items-center gap-6">
            <div class="h-20 w-20 bg-primary/10 rounded-3xl flex items-center justify-center text-primary text-3xl" style="color: <?php echo $config['themeColor']; ?>; background-color: <?php echo $config['themeColor']; ?>11;">
                <i class="fas fa-tag"></i>
            </div>
            <div>
                <h1 class="text-3xl md:text-4xl font-black text-gray-900 mb-2"><?php echo htmlspecialchars($category_name); ?></h1>
                <p class="text-gray-500 font-medium">พบสินค้าทั้งหมด <?php echo count($products); ?> รายการ</p>
            </div>
        </div>
        <a href="index.php" class="px-6 py-3 rounded-xl bg-gray-50 text-gray-600 font-bold hover:bg-gray-100 transition-all flex items-center gap-2">
            <i class="fas fa-arrow-left"></i> กลับหน้าแรก
        </a>
    </div>

    <!-- Products Grid -->
    <div class="mb-12">
        <?php if (empty($products)): ?>
            <div class="text-center py-20 bg-gray-50 rounded-[3rem] border border-dashed border-gray-200">
                <div class="h-20 w-20 bg-white rounded-3xl flex items-center justify-center mx-auto mb-6 shadow-sm">
                    <i class="fas fa-box-open text-3xl text-gray-200"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">ยังไม่มีสินค้าในหมวดหมู่นี้</h3>
                <p class="text-gray-500">ขออภัย กำลังอัปเดตข้อมูลสินค้าในหมวดหมู่นี้เร็วๆ นี้</p>
                <a href="index.php" class="inline-block mt-6 text-primary font-bold hover:underline" style="color: <?php echo $config['themeColor']; ?>;">ดูสินค้าอื่นๆ</a>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-6">
                <?php foreach ($products as $index => $p): ?>
                    <div class="group bg-white rounded-[2rem] border border-gray-100 shadow-sm hover:shadow-2xl hover:-translate-y-2 transition-all overflow-hidden flex flex-col">
                        <a href="product.php?id=<?php echo urlencode($p['product_slug']); ?>" class="relative aspect-square overflow-hidden bg-gray-50">
                            <img src="<?php echo htmlspecialchars($p['product_image']); ?>" 
                                 alt="<?php echo htmlspecialchars($p['product_name']); ?>"
                                 class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                                 onerror="this.src='https://via.placeholder.com/400x400?text=No+Image'">
                            <?php if (!empty($p['product_discounted_percentage'])): ?>
                                <div class="absolute top-4 left-4 bg-red-600 text-white text-xs font-black px-3 py-1.5 rounded-full shadow-lg">
                                    ลด <?php echo htmlspecialchars($p['product_discounted_percentage']); ?>%
                                </div>
                            <?php endif; ?>
                        </a>
                        <div class="p-5 flex flex-col flex-grow">
                            <a href="product.php?id=<?php echo urlencode($p['product_slug']); ?>" class="text-sm font-bold text-gray-800 line-clamp-2 mb-3 group-hover:text-primary transition-colors">
                                <?php echo htmlspecialchars($p['product_name_display'] ?? $p['product_name']); ?>
                            </a>
                            <div class="mt-auto">
                                <div class="flex items-baseline gap-2 mb-3">
                                    <span class="text-xl font-black text-primary" style="color: <?php echo $config['themeColor']; ?>">
                                        <?php echo format_price($p['product_price']); ?>
                                    </span>
                                    <?php if (!empty($p['product_discounted'])): ?>
                                        <span class="text-xs text-gray-400 line-through">
                                            <?php echo format_price($p['product_discounted']); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <a href="<?php echo htmlspecialchars($p['cloaked_url'] ?? $p['tracking_link']); ?>" target="_blank" 
                                   class="block w-full py-3 rounded-xl text-white text-center text-xs font-bold hover:opacity-90 transition-all shadow-lg"
                                   style="background-color: <?php echo $theme_color; ?>">
                                    สั่งซื้อสินค้า
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php include_once 'includes/footer.php'; ?>
