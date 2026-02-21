<?php
include_once 'includes/functions.php';

// Handle Login
if (isset($_POST['login'])) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    if ($username === 'admin' && $password === 'sofaraway') {
        $_SESSION['admin_logged_in'] = true;
    } else {
        $error = "ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง";
    }
}

// Handle Logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: admin.php");
    exit;
}

// Check Login
if (!is_logged_in()) {
    include_once 'includes/header.php';
    ?>
    <div class="min-h-[70vh] flex items-center justify-center px-4">
        <div class="max-w-md w-full bg-white rounded-3xl shadow-xl border border-gray-100 p-8">
            <div class="text-center mb-8">
                <div class="h-16 w-16 bg-red-100 text-red-600 rounded-2xl flex items-center justify-center mx-auto mb-4 text-2xl">
                    <i class="fas fa-lock"></i>
                </div>
                <h1 class="text-2xl font-bold text-gray-900">เข้าสู่ระบบ Admin</h1>
                <p class="text-gray-500">กรุณาเข้าสู่ระบบเพื่อจัดการเว็บไซต์</p>
            </div>
            
            <?php if (isset($error)): ?>
                <div class="bg-red-50 text-red-600 p-4 rounded-xl mb-6 text-sm font-medium border border-red-100">
                    <i class="fas fa-exclamation-circle mr-2"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form action="admin.php" method="POST" class="space-y-6">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">ชื่อผู้ใช้</label>
                    <input type="text" name="username" required class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-4 focus:ring-red-500/10 focus:border-red-500 outline-none transition-all">
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">รหัสผ่าน</label>
                    <input type="password" name="password" required class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-4 focus:ring-red-500/10 focus:border-red-500 outline-none transition-all">
                </div>
                <button type="submit" name="login" class="w-full bg-red-600 text-white py-4 rounded-xl font-bold hover:bg-red-700 transition-all shadow-lg shadow-red-200">
                    เข้าสู่ระบบ
                </button>
            </form>
        </div>
    </div>
    <?php
    include_once 'includes/footer.php';
    exit;
}

// Handle Actions (Save Config, Upload CSV, Delete Category)
$config = get_config();
$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['save_settings'])) {
        $config['siteName'] = $_POST['siteName'];
        $config['themeColor'] = $_POST['themeColor'];
        $config['keywords'] = array_filter(array_map('trim', explode(',', $_POST['keywords'])));
        save_config($config);
        $message = "บันทึกการตั้งค่าเรียบร้อยแล้ว";
    }

    if (isset($_POST['add_category'])) {
        $new_cat = trim($_POST['new_category']);
        if ($new_cat && !in_array($new_cat, $config['categories'])) {
            $config['categories'][] = $new_cat;
            save_config($config);
            $message = "เพิ่มหมวดหมู่เรียบร้อยแล้ว";
        }
    }

    if (isset($_POST['upload_main_csv'])) {
        if (isset($_FILES['main_csv']) && $_FILES['main_csv']['error'] === 0) {
            move_uploaded_file($_FILES['main_csv']['tmp_name'], MAIN_CSV);
            clear_cache();
            $message = "อัปโหลดไฟล์ CSV หลักเรียบร้อยแล้ว";
        }
    }

    if (isset($_POST['upload_category_csv'])) {
        $cat_name = $_POST['category_name'];
        if (isset($_FILES['category_csv']) && $_FILES['category_csv']['error'] === 0) {
            $target = DATA_DIR . '/' . $cat_name . '.csv';
            move_uploaded_file($_FILES['category_csv']['tmp_name'], $target);
            $config['categoryCsvFileNames'][$cat_name] = $_FILES['category_csv']['name'];
            save_config($config);
            clear_cache();
            $message = "อัปโหลดไฟล์ CSV สำหรับหมวดหมู่ $cat_name เรียบร้อยแล้ว";
        }
    }
}

if (isset($_GET['delete_category'])) {
    $cat_to_delete = $_GET['delete_category'];
    $config['categories'] = array_values(array_diff($config['categories'], [$cat_to_delete]));
    unset($config['categoryCsvFileNames'][$cat_to_delete]);
    $cat_file = DATA_DIR . '/' . $cat_to_delete . '.csv';
    if (file_exists($cat_file)) unlink($cat_file);
    save_config($config);
    clear_cache();
    header("Location: admin.php?msg=deleted");
    exit;
}

include_once 'includes/header.php';
?>

<main class="container mx-auto px-4 py-8 max-w-5xl">
    <div class="flex items-center justify-between mb-8">
        <h1 class="text-3xl font-bold text-gray-900">แผงควบคุม Admin</h1>
        <a href="admin.php?logout=1" class="text-red-600 font-bold hover:underline">ออกจากระบบ</a>
    </div>

    <?php if ($message || isset($_GET['msg'])): ?>
        <div class="bg-green-50 text-green-600 p-4 rounded-2xl mb-8 border border-green-100 font-medium">
            <i class="fas fa-check-circle mr-2"></i> <?php echo $message ?: "ดำเนินการเรียบร้อยแล้ว"; ?>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <!-- Site Settings -->
        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8 space-y-6">
            <h2 class="text-xl font-bold flex items-center gap-2">
                <i class="fas fa-cog text-gray-400"></i> ตั้งค่าเว็บไซต์
            </h2>
            <form action="admin.php" method="POST" class="space-y-4">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">ชื่อเว็บไซต์</label>
                    <input type="text" name="siteName" value="<?php echo htmlspecialchars($config['siteName']); ?>" class="w-full px-4 py-3 rounded-xl border border-gray-200 outline-none focus:border-red-500 transition-all">
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">สีธีม (Hex Code)</label>
                    <div class="flex gap-2">
                        <input type="color" name="themeColor" value="<?php echo htmlspecialchars($config['themeColor']); ?>" class="h-12 w-12 rounded-lg border-0 p-1 cursor-pointer">
                        <input type="text" name="themeColor" value="<?php echo htmlspecialchars($config['themeColor']); ?>" class="flex-grow px-4 py-3 rounded-xl border border-gray-200 outline-none focus:border-red-500 transition-all">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">คำค้นหายอดนิยม (แยกด้วยเครื่องหมาย ,)</label>
                    <textarea name="keywords" rows="3" class="w-full px-4 py-3 rounded-xl border border-gray-200 outline-none focus:border-red-500 transition-all"><?php echo htmlspecialchars(implode(', ', $config['keywords'])); ?></textarea>
                </div>
                <button type="submit" name="save_settings" class="w-full bg-gray-900 text-white py-3 rounded-xl font-bold hover:bg-black transition-all">
                    บันทึกการตั้งค่า
                </button>
            </form>
        </div>

        <!-- Main CSV Upload -->
        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8 space-y-6">
            <h2 class="text-xl font-bold flex items-center gap-2">
                <i class="fas fa-file-csv text-gray-400"></i> จัดการไฟล์ CSV หลัก
            </h2>
            <div class="p-6 bg-gray-50 rounded-2xl border border-dashed border-gray-200 text-center">
                <i class="fas fa-cloud-upload-alt text-4xl text-gray-300 mb-4"></i>
                <p class="text-sm text-gray-500 mb-4">อัปโหลดไฟล์ main.csv เพื่อแสดงสินค้าในหน้าแรก</p>
                <form action="admin.php" method="POST" enctype="multipart/form-data">
                    <input type="file" name="main_csv" accept=".csv" required class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-red-50 file:text-red-700 hover:file:bg-red-100 mb-4">
                    <button type="submit" name="upload_main_csv" class="w-full bg-red-600 text-white py-3 rounded-xl font-bold hover:bg-red-700 transition-all">
                        อัปโหลด CSV หลัก
                    </button>
                </form>
            </div>
            <?php if (file_exists(MAIN_CSV)): ?>
                <div class="flex items-center justify-between text-sm text-gray-500 bg-white p-3 rounded-xl border border-gray-100">
                    <span><i class="fas fa-check-circle text-green-500 mr-2"></i> มีไฟล์ main.csv อยู่แล้ว</span>
                    <span><?php echo date("d/m/Y H:i", filemtime(MAIN_CSV)); ?></span>
                </div>
            <?php endif; ?>
        </div>

        <!-- Categories Management -->
        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8 space-y-6 md:col-span-2">
            <h2 class="text-xl font-bold flex items-center gap-2">
                <i class="fas fa-tags text-gray-400"></i> จัดการหมวดหมู่และไฟล์ CSV รายหมวดหมู่
            </h2>
            
            <form action="admin.php" method="POST" class="flex gap-2">
                <input type="text" name="new_category" placeholder="ชื่อหมวดหมู่ใหม่..." required class="flex-grow px-4 py-3 rounded-xl border border-gray-200 outline-none focus:border-red-500 transition-all">
                <button type="submit" name="add_category" class="bg-gray-900 text-white px-6 py-3 rounded-xl font-bold hover:bg-black transition-all">
                    เพิ่มหมวดหมู่
                </button>
            </form>

            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="border-b border-gray-100">
                            <th class="py-4 font-bold text-gray-700">ชื่อหมวดหมู่</th>
                            <th class="py-4 font-bold text-gray-700">ไฟล์ CSV</th>
                            <th class="py-4 font-bold text-gray-700">การจัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($config['categories'] as $cat): ?>
                            <tr class="border-b border-gray-50">
                                <td class="py-4 font-medium text-gray-900"><?php echo htmlspecialchars($cat); ?></td>
                                <td class="py-4">
                                    <form action="admin.php" method="POST" enctype="multipart/form-data" class="flex items-center gap-2">
                                        <input type="hidden" name="category_name" value="<?php echo htmlspecialchars($cat); ?>">
                                        <input type="file" name="category_csv" accept=".csv" required class="text-xs text-gray-500 file:mr-2 file:py-1 file:px-3 file:rounded-full file:border-0 file:text-xs file:bg-gray-100">
                                        <button type="submit" name="upload_category_csv" class="text-xs bg-blue-50 text-blue-600 px-3 py-1 rounded-full font-bold hover:bg-blue-100">
                                            อัปโหลด
                                        </button>
                                    </form>
                                    <?php if (isset($config['categoryCsvFileNames'][$cat])): ?>
                                        <div class="text-[10px] text-green-600 mt-1">
                                            <i class="fas fa-file-alt mr-1"></i> <?php echo htmlspecialchars($config['categoryCsvFileNames'][$cat]); ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="py-4">
                                    <a href="admin.php?delete_category=<?php echo urlencode($cat); ?>" onclick="return confirm('ยืนยันการลบหมวดหมู่และไฟล์ที่เกี่ยวข้อง?')" class="text-red-500 hover:text-red-700 text-sm font-bold">
                                        <i class="fas fa-trash-alt"></i> ลบ
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($config['categories'])): ?>
                            <tr>
                                <td colspan="3" class="py-8 text-center text-gray-400 italic">ยังไม่มีหมวดหมู่สินค้า</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<?php include_once 'includes/footer.php'; ?>
