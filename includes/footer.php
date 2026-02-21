<?php
$config = get_config();
?>
    <footer class="bg-white border-t border-gray-100 pt-16 pb-8 mt-auto">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-12 mb-12">
                <div class="col-span-1 md:col-span-2">
                    <a href="index.php" class="flex items-center gap-3 mb-6">
                        <div class="h-10 w-10 rounded-xl flex items-center justify-center text-white shadow-lg" style="background-color: <?php echo $config['themeColor']; ?>;">
                            <i class="fas fa-shopping-bag"></i>
                        </div>
                        <span class="text-xl font-black text-gray-900 tracking-tight"><?php echo htmlspecialchars($config['siteName']); ?></span>
                    </a>
                    <p class="text-gray-400 text-sm leading-relaxed max-w-sm">
                        แหล่งรวมดีลสินค้าโปรโมชั่นที่ดีที่สุดจากทุกแพลตฟอร์ม ช้อปคุ้มกว่าใครด้วยระบบเปรียบเทียบราคาและรีวิวที่เชื่อถือได้
                    </p>
                </div>
                <div>
                    <h4 class="text-sm font-black text-gray-900 uppercase tracking-widest mb-6">หมวดหมู่ยอดนิยม</h4>
                    <ul class="space-y-4">
                        <?php if (!empty($config['categories'])): ?>
                            <?php foreach (array_slice($config['categories'], 0, 4) as $cat): ?>
                                <li><a href="index.php?cat=<?php echo urlencode($cat['name'] ?? $cat); ?>" class="text-sm font-bold text-gray-400 hover:text-primary transition-colors"><?php echo htmlspecialchars($cat['name'] ?? $cat); ?></a></li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                </div>
                <div>
                    <h4 class="text-sm font-black text-gray-900 uppercase tracking-widest mb-6">ติดต่อเรา</h4>
                    <div class="flex gap-4">
                        <a href="#" class="h-10 w-10 rounded-xl bg-gray-50 text-gray-400 hover:bg-blue-500 hover:text-white transition-all flex items-center justify-center"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="h-10 w-10 rounded-xl bg-gray-50 text-gray-400 hover:bg-green-500 hover:text-white transition-all flex items-center justify-center"><i class="fab fa-line"></i></a>
                        <a href="#" class="h-10 w-10 rounded-xl bg-gray-50 text-gray-400 hover:bg-pink-500 hover:text-white transition-all flex items-center justify-center"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
            </div>
            <div class="pt-8 border-t border-gray-50 flex flex-col md:flex-row justify-between items-center gap-4">
                <p class="text-xs font-bold text-gray-300">
                    &copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($config['siteName']); ?>. All rights reserved.
                </p>
                <div class="flex gap-6">
                    <a href="#" class="text-xs font-bold text-gray-300 hover:text-gray-500">ข้อกำหนดการใช้งาน</a>
                    <a href="#" class="text-xs font-bold text-gray-300 hover:text-gray-500">นโยบายความเป็นส่วนตัว</a>
                </div>
            </div>
        </div>
    </footer>
</body>
</html>
