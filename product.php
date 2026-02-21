<?php 
include_once 'includes/header.php'; 
$id = $_GET['id'] ?? '';
$products = get_all_products();
$product = null;

foreach ($products as $p) {
    if (($p['product_id'] ?? '') == $id) {
        $product = $p;
        break;
    }
}

if (!$product) {
    header("Location: index.php");
    exit;
}

$currentPrice = $product['product_discounted'] ?? $product['product_price'] ?? 0;
$hasDiscount = isset($product['product_discounted']) && $product['product_discounted'] < $product['product_price'];
?>

<main class="container mx-auto px-4 py-12 max-w-6xl">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
        <!-- Product Image -->
        <div class="space-y-6">
            <div class="aspect-square rounded-3xl overflow-hidden bg-white border border-gray-100 shadow-lg">
                <img src="<?php echo htmlspecialchars($product['product_image'] ?? ''); ?>" 
                     alt="<?php echo htmlspecialchars($product['product_name'] ?? ''); ?>"
                     class="w-full h-full object-contain p-8">
            </div>
        </div>

        <!-- Product Info -->
        <div class="space-y-8">
            <div class="space-y-4">
                <div class="flex items-center gap-2">
                    <span class="bg-primary/10 text-primary text-xs font-bold px-3 py-1 rounded-full">
                        <?php echo htmlspecialchars($product['category_name'] ?? 'ทั่วไป'); ?>
                    </span>
                </div>
                <h1 class="text-3xl font-bold text-gray-900 leading-tight">
                    <?php echo htmlspecialchars($product['product_name'] ?? ''); ?>
                </h1>
            </div>

            <div class="bg-white p-8 rounded-3xl border border-gray-100 shadow-sm space-y-6">
                <div class="flex items-baseline gap-4">
                    <span class="text-4xl font-bold text-primary">
                        <?php echo format_price($currentPrice); ?>
                    </span>
                    <?php if ($hasDiscount): ?>
                        <span class="text-xl text-gray-400 line-through">
                            <?php echo format_price($product['product_price']); ?>
                        </span>
                        <span class="bg-red-500 text-white text-sm font-bold px-3 py-1 rounded-lg">
                            ลด <?php echo $product['product_discounted_percentage']; ?>%
                        </span>
                    <?php endif; ?>
                </div>

                <div class="space-y-4 pt-6 border-t">
                    <a href="<?php echo htmlspecialchars($product['tracking_link'] ?? '#'); ?>" target="_blank"
                       class="block w-full bg-blue-600 text-white text-center py-4 rounded-2xl text-lg font-bold hover:bg-blue-700 transition-all shadow-lg shadow-blue-200">
                        ดูรายละเอียดเพิ่มเติม
                    </a>
                    <a href="<?php echo htmlspecialchars($product['tracking_link'] ?? '#'); ?>" target="_blank"
                       class="block w-full bg-primary text-white text-center py-4 rounded-2xl text-lg font-bold hover:bg-red-600 transition-all shadow-lg shadow-red-200">
                        สั่งซื้อสินค้านี้
                    </a>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="bg-white p-4 rounded-2xl border border-gray-100 flex items-center gap-4">
                    <div class="h-10 w-10 bg-green-100 text-green-600 rounded-full flex items-center justify-center">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <span class="text-sm font-medium">รับประกันคุณภาพ</span>
                </div>
                <div class="bg-white p-4 rounded-2xl border border-gray-100 flex items-center gap-4">
                    <div class="h-10 w-10 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center">
                        <i class="fas fa-truck"></i>
                    </div>
                    <span class="text-sm font-medium">จัดส่งรวดเร็ว</span>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include_once 'includes/footer.php'; ?>
