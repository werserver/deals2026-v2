<?php 
require_once 'functions.php'; 
$config = get_config(); 
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($config['siteName']); ?> — รวมสินค้าดีลพิเศษ</title>
    <link rel="icon" type="image/x-icon" href="<?php echo htmlspecialchars($config['siteFavicon'] ?? '/favicon.ico'); ?>">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap');
        body { font-family: 'Inter', sans-serif; }
        :root {
            --primary-color: <?php echo $config['themeColor']; ?>;
        }
        .text-primary { color: var(--primary-color); }
        .bg-primary { background-color: var(--primary-color); }
        .border-primary { border-color: var(--primary-color); }
        .scrollbar-hide::-webkit-scrollbar { display: none; }
        .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen flex flex-col">
    <header class="bg-white/80 backdrop-blur-md border-b border-gray-100 sticky top-0 z-50">
        <div class="container mx-auto px-4 h-20 flex items-center justify-between">
            <a href="index.php" class="flex items-center gap-3 group">
                <div class="h-10 w-10 rounded-xl flex items-center justify-center text-white shadow-lg transition-transform group-hover:scale-110" style="background-color: <?php echo $config['themeColor']; ?>;">
                    <i class="fas fa-shopping-bag"></i>
                </div>
                <span class="text-xl font-black text-gray-900 tracking-tight"><?php echo htmlspecialchars($config['siteName']); ?></span>
            </a>
            
            <nav class="hidden md:flex items-center gap-8">
                <a href="index.php" class="text-sm font-bold text-gray-900 hover:text-primary transition-colors">หน้าแรก</a>
                <?php if (!empty($config['categories'])): ?>
                    <?php foreach (array_slice($config['categories'], 0, 3) as $cat): ?>
                        <a href="index.php?cat=<?php echo urlencode($cat['name'] ?? $cat); ?>" class="text-sm font-bold text-gray-500 hover:text-primary transition-colors"><?php echo htmlspecialchars($cat['name'] ?? $cat); ?></a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </nav>

            <div class="flex items-center gap-4">
                <a href="admin.php" class="h-10 w-10 rounded-xl bg-gray-50 text-gray-400 hover:bg-gray-100 transition-all flex items-center justify-center">
                    <i class="fas fa-user-cog"></i>
                </a>
            </div>
        </div>
    </header>
