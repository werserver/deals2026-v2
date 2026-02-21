<?php
include_once 'includes/functions.php';
$config = get_config();
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$keyword = $_GET['q'] ?? '';
$category = $_GET['cat'] ?? '';

$products = get_all_products($keyword, $category);
$product = $products[$id] ?? null;

if (!$product) {
    header("Location: index.php");
    exit;
}

// Get related products (5 items)
$related_products = array_slice(array_filter($products, function($p, $idx) use ($id) {
    return $idx !== $id;
}, ARRAY_FILTER_USE_BOTH), 0, 5);

include_once 'includes/header.php';
?>

<main class="container mx-auto px-4 py-12">
    <!-- Breadcrumb -->
    <nav class="flex items-center gap-2 text-sm font-bold text-gray-400 mb-8 overflow-x-auto whitespace-nowrap pb-2">
        <a href="index.php" class="hover:text-primary transition-colors">หน้าแรก</a>
        <i class="fas fa-chevron-right text-[10px]"></i>
        <?php if ($product['category_name']): ?>
            <a href="category.php?name=<?php echo urlencode($product['category_name']); ?>" class="hover:text-primary transition-colors"><?php echo htmlspecialchars($product['category_name']); ?></a>
            <i class="fas fa-chevron-right text-[10px]"></i>
        <?php endif; ?>
        <span class="text-gray-900 truncate"><?php echo htmlspecialchars($product['product_name']); ?></span>
    </nav>

    <div class="grid lg:grid-cols-2 gap-12 mb-20">
        <!-- Product Image -->
        <div class="relative group">
            <div class="aspect-square rounded-[3rem] overflow-hidden bg-white border border-gray-100 shadow-2xl shadow-gray-100">
                <img src="<?php echo htmlspecialchars($product['product_image']); ?>" 
                     alt="<?php echo htmlspecialchars($product['product_name']); ?>"
                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700"
                     onerror="this.src='https://via.placeholder.com/800x800?text=No+Image'">
            </div>
            <?php if (!empty($product['product_discounted_percentage'])): ?>
                <div class="absolute top-8 left-8 bg-red-600 text-white text-sm font-black px-5 py-2 rounded-full shadow-xl">
                    ลด <?php echo htmlspecialchars($product['product_discounted_percentage']); ?>%
                </div>
            <?php endif; ?>
        </div>

        <!-- Product Info -->
        <div class="flex flex-col">
            <?php if ($config['flashSaleEnabled']): ?>
                <!-- Flash Sale Banner -->
                <div class="bg-gradient-to-r from-red-600 to-orange-500 text-white p-4 rounded-2xl mb-6 flex items-center justify-between shadow-lg shadow-red-100">
                    <div class="flex items-center gap-3">
                        <span class="text-xl">⚡</span>
                        <span class="font-black uppercase tracking-wider">Flash Sale — เวลาจำกัด!</span>
                    </div>
                    <div class="flex items-center gap-2 font-mono font-bold">
                        <span class="bg-white/20 px-2 py-1 rounded">02</span>:
                        <span class="bg-white/20 px-2 py-1 rounded">45</span>:
                        <span class="bg-white/20 px-2 py-1 rounded">12</span>
                    </div>
                </div>
            <?php endif; ?>

            <h1 class="text-3xl md:text-4xl font-black text-gray-900 mb-6 leading-tight">
                <?php echo htmlspecialchars($product['product_name_display']); ?>
            </h1>

            <div class="flex items-baseline gap-4 mb-8">
                <span class="text-5xl font-black text-primary" style="color: <?php echo $config['themeColor']; ?>">
                    <?php echo format_price($product['product_price']); ?>
                </span>
                <?php if (!empty($product['product_discounted'])): ?>
                    <span class="text-xl text-gray-400 line-through">
                        <?php echo format_price($product['product_discounted']); ?>
                    </span>
                <?php endif; ?>
            </div>

            <!-- Product Options -->
            <div class="mb-8">
                <h3 class="text-sm font-bold text-gray-400 uppercase tracking-widest mb-4">ตัวเลือกสินค้า</h3>
                <div class="flex flex-wrap gap-3">
                    <button class="px-6 py-3 rounded-xl border-2 border-primary bg-primary/5 text-primary font-bold" style="border-color: <?php echo $config['themeColor']; ?>; color: <?php echo $config['themeColor']; ?>; background-color: <?php echo $config['themeColor']; ?>10;">รุ่นมาตรฐาน</button>
                    <button class="px-6 py-3 rounded-xl border-2 border-gray-100 text-gray-400 font-bold hover:border-gray-200 transition-all">รุ่นพรีเมียม</button>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="grid sm:grid-cols-2 gap-4 mb-10">
                <a href="<?php echo htmlspecialchars($product['cloaked_url']); ?>" target="_blank" 
                   class="flex items-center justify-center gap-3 py-5 bg-gray-900 text-white rounded-2xl font-black text-lg hover:bg-black transition-all shadow-2xl shadow-gray-200">
                    <i class="fas fa-info-circle"></i> ดูรายละเอียดเพิ่มเติม
                </a>
                <a href="<?php echo htmlspecialchars($product['cloaked_url']); ?>" target="_blank" 
                   class="flex items-center justify-center gap-3 py-5 bg-primary text-white rounded-2xl font-black text-lg hover:opacity-90 transition-all shadow-2xl shadow-primary/20"
                   style="background-color: <?php echo $config['themeColor']; ?>;">
                    <i class="fas fa-shopping-cart"></i> สั่งซื้อสินค้านี้
                </a>
            </div>

            <!-- Product Features -->
            <div class="grid grid-cols-3 gap-4 py-8 border-y border-gray-100">
                <div class="text-center">
                    <div class="text-primary mb-2" style="color: <?php echo $config['themeColor']; ?>;"><i class="fas fa-shield-alt text-xl"></i></div>
                    <p class="text-[10px] font-bold text-gray-500 uppercase">รับประกันแท้ 100%</p>
                </div>
                <div class="text-center">
                    <div class="text-primary mb-2" style="color: <?php echo $config['themeColor']; ?>;"><i class="fas fa-truck text-xl"></i></div>
                    <p class="text-[10px] font-bold text-gray-500 uppercase">จัดส่งรวดเร็ว</p>
                </div>
                <div class="text-center">
                    <div class="text-primary mb-2" style="color: <?php echo $config['themeColor']; ?>;"><i class="fas fa-undo text-xl"></i></div>
                    <p class="text-[10px] font-bold text-gray-500 uppercase">คืนเงินใน 7 วัน</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Product Details Tabs -->
    <div class="mb-20">
        <div class="flex border-b border-gray-100 mb-8 overflow-x-auto">
            <button class="px-8 py-4 border-b-4 border-primary font-black text-gray-900 whitespace-nowrap" style="border-color: <?php echo $config['themeColor']; ?>;">ข้อมูลสินค้า</button>
            <button class="px-8 py-4 text-gray-400 font-bold hover:text-gray-600 whitespace-nowrap">เปรียบเทียบราคาจากหลายร้าน</button>
            <button class="px-8 py-4 text-gray-400 font-bold hover:text-gray-600 whitespace-nowrap">รีวิวจากผู้ซื้อ (5)</button>
        </div>
        
        <div class="prose prose-indigo max-w-none text-gray-600 leading-relaxed">
            <p class="mb-6">สัมผัสประสบการณ์การใช้งานที่เหนือระดับด้วย <?php echo htmlspecialchars($product['product_name']); ?> สินค้าคุณภาพที่ผ่านการคัดสรรมาอย่างดีเพื่อคุณโดยเฉพาะ โดดเด่นด้วยดีไซน์ที่ทันสมัยและฟังก์ชันการใช้งานที่ครบครัน ตอบโจทย์ทุกไลฟ์สไตล์การใช้ชีวิตในปัจจุบัน</p>
            
            <h4 class="text-gray-900 font-black mb-4">คุณสมบัติเด่น:</h4>
            <ul class="list-disc pl-6 space-y-2 mb-8">
                <li>ผลิตจากวัสดุคุณภาพสูง ทนทานต่อการใช้งาน</li>
                <li>ดีไซน์สวยงาม ทันสมัย เข้าได้กับทุกการแต่งกาย</li>
                <li>ฟังก์ชันการใช้งานที่ง่ายและสะดวกสบาย</li>
                <li>คุ้มค่าคุ้มราคา ด้วยส่วนลดพิเศษเฉพาะที่นี่เท่านั้น</li>
            </ul>

            <?php if ($config['aiReviewsEnabled']): ?>
                <div class="bg-indigo-50 p-8 rounded-[2rem] border border-indigo-100 mt-12">
                    <h4 class="text-indigo-900 font-black mb-6 flex items-center gap-3">
                        <i class="fas fa-robot"></i> AI Reviews & Insights
                    </h4>
                    <div class="space-y-6">
                        <div class="flex gap-4">
                            <div class="h-12 w-12 rounded-full bg-white flex items-center justify-center shadow-sm text-indigo-600 font-bold">A</div>
                            <div>
                                <p class="font-bold text-gray-900 mb-1">สมชาย รักษ์ดี <span class="text-xs text-gray-400 font-normal ml-2">ยืนยันการซื้อแล้ว</span></p>
                                <div class="flex text-yellow-400 text-xs mb-2"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div>
                                <p class="text-sm text-gray-600">สินค้าคุณภาพดีมากครับ คุ้มค่ากับราคาที่จ่ายไป การจัดส่งก็รวดเร็วทันใจ แนะนำเลยครับสำหรับใครที่กำลังลังเลอยู่</p>
                            </div>
                        </div>
                        <div class="flex gap-4">
                            <div class="h-12 w-12 rounded-full bg-white flex items-center justify-center shadow-sm text-indigo-600 font-bold">W</div>
                            <div>
                                <p class="font-bold text-gray-900 mb-1">วิภาวรรณ สวยงาม <span class="text-xs text-gray-400 font-normal ml-2">ยืนยันการซื้อแล้ว</span></p>
                                <div class="flex text-yellow-400 text-xs mb-2"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div>
                                <p class="text-sm text-gray-600">ได้รับสินค้าแล้วค่ะ แพ็คมาอย่างดี สินค้าตรงปกมาก สีสวยถูกใจ ใช้งานได้ดีไม่มีปัญหาอะไรเลยค่ะ</p>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Related Products -->
    <?php if (!empty($related_products)): ?>
        <div>
            <div class="flex items-center justify-between mb-8">
                <h2 class="text-2xl font-bold text-gray-900 flex items-center gap-3">
                    <span class="h-8 w-2 rounded-full bg-primary" style="background-color: <?php echo $config['themeColor']; ?>;"></span>
                    สินค้าที่คุณอาจสนใจ
                </h2>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-6">
                <?php foreach ($related_products as $index => $p): ?>
                    <div class="group bg-white rounded-[2rem] border border-gray-100 shadow-sm hover:shadow-xl transition-all overflow-hidden flex flex-col">
                        <a href="product.php?id=<?php echo $index; ?><?php echo $keyword ? '&q='.urlencode($keyword) : ''; ?><?php echo $category ? '&cat='.urlencode($category) : ''; ?>" class="relative aspect-square overflow-hidden bg-gray-50">
                            <img src="<?php echo htmlspecialchars($p['product_image']); ?>" 
                                 alt="<?php echo htmlspecialchars($p['product_name']); ?>"
                                 class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                                 onerror="this.src='https://via.placeholder.com/400x400?text=No+Image'">
                        </a>
                        <div class="p-4 flex flex-col flex-grow">
                            <a href="product.php?id=<?php echo $index; ?><?php echo $keyword ? '&q='.urlencode($keyword) : ''; ?><?php echo $category ? '&cat='.urlencode($category) : ''; ?>" class="text-xs font-bold text-gray-800 line-clamp-2 mb-2 group-hover:text-primary transition-colors">
                                <?php echo htmlspecialchars($p['product_name_display']); ?>
                            </a>
                            <div class="mt-auto">
                                <span class="text-lg font-black text-primary" style="color: <?php echo $config['themeColor']; ?>">
                                    <?php echo format_price($p['product_price']); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</main>

<?php include_once 'includes/footer.php'; ?>
