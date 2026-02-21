<?php 
include_once 'includes/header.php'; 
$categoryName = $_GET['name'] ?? '';
$products = get_all_products('', $categoryName);
?>

<main class="container mx-auto px-4 py-8 space-y-10">
    <!-- Breadcrumb -->
    <nav class="flex items-center gap-2 text-sm text-gray-500">
        <a href="index.php" class="hover:text-primary transition-colors">หน้าแรก</a>
        <i class="fas fa-chevron-right text-xs"></i>
        <span class="text-gray-900 font-medium"><?php echo htmlspecialchars($categoryName); ?></span>
    </nav>

    <!-- Category Header -->
    <div class="rounded-3xl bg-gradient-to-r from-blue-600 to-indigo-600 px-8 py-12 text-white shadow-xl relative overflow-hidden">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_70%_50%,rgba(255,255,255,0.12),transparent_60%)]"></div>
        <div class="relative flex items-center gap-4 mb-4">
            <div class="h-12 w-12 bg-white/20 rounded-2xl flex items-center justify-center text-2xl">
                <i class="fas fa-tag"></i>
            </div>
            <h1 class="text-3xl font-bold"><?php echo htmlspecialchars($categoryName); ?></h1>
        </div>
        <p class="text-lg text-white/80 relative">
            รวมสินค้าในหมวดหมู่ <?php echo htmlspecialchars($categoryName); ?> ลดราคา โปรโมชั่นสุดคุ้ม
        </p>
    </div>

    <!-- Product Grid -->
    <section class="space-y-6">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-bold text-gray-900">สินค้าในหมวดหมู่ <?php echo htmlspecialchars($categoryName); ?></h2>
            <span class="text-sm text-gray-500"><?php echo count($products); ?> รายการ</span>
        </div>

        <?php if (empty($products)): ?>
            <div class="text-center py-20 bg-white rounded-3xl border border-dashed border-gray-300">
                <i class="fas fa-box-open text-4xl text-gray-300 mb-4"></i>
                <p class="text-gray-500">ยังไม่มีสินค้าในหมวดหมู่นี้</p>
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
