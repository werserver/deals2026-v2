<?php 
require_once __DIR__ . '/functions.php'; 
$config = get_config(); 
$theme_color = $config['themeColor'] ?? '#ff6b00';
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($config['siteName']); ?> — รวมสินค้าดีลพิเศษ ลดราคา โปรโมชั่นสุดคุ้ม</title>
    <link rel="icon" type="image/x-icon" href="<?php echo htmlspecialchars($config['siteFavicon'] ?? '/favicon.ico'); ?>">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        * { font-family: 'Prompt', sans-serif !important; }
        body { line-height: 1.6; letter-spacing: 0.3px; }
        h1, h2, h3, h4, h5, h6 { letter-spacing: 0.5px; line-height: 1.3; }
        p, a, span, div { font-size: 14px; }
        .text-sm { font-size: 13px; }
        .text-base { font-size: 15px; }
        .text-lg { font-size: 17px; }
        .text-xl { font-size: 19px; }
        .text-2xl { font-size: 22px; }
        .font-semibold { font-weight: 600; }
        .font-bold { font-weight: 700; }
        .font-black { font-weight: 900; }
        :root {
            --primary: <?php echo $theme_color; ?>;
            --primary-dark: <?php echo $theme_color; ?>dd;
            --primary-light: <?php echo $theme_color; ?>22;
        }
        .text-primary { color: var(--primary) !important; }
        .bg-primary { background-color: var(--primary) !important; }
        .border-primary { border-color: var(--primary) !important; }
        .ring-primary { --tw-ring-color: var(--primary); }
        .btn-primary {
            background-color: var(--primary);
            color: white;
            transition: all 0.2s;
        }
        .btn-primary:hover { opacity: 0.9; transform: translateY(-1px); }
        .btn-outline-primary {
            border: 2px solid var(--primary);
            color: var(--primary);
            background: transparent;
            transition: all 0.2s;
        }
        .btn-outline-primary:hover {
            background-color: var(--primary);
            color: white;
        }
        .product-card:hover { transform: translateY(-4px); box-shadow: 0 20px 40px rgba(0,0,0,0.12); }
        .product-card { transition: all 0.3s ease; }
        .discount-badge {
            background: #ef4444;
            color: white;
            font-weight: 700;
            font-size: 0.75rem;
            padding: 2px 8px;
            border-radius: 999px;
        }
        .scrollbar-hide::-webkit-scrollbar { display: none; }
        .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
        .announcement-bar {
            background: linear-gradient(90deg, var(--primary), var(--primary-dark));
        }
        .star-filled { color: #f59e0b; }
        .star-empty { color: #d1d5db; }
        @keyframes fadeOut {
            to { opacity: 0; transform: translateY(10px); }
        }
        @keyframes slideIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .slide-in { animation: slideIn 0.4s ease-out; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen flex flex-col">

    <!-- Announcement Bar -->
    <div class="announcement-bar text-white text-center py-2 px-4 text-xs font-semibold">
        <i class="fas fa-tag mr-2"></i>
        ดีลพิเศษวันนี้! สินค้าลดราคาสูงสุด 50% — รีบสั่งซื้อก่อนหมดเขต!
    </div>

    <!-- Header -->
    <header class="bg-white shadow-sm sticky top-0 z-50">
        <div class="container mx-auto px-4 h-16 flex items-center justify-between">
            <!-- Logo -->
            <a href="index.php" class="flex items-center gap-2 group">
                <div class="h-9 w-9 rounded-xl flex items-center justify-center text-white shadow-md transition-transform group-hover:scale-110" style="background-color: <?php echo $theme_color; ?>;">
                    <i class="fas fa-shopping-bag text-sm"></i>
                </div>
                <span class="text-lg font-black text-gray-900 tracking-tight"><?php echo htmlspecialchars($config['siteName']); ?></span>
            </a>
            
            <!-- Desktop Nav -->
            <nav class="hidden md:flex items-center gap-6">
                <a href="index.php" class="text-sm font-semibold text-gray-700 hover:text-primary transition-colors" style="--primary: <?php echo $theme_color; ?>">หน้าแรก</a>
                <?php if (!empty($config['categories'])): ?>
                    <?php foreach (array_slice($config['categories'], 0, 4) as $cat): ?>
                        <?php $cat_name = is_array($cat) ? ($cat['name'] ?? '') : $cat; ?>
                        <a href="index.php?cat=<?php echo urlencode($cat_name); ?>" 
                           class="text-sm font-semibold text-gray-500 hover:text-primary transition-colors"
                           style="--primary: <?php echo $theme_color; ?>"><?php echo htmlspecialchars($cat_name); ?></a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </nav>

            <!-- Right Actions -->
            <div class="flex items-center gap-3">
                <!-- Mobile Menu Button -->
                <button id="mobileMenuBtn" class="md:hidden h-9 w-9 rounded-xl bg-gray-100 text-gray-500 hover:bg-gray-200 transition-all flex items-center justify-center">
                    <i class="fas fa-bars text-sm"></i>
                </button>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div id="mobileMenu" class="hidden md:hidden border-t border-gray-100 bg-white">
            <div class="container mx-auto px-4 py-3 flex flex-col gap-2">
                <a href="index.php" class="py-2 text-sm font-semibold text-gray-700">หน้าแรก</a>
                <?php if (!empty($config['categories'])): ?>
                    <?php foreach (array_slice($config['categories'], 0, 5) as $cat): ?>
                        <?php $cat_name = is_array($cat) ? ($cat['name'] ?? '') : $cat; ?>
                        <a href="index.php?cat=<?php echo urlencode($cat_name); ?>" class="py-2 text-sm font-semibold text-gray-500"><?php echo htmlspecialchars($cat_name); ?></a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <script>
        document.getElementById('mobileMenuBtn')?.addEventListener('click', function() {
            const menu = document.getElementById('mobileMenu');
            menu.classList.toggle('hidden');
        });
    </script>
