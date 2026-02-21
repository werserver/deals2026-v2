<?php 
include_once 'includes/functions.php';
$config = get_config();
$message = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['save_config'])) {
        $config['siteName'] = $_POST['siteName'];
        $config['categories'] = array_filter(array_map('trim', explode(',', $_POST['categories'])));
        $config['keywords'] = array_filter(array_map('trim', explode(',', $_POST['keywords'])));
        save_config($config);
        $message = 'บันทึกการตั้งค่าเรียบร้อยแล้ว';
    }
    
    if (isset($_FILES['csv_file'])) {
        $target_file = DATA_DIR . ($_POST['csv_type'] === 'main' ? 'main.csv' : $_POST['category_name'] . '.csv');
        if (move_uploaded_file($_FILES['csv_file']['tmp_name'], $target_file)) {
            if ($_POST['csv_type'] !== 'main') {
                $config['categoryCsvMap'][$_POST['category_name']] = $_POST['category_name'] . '.csv';
                save_config($config);
            }
            $message = 'อัปโหลดไฟล์ CSV เรียบร้อยแล้ว';
        } else {
            $message = 'เกิดข้อผิดพลาดในการอัปโหลดไฟล์';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>Admin Panel — <?php echo htmlspecialchars($config['siteName']); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100 min-h-screen p-8">
    <div class="max-w-4xl mx-auto space-y-8">
        <div class="flex items-center justify-between">
            <h1 class="text-3xl font-bold text-gray-900">Admin Panel</h1>
            <a href="index.php" class="text-blue-600 hover:underline">กลับหน้าหลัก</a>
        </div>

        <?php if ($message): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <!-- Config Section -->
        <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-200 space-y-6">
            <h2 class="text-xl font-bold border-b pb-4">ตั้งค่าเว็บไซต์</h2>
            <form method="POST" class="space-y-4">
                <div class="space-y-2">
                    <label class="text-sm font-medium">ชื่อเว็บไซต์</label>
                    <input type="text" name="siteName" value="<?php echo htmlspecialchars($config['siteName']); ?>" 
                           class="w-full px-4 py-2 rounded-lg border focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-medium">หมวดหมู่ (แยกด้วยเครื่องหมายคอมม่า ,)</label>
                    <textarea name="categories" class="w-full px-4 py-2 rounded-lg border focus:ring-2 focus:ring-blue-500 outline-none h-24"><?php echo htmlspecialchars(implode(', ', $config['categories'])); ?></textarea>
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-medium">คำค้นหาแนะนำ (แยกด้วยเครื่องหมายคอมม่า ,)</label>
                    <textarea name="keywords" class="w-full px-4 py-2 rounded-lg border focus:ring-2 focus:ring-blue-500 outline-none h-24"><?php echo htmlspecialchars(implode(', ', $config['keywords'])); ?></textarea>
                </div>
                <button type="submit" name="save_config" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                    บันทึกการตั้งค่า
                </button>
            </form>
        </div>

        <!-- CSV Upload Section -->
        <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-200 space-y-6">
            <h2 class="text-xl font-bold border-b pb-4">อัปโหลดไฟล์ CSV</h2>
            <form method="POST" enctype="multipart/form-data" class="space-y-6">
                <div class="space-y-2">
                    <label class="text-sm font-medium">ประเภทไฟล์</label>
                    <select name="csv_type" class="w-full px-4 py-2 rounded-lg border focus:ring-2 focus:ring-blue-500 outline-none" onchange="toggleCategory(this.value)">
                        <option value="main">ไฟล์หลัก (main.csv)</option>
                        <option value="category">ไฟล์รายหมวดหมู่</option>
                    </select>
                </div>
                <div id="category_select" class="space-y-2 hidden">
                    <label class="text-sm font-medium">เลือกหมวดหมู่</label>
                    <select name="category_name" class="w-full px-4 py-2 rounded-lg border focus:ring-2 focus:ring-blue-500 outline-none">
                        <?php foreach ($config['categories'] as $cat): ?>
                            <option value="<?php echo htmlspecialchars($cat); ?>"><?php echo htmlspecialchars($cat); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-medium">เลือกไฟล์ CSV</label>
                    <input type="file" name="csv_file" accept=".csv" required 
                           class="w-full px-4 py-2 rounded-lg border focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 transition-colors">
                    อัปโหลดไฟล์
                </button>
            </form>
        </div>
    </div>

    <script>
        function toggleCategory(val) {
            document.getElementById('category_select').classList.toggle('hidden', val !== 'category');
        }
    </script>
</body>
</html>
