<?php include_once 'functions.php'; $config = get_config(); ?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($config['siteName'] ?? 'ThaiDeals'); ?> — รวมสินค้าดีลพิเศษ</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Kanit', sans-serif; }
        .bg-primary { background-color: #ef4444; }
        .text-primary { color: #ef4444; }
        .border-primary { border-color: #ef4444; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen flex flex-col">
    <header class="bg-white border-b sticky top-0 z-50">
        <div class="container mx-auto px-4 py-4 flex items-center justify-between">
            <a href="index.php" class="text-2xl font-bold text-primary flex items-center gap-2">
                <i class="fas fa-shopping-cart"></i>
                <?php echo htmlspecialchars($config['siteName'] ?? 'ThaiDeals'); ?>
            </a>
            <nav class="hidden md:flex items-center gap-6 text-sm font-medium">
                <a href="index.php" class="hover:text-primary transition-colors">หน้าแรก</a>
                <a href="about.php" class="hover:text-primary transition-colors">เกี่ยวกับเรา</a>
                <a href="contact.php" class="hover:text-primary transition-colors">ติดต่อเรา</a>
            </nav>
            <div class="flex items-center gap-4">
                <a href="admin.php" class="text-gray-400 hover:text-primary transition-colors">
                    <i class="fas fa-user-cog"></i>
                </a>
            </div>
        </div>
    </header>
