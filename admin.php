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

if (!is_logged_in()) {
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - ThaiDeals</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-50 flex items-center justify-center min-h-screen p-4">
    <div class="bg-white p-8 rounded-[2.5rem] shadow-2xl w-full max-w-md border border-gray-100">
        <div class="text-center mb-8">
            <div class="h-20 w-20 bg-indigo-600 text-white rounded-3xl flex items-center justify-center mx-auto mb-4 shadow-lg shadow-indigo-200">
                <i class="fas fa-lock text-3xl"></i>
            </div>
            <h1 class="text-2xl font-black text-gray-900">Admin Login</h1>
            <p class="text-gray-500">กรุณาเข้าสู่ระบบเพื่อจัดการเว็บไซต์</p>
        </div>
        
        <?php if (isset($error)): ?>
            <div class="bg-red-50 text-red-600 p-4 rounded-2xl mb-6 text-sm font-bold flex items-center gap-3">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form action="admin.php" method="POST" class="space-y-6">
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">ชื่อผู้ใช้</label>
                <input type="text" name="username" required class="w-full px-6 py-4 rounded-2xl bg-gray-50 border-0 focus:ring-4 focus:ring-indigo-100 outline-none transition-all" placeholder="admin">
            </div>
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">รหัสผ่าน</label>
                <input type="password" name="password" required class="w-full px-6 py-4 rounded-2xl bg-gray-50 border-0 focus:ring-4 focus:ring-indigo-100 outline-none transition-all" placeholder="••••••••">
            </div>
            <button type="submit" name="login" class="w-full py-4 bg-indigo-600 text-white rounded-2xl font-black hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-200">
                เข้าสู่ระบบ
            </button>
        </form>
    </div>
</body>
</html>
<?php
    exit;
}

$config = get_config();

// Handle Save Config
if (isset($_POST['save_config'])) {
    $config['siteName'] = $_POST['siteName'] ?? $config['siteName'];
    $config['favicon'] = $_POST['favicon'] ?? $config['favicon'];
    $config['themeColor'] = $_POST['themeColor'] ?? $config['themeColor'];
    $config['cloakingBaseUrl'] = $_POST['cloakingBaseUrl'] ?? $config['cloakingBaseUrl'];
    $config['cloakingToken'] = $_POST['cloakingToken'] ?? $config['cloakingToken'];
    $config['flashSaleEnabled'] = isset($_POST['flashSaleEnabled']);
    $config['aiReviewsEnabled'] = isset($_POST['aiReviewsEnabled']);
    $config['prefixWordsEnabled'] = isset($_POST['prefixWordsEnabled']);
    
    // Keywords
    if (!empty($_POST['keywords_str'])) {
        $config['keywords'] = array_map('trim', explode(',', $_POST['keywords_str']));
    }
    
    // Prefix Words
    if (!empty($_POST['prefixWords_str'])) {
        $config['prefixWords'] = array_map('trim', explode(',', $_POST['prefixWords_str']));
    }

    save_config($config);
    $success = "บันทึกการตั้งค่าเรียบร้อยแล้ว";
}

// Handle Add Category
if (isset($_POST['add_category'])) {
    $new_cat = trim($_POST['new_category']);
    if ($new_cat && !in_array($new_cat, $config['categories'])) {
        $config['categories'][] = $new_cat;
        save_config($config);
    }
}

// Handle Delete Category
if (isset($_GET['del_cat'])) {
    $cat_to_del = $_GET['del_cat'];
    $config['categories'] = array_values(array_filter($config['categories'], fn($c) => $c !== $cat_to_del));
    save_config($config);
    header("Location: admin.php");
    exit;
}

// Handle CSV Upload
if (isset($_POST['upload_csv'])) {
    $target_cat = $_POST['target_category'] ?? 'main';
    if (isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] == 0) {
        $filename = ($target_cat === 'main') ? 'main.csv' : $target_cat . '.csv';
        move_uploaded_file($_FILES['csv_file']['tmp_name'], DATA_DIR . '/' . $filename);
        clear_cache();
        $success = "อัปโหลดไฟล์ CSV เรียบร้อยแล้ว";
    }
}

// Handle Export Config
if (isset($_GET['export'])) {
    header('Content-Type: application/json');
    header('Content-Disposition: attachment; filename="config_export.json"');
    echo json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

// Handle Import Config
if (isset($_POST['import_config'])) {
    if (isset($_FILES['config_file']) && $_FILES['config_file']['error'] == 0) {
        $imported = json_decode(file_get_contents($_FILES['config_file']['tmp_name']), true);
        if ($imported) {
            save_config($imported);
            $config = get_config();
            $success = "นำเข้าการตั้งค่าเรียบร้อยแล้ว";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - <?php echo htmlspecialchars($config['siteName']); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap');
        body { font-family: 'Inter', sans-serif; }
        .card { @apply bg-white rounded-[2rem] border border-gray-100 shadow-sm p-8 mb-8; }
        .section-title { @apply text-lg font-black text-gray-900 mb-6 flex items-center gap-3; }
        .input-label { @apply block text-sm font-bold text-gray-500 mb-2; }
        .input-field { @apply w-full px-6 py-4 rounded-2xl bg-gray-50 border-0 focus:ring-4 focus:ring-indigo-100 outline-none transition-all; }
        .btn-primary { @apply px-8 py-4 bg-indigo-600 text-white rounded-2xl font-black hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-100 flex items-center gap-2; }
        .btn-secondary { @apply px-6 py-3 bg-gray-100 text-gray-600 rounded-xl font-bold hover:bg-gray-200 transition-all flex items-center gap-2; }
        .toggle-checkbox:checked { @apply right-0 border-indigo-600; }
        .toggle-checkbox:checked + .toggle-label { @apply bg-indigo-600; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen pb-20">
    <!-- Header -->
    <header class="bg-white border-b border-gray-100 sticky top-0 z-50">
        <div class="container mx-auto px-4 h-20 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="h-10 w-10 bg-indigo-600 text-white rounded-xl flex items-center justify-center shadow-lg shadow-indigo-100">
                    <i class="fas fa-tools"></i>
                </div>
                <h1 class="text-xl font-black text-gray-900">Admin Panel</h1>
            </div>
            <div class="flex items-center gap-4">
                <a href="admin.php?export=1" class="btn-secondary text-sm">
                    <i class="fas fa-file-export"></i> Export Config
                </a>
                <button onclick="document.getElementById('import-input').click()" class="btn-secondary text-sm">
                    <i class="fas fa-file-import"></i> Import Config
                </button>
                <form action="admin.php" method="POST" enctype="multipart/form-data" class="hidden">
                    <input type="file" id="import-input" name="config_file" onchange="this.form.submit()">
                    <input type="hidden" name="import_config" value="1">
                </form>
                <a href="admin.php?logout=1" class="px-6 py-3 bg-red-50 text-red-600 rounded-xl font-bold hover:bg-red-100 transition-all flex items-center gap-2 text-sm">
                    <i class="fas fa-sign-out-alt"></i> ออกจากระบบ
                </a>
            </div>
        </div>
    </header>

    <main class="container mx-auto px-4 py-10 max-w-5xl">
        <?php if (isset($success)): ?>
            <div class="bg-green-50 text-green-600 p-6 rounded-[2rem] mb-8 font-bold flex items-center gap-3 border border-green-100">
                <i class="fas fa-check-circle text-xl"></i> <?php echo $success; ?>
            </div>
        <?php endif; ?>

        <form action="admin.php" method="POST">
            <!-- Theme Color -->
            <div class="card">
                <h2 class="section-title">
                    <i class="fas fa-palette text-indigo-600"></i> ธีมเว็บไซต์
                </h2>
                <p class="text-sm text-gray-400 mb-6">เลือกสีหลักของเว็บไซต์ จะมีผลทันทีหลังบันทึก</p>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <?php 
                    $colors = [
                        'ส้ม (ค่าเริ่มต้น)' => '#f97316',
                        'น้ำเงิน' => '#2563eb',
                        'เขียว' => '#10b981',
                        'ม่วง' => '#8b5cf6',
                        'แดง' => '#ef4444',
                        'เขียวปีกแมลง' => '#0d9488',
                        'ชมพู' => '#db2777',
                        'คราม' => '#4f46e5'
                    ];
                    foreach ($colors as $name => $code):
                    ?>
                    <label class="relative flex items-center gap-3 p-4 rounded-2xl border-2 cursor-pointer transition-all <?php echo $config['themeColor'] === $code ? 'border-indigo-600 bg-indigo-50' : 'border-gray-100 hover:border-gray-200'; ?>">
                        <input type="radio" name="themeColor" value="<?php echo $code; ?>" class="hidden" <?php echo $config['themeColor'] === $code ? 'checked' : ''; ?>>
                        <div class="h-6 w-6 rounded-full shadow-sm" style="background-color: <?php echo $code; ?>;"></div>
                        <span class="text-sm font-bold text-gray-700"><?php echo $name; ?></span>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Site Info -->
            <div class="card">
                <h2 class="section-title">
                    <i class="fas fa-globe text-indigo-600"></i> ข้อมูลเว็บไซต์
                </h2>
                <div class="grid md:grid-cols-2 gap-6">
                    <div>
                        <label class="input-label">ชื่อเว็บไซต์</label>
                        <input type="text" name="siteName" value="<?php echo htmlspecialchars($config['siteName']); ?>" class="input-field" placeholder="ThaiDeals">
                    </div>
                    <div>
                        <label class="input-label">URL ไอคอนเว็บ (Favicon)</label>
                        <input type="text" name="favicon" value="<?php echo htmlspecialchars($config['favicon']); ?>" class="input-field" placeholder="/favicon.ico">
                    </div>
                </div>
            </div>

            <!-- URL Cloaking -->
            <div class="card">
                <h2 class="section-title">
                    <i class="fas fa-link text-indigo-600"></i> URL Cloaking
                </h2>
                <div class="space-y-6">
                    <div>
                        <label class="input-label">URL Cloaking Base URL</label>
                        <input type="text" name="cloakingBaseUrl" value="<?php echo htmlspecialchars($config['cloakingBaseUrl']); ?>" class="input-field" placeholder="https://goeco.mobi/?token=...">
                        <p class="text-[10px] text-gray-400 mt-2">ระบบจะสร้างลิงก์เป็น: base_url&url=encoded_product_url&source=api_product</p>
                    </div>
                    <div>
                        <label class="input-label">URL Cloaking Token</label>
                        <input type="text" name="cloakingToken" value="<?php echo htmlspecialchars($config['cloakingToken']); ?>" class="input-field" placeholder="QlpXZyCqMylKUjZiYchwB">
                        <p class="text-[10px] text-gray-400 mt-2">URL ที่แสดงผล: https://goeco.mobi/?token=YOUR_TOKEN&url=...&source=api_product</p>
                    </div>
                    <div class="bg-gray-50 p-4 rounded-2xl border border-gray-100">
                        <p class="text-[10px] font-bold text-gray-400 mb-1 uppercase tracking-wider">ตัวอย่าง:</p>
                        <code class="text-[10px] text-indigo-600 break-all">https://goeco.mobi/?token=<?php echo htmlspecialchars($config['cloakingToken']); ?>&url=https%3A%2F%2Fshopee.co.th%2Fproduct&source=api_product</code>
                    </div>
                </div>
            </div>

            <!-- Keywords -->
            <div class="card">
                <h2 class="section-title">
                    <i class="fas fa-wand-magic-sparkles text-indigo-600"></i> คำค้นหาแนะนำ
                </h2>
                <label class="input-label">คำค้นหา (แยกด้วยเครื่องหมายคอมม่า ,)</label>
                <textarea name="keywords_str" class="input-field h-32 py-6" placeholder="โปรโมชั่น, ลดราคา, สินค้ายอดนิยม..."><?php echo implode(', ', $config['keywords']); ?></textarea>
                <div class="mt-4 flex flex-wrap gap-2">
                    <?php foreach ($config['keywords'] as $kw): ?>
                        <span class="bg-indigo-50 text-indigo-600 px-3 py-1 rounded-full text-xs font-bold border border-indigo-100"><?php echo htmlspecialchars($kw); ?></span>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Features -->
            <div class="card">
                <h2 class="section-title">
                    <i class="fas fa-fire text-indigo-600"></i> ฟีเจอร์เสริม
                </h2>
                <div class="space-y-6">
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-2xl">
                        <div>
                            <p class="font-bold text-gray-900">Flash Sale Countdown</p>
                            <p class="text-xs text-gray-400">แสดงเวลานับถอยหลังสำหรับสินค้าลดราคา</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="flashSaleEnabled" class="sr-only peer" <?php echo $config['flashSaleEnabled'] ? 'checked' : ''; ?>>
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                        </label>
                    </div>
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-2xl">
                        <div>
                            <p class="font-bold text-gray-900">AI Reviews</p>
                            <p class="text-xs text-gray-400">แสดงรีวิวที่สร้างโดย AI เพื่อความน่าเชื่อถือ</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="aiReviewsEnabled" class="sr-only peer" <?php echo $config['aiReviewsEnabled'] ? 'checked' : ''; ?>>
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Prefix Words -->
            <div class="card">
                <h2 class="section-title">
                    <i class="fas fa-font text-indigo-600"></i> คำนำหน้าชื่อสินค้า (Prefix Words)
                </h2>
                <div class="flex items-center justify-between mb-6 p-4 bg-gray-50 rounded-2xl">
                    <div>
                        <p class="font-bold text-gray-900">เปิดใช้งาน Prefix Words</p>
                        <p class="text-xs text-gray-400">เพิ่มคำนำหน้าชื่อสินค้าเพื่อดึงดูดความสนใจ</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="prefixWordsEnabled" class="sr-only peer" <?php echo $config['prefixWordsEnabled'] ? 'checked' : ''; ?>>
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                    </label>
                </div>
                <label class="input-label">รายการคำนำหน้า (แยกด้วยเครื่องหมายคอมม่า ,)</label>
                <textarea name="prefixWords_str" class="input-field h-32 py-6" placeholder="ถูกที่สุด, ลดราคา, ส่วนลดพิเศษ..."><?php echo implode(', ', $config['prefixWords']); ?></textarea>
                <div class="mt-4 flex flex-wrap gap-2">
                    <?php foreach ($config['prefixWords'] as $pw): ?>
                        <span class="bg-indigo-50 text-indigo-600 px-3 py-1 rounded-full text-xs font-bold border border-indigo-100"><?php echo htmlspecialchars($pw); ?></span>
                    <?php endforeach; ?>
                </div>
                <p class="text-[10px] text-gray-400 mt-4 italic">ตัวอย่าง: "<?php echo $config['prefixWords'][0] ?? 'ถูกที่สุด'; ?> ชื่อสินค้า"</p>
            </div>

            <div class="flex justify-end sticky bottom-8 z-40">
                <button type="submit" name="save_config" class="btn-primary shadow-2xl shadow-indigo-200">
                    <i class="fas fa-save"></i> บันทึกการตั้งค่าทั้งหมด
                </button>
            </div>
        </form>

        <!-- Data Sources & Categories -->
        <div class="card mt-12">
            <h2 class="section-title">
                <i class="fas fa-database text-indigo-600"></i> แหล่งข้อมูลสินค้า
            </h2>
            
            <!-- Main CSV -->
            <div class="bg-gray-50 p-6 rounded-[2rem] border border-gray-100 mb-8">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <p class="font-bold text-gray-900">CSV ทั่วไป (ใช้เมื่อไม่มี CSV ตามหมวดหมู่)</p>
                        <p class="text-xs text-gray-400 flex items-center gap-2">
                            <i class="fas fa-file-csv"></i> main.csv
                        </p>
                    </div>
                    <form action="admin.php" method="POST" enctype="multipart/form-data" class="flex items-center gap-3">
                        <input type="file" name="csv_file" id="main-csv" class="hidden" onchange="this.form.submit()">
                        <input type="hidden" name="target_category" value="main">
                        <input type="hidden" name="upload_csv" value="1">
                        <button type="button" onclick="document.getElementById('main-csv').click()" class="btn-secondary text-sm">
                            <i class="fas fa-upload"></i> อัปโหลดไฟล์ CSV
                        </button>
                    </form>
                </div>
            </div>

            <!-- Categories Management -->
            <h3 class="text-sm font-bold text-gray-500 mb-4 uppercase tracking-wider">หมวดหมู่สินค้า</h3>
            <form action="admin.php" method="POST" class="flex gap-3 mb-6">
                <input type="text" name="new_category" class="input-field" placeholder="เพิ่มหมวดหมู่ใหม่...">
                <button type="submit" name="add_category" class="btn-primary px-6">
                    <i class="fas fa-plus"></i>
                </button>
            </form>

            <div class="space-y-4">
                <?php foreach ($config['categories'] as $cat): ?>
                <div class="flex items-center justify-between p-4 bg-white border border-gray-100 rounded-2xl hover:shadow-md transition-all">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-folder text-gray-300"></i>
                        <span class="font-bold text-gray-700"><?php echo htmlspecialchars($cat); ?></span>
                        <span class="text-[10px] text-gray-400 italic"><?php echo htmlspecialchars($cat); ?>.csv</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <form action="admin.php" method="POST" enctype="multipart/form-data" class="flex items-center">
                            <input type="file" name="csv_file" id="csv-<?php echo md5($cat); ?>" class="hidden" onchange="this.form.submit()">
                            <input type="hidden" name="target_category" value="<?php echo htmlspecialchars($cat); ?>">
                            <input type="hidden" name="upload_csv" value="1">
                            <button type="button" onclick="document.getElementById('csv-<?php echo md5($cat); ?>').click()" class="btn-secondary text-xs py-2">
                                <i class="fas fa-upload"></i> แนบ CSV
                            </button>
                        </form>
                        <a href="admin.php?del_cat=<?php echo urlencode($cat); ?>" onclick="return confirm('ยืนยันการลบหมวดหมู่?')" class="h-8 w-8 flex items-center justify-center text-red-300 hover:text-red-500 transition-colors">
                            <i class="fas fa-times"></i>
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </main>
</body>
</html>
