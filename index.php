<?php
include_once 'includes/functions.php';
$config = get_config();
$keyword = $_GET['q'] ?? '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 20;

$all_products = get_all_products($keyword);
$total_products = count($all_products);
$total_pages = ceil($total_products / $per_page);
$page = max(1, min($page, $total_pages ?: 1));
$offset = ($page - 1) * $per_page;
$products = array_slice($all_products, $offset, $per_page);

include_once 'includes/header.php';
?>

<main class="container mx-auto px-4 py-8">
    <!-- Hero Section -->
    <div class="relative rounded-[2.5rem] overflow-hidden mb-12 bg-gradient-to-br from-red-500 to-red-600 text-white p-8 md:p-16 shadow-2xl shadow-red-200" style="background: linear-gradient(135deg, <?php echo $config['themeColor']; ?> 0%, <?php echo $config['themeColor']; ?>dd 100%);">
        <div class="relative z-10 max-w-2xl">
            <h1 class="text-4xl md:text-6xl font-black mb-6 leading-tight">
                ดีลเด็ดโดนใจ <br/>
                <span class="text-yellow-300">ลดสูงสุด 90%</span>
            </h1>
            <p class="text-lg md:text-xl opacity-90 mb-8 font-medium">
                รวบรวมสินค้าโปรโมชั่นจากทุกแพลตฟอร์มไว้ในที่เดียว ช้อปคุ้มกว่าใครได้แล้ววันนี้
            </p>
            
            <!-- Search Bar -->
            <form action="index.php" method="GET" class="relative group">
                <input type="text" name="q" value="<?php echo htmlspecialchars($keyword); ?>" 
                       placeholder="ค้นหาสินค้าที่คุณต้องการ..." 
                       class="w-full px-8 py-5 rounded-2xl bg-white text-gray-900 text-lg shadow-2xl outline-none focus:ring-4 focus:ring-white/20 transition-all pr-16">
                <button type="submit" class="absolute right-3 top-1/2 -translate-y-1/2 h-12 w-12 bg-gray-900 text-white rounded-xl flex items-center justify-center hover:bg-black transition-all">
                    <i class="fas fa-search"></i>
                </button>
            </form>

            <!-- Popular Keywords -->
            <?php if (!empty($config['keywords'])): ?>
                <div class="mt-6 flex flex-wrap gap-2 items-center">
                    <span class="text-sm font-bold opacity-80">ยอดนิยม:</span>
                    <?php foreach ($config['keywords'] as $kw): ?>
                        <a href="index.php?q=<?php echo urlencode($kw); ?>" class="text-sm bg-white/20 hover:bg-white/30 px-3 py-1 rounded-full transition-all backdrop-blur-sm">
                            <?php echo htmlspecialchars($kw); ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Decorative Elements -->
        <div class="absolute top-0 right-0 w-1/2 h-full opacity-10 pointer-events-none">
            <i class="fas fa-shopping-bag text-[20rem] absolute -top-20 -right-20 rotate-12"></i>
        </div>
    </div>

    <!-- Categories Section -->
    <?php if (!empty($config['categories'])): ?>
        <div class="mb-12">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-bold text-gray-900 flex items-center gap-3">
                    <span class="h-8 w-2 rounded-full bg-primary" style="background-color: <?php echo $config['themeColor']; ?>;"></span>
                    หมวดหมู่สินค้า
                </h2>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                <?php foreach ($config['categories'] as $cat): ?>
                    <a href="category.php?name=<?php echo urlencode($cat); ?>" 
                       class="group bg-white p-6 rounded-3xl border border-gray-100 shadow-sm hover:shadow-xl hover:border-primary/20 transition-all text-center">
                        <div class="h-16 w-16 bg-gray-50 rounded-2xl flex items-center justify-center mx-auto mb-4 group-hover:bg-primary/10 transition-colors">
                            <i class="fas fa-tag text-2xl text-gray-400 group-hover:text-primary" style="color: <?php echo $config['themeColor']; ?>;"></i>
                        </div>
                        <span class="font-bold text-gray-700 group-hover:text-primary transition-colors"><?php echo htmlspecialchars($cat); ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Products Grid -->
    <div class="mb-12">
        <div class="flex items-center justify-between mb-8">
            <h2 class="text-2xl font-bold text-gray-900 flex items-center gap-3">
                <span class="h-8 w-2 rounded-full bg-primary" style="background-color: <?php echo $config['themeColor']; ?>;"></span>
                <?php echo $keyword ? 'ผลการค้นหา: ' . htmlspecialchars($keyword) : 'สินค้าแนะนำสำหรับคุณ'; ?>
            </h2>
            <span class="text-sm font-bold text-gray-400"><?php echo $total_products; ?> รายการ</span>
        </div>

        <?php if (empty($products)): ?>
            <div class="text-center py-20 bg-gray-50 rounded-[3rem] border border-dashed border-gray-200">
                <div class="h-20 w-20 bg-white rounded-3xl flex items-center justify-center mx-auto mb-6 shadow-sm">
                    <i class="fas fa-search text-3xl text-gray-200"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">ไม่พบสินค้าที่คุณต้องการ</h3>
                <p class="text-gray-500">ลองค้นหาด้วยคำอื่น หรือเลือกดูตามหมวดหมู่สินค้า</p>
                <a href="index.php" class="inline-block mt-6 text-primary font-bold hover:underline" style="color: <?php echo $config['themeColor']; ?>;">ดูสินค้าทั้งหมด</a>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-6">
                <?php foreach ($products as $index => $p): ?>
                    <div class="group bg-white rounded-[2rem] border border-gray-100 shadow-sm hover:shadow-2xl hover:-translate-y-2 transition-all overflow-hidden flex flex-col">
                        <a href="product.php?id=<?php echo $offset + $index; ?><?php echo $keyword ? '&q='.urlencode($keyword) : ''; ?>" class="relative aspect-square overflow-hidden bg-gray-50">
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
                            <a href="product.php?id=<?php echo $offset + $index; ?><?php echo $keyword ? '&q='.urlencode($keyword) : ''; ?>" class="text-sm font-bold text-gray-800 line-clamp-2 mb-3 group-hover:text-primary transition-colors">
                                <?php echo htmlspecialchars($p['product_name_display']); ?>
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
                                <a href="<?php echo htmlspecialchars($p['cloaked_url']); ?>" target="_blank" 
                                   class="block w-full py-3 rounded-xl bg-gray-900 text-white text-center text-xs font-bold hover:bg-black transition-all shadow-lg shadow-gray-100">
                                    สั่งซื้อสินค้า
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div class="mt-16 flex justify-center items-center gap-2">
                    <?php if ($page > 1): ?>
                        <a href="index.php?page=<?php echo $page - 1; ?><?php echo $keyword ? '&q='.urlencode($keyword) : ''; ?>" class="h-12 w-12 flex items-center justify-center rounded-xl bg-white border border-gray-100 text-gray-600 hover:bg-gray-50 transition-all">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    <?php endif; ?>

                    <?php 
                    $start_page = max(1, $page - 2);
                    $end_page = min($total_pages, $page + 2);
                    for ($i = $start_page; $i <= $end_page; $i++): 
                    ?>
                        <a href="index.php?page=<?php echo $i; ?><?php echo $keyword ? '&q='.urlencode($keyword) : ''; ?>" 
                           class="h-12 w-12 flex items-center justify-center rounded-xl font-bold transition-all <?php echo $i === $page ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-100' : 'bg-white border border-gray-100 text-gray-600 hover:bg-gray-50'; ?>"
                           style="<?php echo $i === $page ? 'background-color: '.$config['themeColor'].';' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>

                    <?php if ($page < $total_pages): ?>
                        <a href="index.php?page=<?php echo $page + 1; ?><?php echo $keyword ? '&q='.urlencode($keyword) : ''; ?>" class="h-12 w-12 flex items-center justify-center rounded-xl bg-white border border-gray-100 text-gray-600 hover:bg-gray-50 transition-all">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</main>

<?php include_once 'includes/footer.php'; ?>
