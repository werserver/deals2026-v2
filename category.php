<?php 
include_once 'includes/functions.php';
$config = get_config();
$category = $_GET['name'] ?? '';
$products = get_all_products('', $category);
include_once 'includes/header.php';
?>

<main class="container mx-auto px-4 py-8 space-y-10">
    <!-- Category Header -->
    <div class="rounded-3xl bg-gradient-to-r from-blue-600 to-indigo-600 px-8 py-12 text-center text-white shadow-xl relative overflow-hidden" style="background: linear-gradient(to right, <?php echo $config['themeColor']; ?>, <?php echo $config['themeColor']; ?>dd);">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_30%_50%,rgba(255,255,255,0.15),transparent_60%)]"></div>
        <div class="relative flex flex-col items-center gap-4">
            <div class="h-16 w-16 bg-white/20 rounded-2xl flex items-center justify-center text-3xl">
                <i class="fas fa-folder-open"></i>
            </div>
            <h1 class="text-3xl md:text-4xl font-bold">
                หมวดหมู่: <?php echo htmlspecialchars($category); ?>
            </h1>
            <p class="text-lg text-white/80">
                พบสินค้าทั้งหมด <?php echo count($products); ?> รายการ ในหมวดหมู่นี้
            </p>
        </div>
    </div>

    <!-- Products Grid -->
    <section class="space-y-6">
        <?php if (empty($products)): ?>
            <div class="text-center py-20 bg-white rounded-3xl border border-dashed border-gray-200">
                <div class="text-6xl mb-4 text-gray-200"><i class="fas fa-box-open"></i></div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">ยังไม่มีสินค้าในหมวดหมู่นี้</h3>
                <p class="text-gray-500">ลองเลือกดูหมวดหมู่อื่น หรือกลับไปที่หน้าแรก</p>
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
                            <a href="product.php?id=<?php echo $index; ?>&cat=<?php echo urlencode($category); ?>" 
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
