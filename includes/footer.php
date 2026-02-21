    <footer class="bg-white border-t mt-auto">
        <div class="container mx-auto px-4 py-12">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-12">
                <div class="space-y-4">
                    <h3 class="text-xl font-bold text-gray-900"><?php echo htmlspecialchars($config['siteName'] ?? 'ThaiDeals'); ?></h3>
                    <p class="text-sm text-gray-500 leading-relaxed">
                        รวมสินค้าดีลพิเศษ ลดราคา โปรโมชั่นสุดคุ้ม จากร้านค้าชั้นนำ 
                        อัปเดตทุกวันเพื่อให้คุณไม่พลาดทุกดีลเด็ด
                    </p>
                </div>
                <div class="space-y-4">
                    <h3 class="text-sm font-bold uppercase tracking-wider text-gray-900">เมนูหลัก</h3>
                    <nav class="flex flex-col gap-3">
                        <a href="index.php" class="text-sm text-gray-500 hover:text-primary transition-colors flex items-center gap-2">
                            <i class="fas fa-home w-4"></i> หน้าแรก
                        </a>
                        <a href="about.php" class="text-sm text-gray-500 hover:text-primary transition-colors flex items-center gap-2">
                            <i class="fas fa-info-circle w-4"></i> เกี่ยวกับเรา
                        </a>
                        <a href="contact.php" class="text-sm text-gray-500 hover:text-primary transition-colors flex items-center gap-2">
                            <i class="fas fa-envelope w-4"></i> ติดต่อเรา
                        </a>
                    </nav>
                </div>
                <div class="space-y-4">
                    <h3 class="text-sm font-bold uppercase tracking-wider text-gray-900">หมวดหมู่ยอดนิยม</h3>
                    <div class="flex flex-wrap gap-2">
                        <?php foreach (array_slice($config['categories'] ?? [], 0, 8) as $cat): ?>
                            <a href="category.php?name=<?php echo urlencode($cat); ?>" 
                               class="text-xs bg-gray-100 hover:bg-primary hover:text-white px-3 py-1.5 rounded-full transition-colors">
                                <?php echo htmlspecialchars($cat); ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <div class="border-t mt-12 pt-8 text-center">
                <p class="text-xs text-gray-400">
                    © 2026 <?php echo htmlspecialchars($config['siteName'] ?? 'ThaiDeals'); ?> — สินค้าดีลพิเศษ โปรโมชั่นสุดคุ้ม. All rights reserved.
                </p>
            </div>
        </div>
    </footer>
</body>
</html>
