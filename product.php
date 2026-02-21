<?php 
include_once 'includes/functions.php';
$config = get_config();
$id = $_GET['id'] ?? 0;
$category = $_GET['cat'] ?? '';
$products = get_all_products('', $category);
$product = $products[$id] ?? null;

if (!$product) {
    header("Location: index.php");
    exit;
}

include_once 'includes/header.php';
?>

<main class="container mx-auto px-4 py-8">
    <div class="max-w-5xl mx-auto">
        <!-- Breadcrumb -->
        <nav class="flex items-center gap-2 text-sm text-gray-500 mb-8">
            <a href="index.php" class="hover:text-primary transition-colors">หน้าแรก</a>
            <i class="fas fa-chevron-right text-[10px]"></i>
            <?php if ($category): ?>
                <a href="category.php?name=<?php echo urlencode($category); ?>" class="hover:text-primary transition-colors"><?php echo htmlspecialchars($category); ?></a>
                <i class="fas fa-chevron-right text-[10px]"></i>
            <?php endif; ?>
            <span class="text-gray-900 font-medium line-clamp-1"><?php echo htmlspecialchars($product['product_name']); ?></span>
        </nav>

        <div class="bg-white rounded-[2rem] border border-gray-100 shadow-sm overflow-hidden">
            <div class="grid grid-cols-1 md:grid-cols-2">
                <!-- Product Image -->
                <div class="p-8 bg-gray-50/50">
                    <div class="aspect-square rounded-2xl overflow-hidden shadow-lg bg-white">
                        <img src="<?php echo htmlspecialchars($product['product_image']); ?>" 
                             alt="<?php echo htmlspecialchars($product['product_name']); ?>"
                             class="w-full h-full object-cover"
                             onerror="this.src='https://via.placeholder.com/600x600?text=No+Image'">
                    </div>
                </div>

                <!-- Product Info -->
                <div class="p-8 md:p-12 flex flex-col justify-center space-y-8">
                    <div class="space-y-4">
                        <?php if ($category): ?>
                            <span class="inline-block px-3 py-1 rounded-full bg-primary/10 text-primary text-xs font-bold" style="color: <?php echo $config['themeColor']; ?>; background-color: <?php echo $config['themeColor']; ?>11;">
                                <?php echo htmlspecialchars($category); ?>
                            </span>
                        <?php endif; ?>
                        <h1 class="text-2xl md:text-3xl font-bold text-gray-900 leading-tight">
                            <?php echo htmlspecialchars($product['product_name']); ?>
                        </h1>
                    </div>

                    <div class="space-y-2">
                        <div class="flex items-baseline gap-3">
                            <span class="text-4xl font-bold text-primary" style="color: <?php echo $config['themeColor']; ?>">
                                <?php echo format_price($product['product_price']); ?>
                            </span>
                            <?php if (!empty($product['product_discounted'])): ?>
                                <span class="text-lg text-gray-400 line-through">
                                    <?php echo format_price($product['product_discounted']); ?>
                                </span>
                                <span class="bg-red-500 text-white text-xs font-bold px-2 py-1 rounded-lg">
                                    ลด <?php echo htmlspecialchars($product['product_discounted_percentage']); ?>%
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="space-y-4 pt-4">
                        <a href="<?php echo htmlspecialchars($product['tracking_link']); ?>" target="_blank" 
                           class="block w-full py-4 rounded-2xl bg-blue-600 text-white text-center font-bold text-lg shadow-xl shadow-blue-200 hover:bg-blue-700 transition-all transform hover:-translate-y-1">
                            ดูรายละเอียดเพิ่มเติม
                        </a>
                        <a href="<?php echo htmlspecialchars($product['tracking_link']); ?>" target="_blank" 
                           class="block w-full py-4 rounded-2xl bg-primary text-white text-center font-bold text-lg shadow-xl shadow-red-200 hover:opacity-90 transition-all transform hover:-translate-y-1"
                           style="background-color: <?php echo $config['themeColor']; ?>; box-shadow: 0 10px 15px -3px <?php echo $config['themeColor']; ?>44;">
                            สั่งซื้อสินค้านี้
                        </a>
                    </div>

                    <div class="grid grid-cols-2 gap-4 pt-4">
                        <div class="flex items-center gap-3 p-4 rounded-2xl bg-gray-50 border border-gray-100">
                            <div class="h-10 w-10 rounded-full bg-green-100 text-green-600 flex items-center justify-center">
                                <i class="fas fa-shield-alt"></i>
                            </div>
                            <span class="text-sm font-bold text-gray-700">รับประกันคุณภาพ</span>
                        </div>
                        <div class="flex items-center gap-3 p-4 rounded-2xl bg-gray-50 border border-gray-100">
                            <div class="h-10 w-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center">
                                <i class="fas fa-truck"></i>
                            </div>
                            <span class="text-sm font-bold text-gray-700">จัดส่งรวดเร็ว</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include_once 'includes/footer.php'; ?>
