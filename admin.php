<?php 
include_once 'includes/functions.php';
$config = get_config();
$message = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Save Config
    if (isset($_POST['save_config'])) {
        $config['siteName'] = $_POST['siteName'];
        $config['themeColor'] = $_POST['themeColor'];
        $config['categories'] = array_filter(array_map('trim', explode(',', $_POST['categories'])));
        $config['keywords'] = array_filter(array_map('trim', explode(',', $_POST['keywords'])));
        save_config($config);
        $message = 'บันทึกการตั้งค่าเรียบร้อยแล้ว';
    }
    
    // Upload Main CSV
    if (isset($_FILES['main_csv'])) {
        $target_file = DATA_DIR . 'main.csv';
        if (move_uploaded_file($_FILES['main_csv']['tmp_name'], $target_file)) {
            $config['csvFileName'] = $_FILES['main_csv']['name'];
            save_config($config);
            $message = 'อัปโหลดไฟล์ main.csv เรียบร้อยแล้ว';
        } else {
            $error = 'เกิดข้อผิดพลาดในการอัปโหลดไฟล์ main.csv';
        }
    }

    // Upload Category CSV
    if (isset($_FILES['category_csv']) && isset($_POST['category_name'])) {
        $cat_name = $_POST['category_name'];
        $target_file = DATA_DIR . $cat_name . '.csv';
        if (move_uploaded_file($_FILES['category_csv']['tmp_name'], $target_file)) {
            $config['categoryCsvFileNames'][$cat_name] = $_FILES['category_csv']['name'];
            save_config($config);
            $message = 'อัปโหลดไฟล์ CSV สำหรับหมวดหมู่ ' . $cat_name . ' เรียบร้อยแล้ว';
        } else {
            $error = 'เกิดข้อผิดพลาดในการอัปโหลดไฟล์ CSV สำหรับหมวดหมู่ ' . $cat_name;
        }
    }

    // Delete Category CSV
    if (isset($_POST['delete_category_csv'])) {
        $cat_name = $_POST['delete_category_csv'];
        $target_file = DATA_DIR . $cat_name . '.csv';
        if (file_exists($target_file)) {
            unlink($target_file);
        }
        unset($config['categoryCsvFileNames'][$cat_name]);
        save_config($config);
        $message = 'ลบไฟล์ CSV สำหรับหมวดหมู่ ' . $cat_name . ' เรียบร้อยแล้ว';
    }
}

$theme_options = [
    ['name' => 'Red', 'value' => '#ef4444'],
    ['name' => 'Blue', 'value' => '#3b82f6'],
    ['name' => 'Green', 'value' => '#10b981'],
    ['name' => 'Orange', 'value' => '#f59e0b'],
    ['name' => 'Purple', 'value' => '#8b5cf6'],
    ['name' => 'Pink', 'value' => '#ec4899'],
    ['name' => 'Indigo', 'value' => '#6366f1'],
    ['name' => 'Teal', 'value' => '#14b8a6'],
];
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel — <?php echo htmlspecialchars($config['siteName']); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Kanit', sans-serif; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <div class="max-w-5xl mx-auto px-4 py-8 space-y-8">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="h-12 w-12 bg-blue-600 text-white rounded-2xl flex items-center justify-center text-2xl shadow-lg shadow-blue-200">
                    <i class="fas fa-tools"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Admin Panel</h1>
                    <p class="text-sm text-gray-500">จัดการระบบและตั้งค่าเว็บไซต์</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <a href="index.php" class="bg-white border border-gray-200 text-gray-700 px-5 py-2.5 rounded-xl text-sm font-bold hover:bg-gray-50 transition-all flex items-center gap-2">
                    <i class="fas fa-external-link-alt"></i> ดูหน้าเว็บ
                </a>
            </div>
        </div>

        <?php if ($message): ?>
            <div class="bg-green-50 border border-green-200 text-green-700 px-6 py-4 rounded-2xl flex items-center gap-3 animate-fade-in">
                <i class="fas fa-check-circle text-xl"></i>
                <span class="font-medium"><?php echo htmlspecialchars($message); ?></span>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="bg-red-50 border border-red-200 text-red-700 px-6 py-4 rounded-2xl flex items-center gap-3 animate-fade-in">
                <i class="fas fa-exclamation-circle text-xl"></i>
                <span class="font-medium"><?php echo htmlspecialchars($error); ?></span>
            </div>
        <?php endif; ?>

        <!-- Quick Actions -->
        <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm flex flex-wrap items-center gap-4">
            <div class="flex items-center gap-2">
                <button onclick="window.location.reload()" class="bg-gray-50 text-gray-700 px-4 py-2 rounded-xl text-sm font-bold hover:bg-gray-100 transition-all flex items-center gap-2">
                    <i class="fas fa-sync-alt"></i> รีเฟรชข้อมูล
                </button>
            </div>
            <div class="ml-auto">
                <form method="POST" class="inline">
                    <button type="submit" name="save_config" class="bg-blue-600 text-white px-6 py-2.5 rounded-xl text-sm font-bold hover:bg-blue-700 transition-all shadow-lg shadow-blue-200 flex items-center gap-2">
                        <i class="fas fa-save"></i> บันทึกการตั้งค่าทั้งหมด
                    </button>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left Column: Settings -->
            <div class="lg:col-span-2 space-y-8">
                <!-- Theme Color -->
                <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
                    <div class="p-6 border-b border-gray-50 flex items-center gap-3">
                        <i class="fas fa-palette text-blue-600"></i>
                        <h2 class="font-bold text-gray-900">ธีมสีเว็บไซต์</h2>
                    </div>
                    <div class="p-8">
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                            <?php foreach ($theme_options as $theme): ?>
                                <label class="relative cursor-pointer group">
                                    <input type="radio" name="themeColor" value="<?php echo $theme['value']; ?>" 
                                           form="main-form" class="peer sr-only" 
                                           <?php echo $config['themeColor'] === $theme['value'] ? 'checked' : ''; ?>>
                                    <div class="flex items-center gap-3 p-3 rounded-2xl border-2 border-gray-100 peer-checked:border-blue-600 peer-checked:bg-blue-50 transition-all group-hover:border-gray-200">
                                        <div class="h-6 w-6 rounded-full shadow-inner" style="background-color: <?php echo $theme['value']; ?>"></div>
                                        <span class="text-sm font-bold text-gray-700"><?php echo $theme['name']; ?></span>
                                    </div>
                                    <div class="absolute -top-2 -right-2 h-6 w-6 bg-blue-600 text-white rounded-full flex items-center justify-center text-[10px] opacity-0 peer-checked:opacity-100 transition-opacity shadow-lg">
                                        <i class="fas fa-check"></i>
                                    </div>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Site Settings -->
                <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
                    <div class="p-6 border-b border-gray-50 flex items-center gap-3">
                        <i class="fas fa-cog text-blue-600"></i>
                        <h2 class="font-bold text-gray-900">ข้อมูลพื้นฐาน</h2>
                    </div>
                    <div class="p-8">
                        <form id="main-form" method="POST" class="space-y-6">
                            <input type="hidden" name="save_config" value="1">
                            <div class="space-y-2">
                                <label class="text-sm font-bold text-gray-700 flex items-center gap-2">
                                    <i class="fas fa-globe text-gray-400"></i> ชื่อเว็บไซต์
                                </label>
                                <input type="text" name="siteName" value="<?php echo htmlspecialchars($config['siteName']); ?>" 
                                       class="w-full px-5 py-3 rounded-2xl border border-gray-200 bg-gray-50 focus:ring-2 focus:ring-blue-600 focus:bg-white outline-none transition-all"
                                       placeholder="ระบุชื่อเว็บไซต์">
                            </div>
                            <div class="space-y-2">
                                <label class="text-sm font-bold text-gray-700 flex items-center gap-2">
                                    <i class="fas fa-tags text-gray-400"></i> หมวดหมู่สินค้า (แยกด้วยเครื่องหมายคอมม่า ,)
                                </label>
                                <textarea name="categories" class="w-full px-5 py-3 rounded-2xl border border-gray-200 bg-gray-50 focus:ring-2 focus:ring-blue-600 focus:bg-white outline-none transition-all min-h-[100px]"
                                          placeholder="เช่น Electronics, Fashion, Home & Living"><?php echo htmlspecialchars(implode(', ', $config['categories'])); ?></textarea>
                            </div>
                            <div class="space-y-2">
                                <label class="text-sm font-bold text-gray-700 flex items-center gap-2">
                                    <i class="fas fa-search text-gray-400"></i> คำค้นหาแนะนำ (แยกด้วยเครื่องหมายคอมม่า ,)
                                </label>
                                <textarea name="keywords" class="w-full px-5 py-3 rounded-2xl border border-gray-200 bg-gray-50 focus:ring-2 focus:ring-blue-600 focus:bg-white outline-none transition-all min-h-[100px]"
                                          placeholder="เช่น iPhone 15, Samsung S24, Nike"><?php echo htmlspecialchars(implode(', ', $config['keywords'])); ?></textarea>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Main CSV Upload -->
                <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
                    <div class="p-6 border-b border-gray-50 flex items-center gap-3">
                        <i class="fas fa-file-csv text-blue-600"></i>
                        <h2 class="font-bold text-gray-900">ไฟล์สินค้าหลัก (main.csv)</h2>
                    </div>
                    <div class="p-8 flex items-center justify-between gap-8">
                        <div class="flex items-center gap-4">
                            <div class="h-14 w-14 bg-green-50 text-green-600 rounded-2xl flex items-center justify-center text-2xl">
                                <i class="fas fa-file-spreadsheet"></i>
                            </div>
                            <div>
                                <p class="font-bold text-gray-900"><?php echo htmlspecialchars($config['csvFileName'] ?? 'ยังไม่มีไฟล์'); ?></p>
                                <p class="text-xs text-gray-500">ไฟล์สินค้าที่จะแสดงในหน้าแรก</p>
                            </div>
                        </div>
                        <form method="POST" enctype="multipart/form-data" class="flex items-center gap-3">
                            <label class="cursor-pointer bg-blue-50 text-blue-600 px-5 py-2.5 rounded-xl text-sm font-bold hover:bg-blue-100 transition-all flex items-center gap-2">
                                <i class="fas fa-upload"></i> เลือกไฟล์ใหม่
                                <input type="file" name="main_csv" accept=".csv" class="hidden" onchange="this.form.submit()">
                            </label>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Right Column: Category CSVs -->
            <div class="space-y-8">
                <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
                    <div class="p-6 border-b border-gray-50 flex items-center gap-3">
                        <i class="fas fa-folder-open text-blue-600"></i>
                        <h2 class="font-bold text-gray-900">ไฟล์รายหมวดหมู่</h2>
                    </div>
                    <div class="p-6 space-y-4">
                        <?php if (empty($config['categories'])): ?>
                            <p class="text-center py-8 text-gray-400 text-sm italic">กรุณาเพิ่มหมวดหมู่ก่อน</p>
                        <?php else: ?>
                            <?php foreach ($config['categories'] as $cat): ?>
                                <div class="p-4 rounded-2xl border border-gray-50 bg-gray-50/50 space-y-3">
                                    <div class="flex items-center justify-between">
                                        <span class="font-bold text-gray-800"><?php echo htmlspecialchars($cat); ?></span>
                                        <?php if (isset($config['categoryCsvFileNames'][$cat])): ?>
                                            <span class="text-[10px] bg-green-100 text-green-600 px-2 py-0.5 rounded-full font-bold">มีไฟล์แล้ว</span>
                                        <?php else: ?>
                                            <span class="text-[10px] bg-gray-200 text-gray-500 px-2 py-0.5 rounded-full font-bold">ไม่มีไฟล์</span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <?php if (isset($config['categoryCsvFileNames'][$cat])): ?>
                                        <p class="text-[10px] text-gray-400 truncate"><?php echo htmlspecialchars($config['categoryCsvFileNames'][$cat]); ?></p>
                                    <?php endif; ?>

                                    <div class="flex items-center gap-2">
                                        <form method="POST" enctype="multipart/form-data" class="flex-grow">
                                            <input type="hidden" name="category_name" value="<?php echo htmlspecialchars($cat); ?>">
                                            <label class="w-full cursor-pointer bg-white border border-gray-200 text-gray-700 px-3 py-2 rounded-lg text-xs font-bold hover:bg-gray-50 transition-all flex items-center justify-center gap-2">
                                                <i class="fas fa-upload"></i> อัปโหลด
                                                <input type="file" name="category_csv" accept=".csv" class="hidden" onchange="this.form.submit()">
                                            </label>
                                        </form>
                                        <?php if (isset($config['categoryCsvFileNames'][$cat])): ?>
                                            <form method="POST" class="flex-shrink-0">
                                                <input type="hidden" name="delete_category_csv" value="<?php echo htmlspecialchars($cat); ?>">
                                                <button type="submit" class="h-8 w-8 bg-red-50 text-red-500 rounded-lg hover:bg-red-100 transition-all flex items-center justify-center" title="ลบไฟล์">
                                                    <i class="fas fa-trash-alt text-xs"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Info Card -->
                <div class="bg-gradient-to-br from-blue-600 to-indigo-700 p-8 rounded-3xl text-white shadow-xl shadow-blue-200 space-y-4">
                    <div class="h-12 w-12 bg-white/20 rounded-2xl flex items-center justify-center text-2xl">
                        <i class="fas fa-lightbulb"></i>
                    </div>
                    <h3 class="text-xl font-bold">คำแนะนำ</h3>
                    <p class="text-sm text-white/80 leading-relaxed">
                        การอัปโหลดไฟล์ CSV รายหมวดหมู่จะช่วยให้หน้าหมวดหมู่นั้นๆ แสดงสินค้าได้รวดเร็วขึ้น หากไม่มีไฟล์ ระบบจะดึงข้อมูลจากไฟล์หลัก (main.csv) มาแสดงแทน
                    </p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
