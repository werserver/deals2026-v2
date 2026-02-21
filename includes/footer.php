<?php
$config = get_config();
$theme_color = $config['themeColor'] ?? '#ff6b00';
?>
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
                    <p class="text-gray-400 text-sm leading-relaxed max-w-sm">
                        แหล่งรวมดีลสินค้าโปรโมชั่นที่ดีที่สุดจากทุกแพลตฟอร์ม ช้อปคุ้มกว่าใครด้วยระบบเปรียบเทียบราคาและรีวิวที่เชื่อถือได้
                    </p>
                </div>
                <div>
                    <h4 class="text-sm font-bold text-gray-900 uppercase tracking-widest mb-4">เมนูหลัก</h4>
                    <ul class="space-y-3">
                        <li><a href="index.php" class="text-sm text-gray-400 hover:text-gray-700 transition-colors flex items-center gap-2"><i class="fas fa-home text-xs"></i> หน้าแรก</a></li>
                        <li><a href="index.php" class="text-sm text-gray-400 hover:text-gray-700 transition-colors flex items-center gap-2"><i class="fas fa-th-large text-xs"></i> หมวดหมู่สินค้า</a></li>
                        <li><a href="about.php" class="text-sm text-gray-400 hover:text-gray-700 transition-colors flex items-center gap-2"><i class="fas fa-info-circle text-xs"></i> เกี่ยวกับเรา</a></li>
                        <li><a href="contact.php" class="text-sm text-gray-400 hover:text-gray-700 transition-colors flex items-center gap-2"><i class="fas fa-envelope text-xs"></i> ติดต่อเรา</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-sm font-bold text-gray-900 uppercase tracking-widest mb-4">หมวดหมู่ยอดนิยม</h4>
                    <ul class="space-y-3">
                        <?php if (!empty($config['categories'])): ?>
                            <?php foreach (array_slice($config['categories'], 0, 5) as $cat): ?>
                                <?php $cat_name = is_array($cat) ? ($cat['name'] ?? '') : $cat; ?>
                                <li><a href="index.php?cat=<?php echo urlencode($cat_name); ?>" class="text-sm text-gray-400 hover:text-gray-700 transition-colors"><?php echo htmlspecialchars($cat_name); ?></a></li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
            <div class="pt-6 border-t border-gray-100 flex flex-col md:flex-row justify-between items-center gap-4">
                <p class="text-xs text-gray-300">
                    &copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($config['siteName']); ?> — สินค้าดีลพิเศษ โปรโมชั่นสุดคุ้ม. All rights reserved.
                </p>
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
            <div class="bg-white rounded-2xl shadow-2xl border border-gray-100 overflow-hidden hover:shadow-3xl transition-all duration-300 cursor-pointer">
                <div class="p-4">
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0">
                            <?php if (!empty($notification['product_image'])): ?>
                                <img src="<?php echo htmlspecialchars($notification['product_image']); ?>" 
                                     alt="product" 
                                     class="w-14 h-14 rounded-xl object-cover bg-gray-50"
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
        function closeNotification() {
            const el = document.getElementById('purchaseNotification');
            if (el) {
                el.style.animation = 'fadeOut 0.3s ease-out forwards';
                setTimeout(() => el.remove(), 300);
            }
        }
        // Auto-hide after 8 seconds
        setTimeout(() => closeNotification(), 8000);
    </script>
</body>
</html>
