<?php
require_once 'includes/functions.php';

// Handle Login
if (isset($_POST['login'])) {
    if (login($_POST['username'], $_POST['password'])) {
        header('Location: admin.php');
        exit;
    } else {
        $error = "ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง";
    }
}

// Handle Logout
if (isset($_GET['logout'])) {
    logout();
    header('Location: admin.php');
    exit;
}

// Check Login
if (!is_logged_in()) {
    ?>
    <!DOCTYPE html>
    <html lang="th">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>เข้าสู่ระบบ - Admin Panel</title>
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
            <form method="POST" class="space-y-6">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Username</label>
                    <input type="text" name="username" class="w-full px-6 py-4 rounded-2xl bg-gray-50 border-0 focus:ring-4 focus:ring-indigo-100 outline-none transition-all" required>
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Password</label>
                    <input type="password" name="password" class="w-full px-6 py-4 rounded-2xl bg-gray-50 border-0 focus:ring-4 focus:ring-indigo-100 outline-none transition-all" required>
                </div>
                <button type="submit" name="login" class="w-full py-4 bg-indigo-600 text-white rounded-2xl font-black hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-200">เข้าสู่ระบบ</button>
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
    $config['siteName'] = $_POST['siteName'];
    $config['siteFavicon'] = $_POST['siteFavicon'];
    $config['themeColor'] = $_POST['themeColor'];
    $config['themeName'] = $_POST['themeName'];
    $config['cloakingBaseUrl'] = $_POST['cloakingBaseUrl'];
    $config['cloakingToken'] = $_POST['cloakingToken'];
    $config['flashSaleEnabled'] = isset($_POST['flashSaleEnabled']);
    $config['aiReviewsEnabled'] = isset($_POST['aiReviewsEnabled']);
    $config['prefixWordsEnabled'] = isset($_POST['prefixWordsEnabled']);
    
    if (!empty($_POST['keywords'])) {
        $config['keywords'] = array_map('trim', explode(',', $_POST['keywords']));
    }
    if (!empty($_POST['prefixWords'])) {
        $config['prefixWords'] = array_map('trim', explode(',', $_POST['prefixWords']));
    }

    save_config($config);
    $success = "บันทึกการตั้งค่าเรียบร้อยแล้ว";
}

// Handle Add Category
if (isset($_POST['add_category'])) {
    $new_cat = [
        'id' => uniqid(),
        'name' => $_POST['cat_name'],
        'csvFile' => ''
    ];
    $config['categories'][] = $new_cat;
    save_config($config);
}

// Handle Delete Category
if (isset($_GET['delete_cat'])) {
    $config['categories'] = array_filter($config['categories'], fn($c) => $c['id'] !== $_GET['delete_cat']);
    save_config($config);
    header('Location: admin.php');
    exit;
}

// Handle CSV Upload
if (isset($_POST['upload_csv'])) {
    $target_dir = DATA_DIR;
    $file_name = basename($_FILES["csv_file"]["name"]);
    $target_file = $target_dir . $file_name;
    
    if (move_uploaded_file($_FILES["csv_file"]["tmp_name"], $target_file)) {
        if ($_POST['cat_id'] === 'main') {
            rename($target_file, DATA_DIR . 'main.csv');
        } else {
            foreach ($config['categories'] as &$cat) {
                if ($cat['id'] === $_POST['cat_id']) {
                    $cat['csvFile'] = $file_name;
                    break;
                }
            }
            save_config($config);
        }
        $success = "อัปโหลดไฟล์ CSV เรียบร้อยแล้ว";
    }
}

$themes = [
    ['name' => 'orange', 'color' => '#ff6b00', 'label' => 'ส้ม (ค่าเริ่มต้น)'],
    ['name' => 'blue', 'color' => '#2563eb', 'label' => 'น้ำเงิน'],
    ['name' => 'green', 'color' => '#10b981', 'label' => 'เขียว'],
    ['name' => 'purple', 'color' => '#8b5cf6', 'label' => 'ม่วง'],
    ['name' => 'red', 'color' => '#ef4444', 'label' => 'แดง'],
    ['name' => 'teal', 'color' => '#0d9488', 'label' => 'เขียวปีกแมลง'],
    ['name' => 'pink', 'color' => '#db2777', 'label' => 'ชมพู'],
    ['name' => 'indigo', 'color' => '#4f46e5', 'label' => 'คราม']
];
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - <?php echo $config['siteName']; ?></title>
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

        <form method="POST" id="configForm">
            <!-- Theme Selection -->
            <div class="card">
                <h2 class="section-title">
                    <i class="fas fa-palette text-indigo-600"></i> ธีมเว็บไซต์
                </h2>
                <p class="text-sm text-gray-400 mb-6">เลือกสีหลักของเว็บไซต์ จะมีผลทันทีหลังบันทึก</p>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <?php foreach ($themes as $theme): ?>
                        <label class="relative flex items-center gap-3 p-4 rounded-2xl border-2 cursor-pointer transition-all <?php echo $config['themeName'] === $theme['name'] ? 'border-indigo-600 bg-indigo-50' : 'border-gray-100 hover:border-gray-200'; ?>">
                            <input type="radio" name="themeName" value="<?php echo $theme['name']; ?>" class="hidden" <?php echo $config['themeName'] === $theme['name'] ? 'checked' : ''; ?> onchange="document.getElementById('themeColorInput').value='<?php echo $theme['color']; ?>'">
                            <div class="h-6 w-6 rounded-full shadow-sm" style="background-color: <?php echo $theme['color']; ?>;"></div>
                            <span class="text-sm font-bold text-gray-700"><?php echo $theme['label']; ?></span>
                        </label>
                    <?php endforeach; ?>
                    <input type="hidden" name="themeColor" id="themeColorInput" value="<?php echo $config['themeColor']; ?>">
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
                        <input type="text" name="siteName" value="<?php echo htmlspecialchars($config['siteName']); ?>" class="input-field">
                    </div>
                    <div>
                        <label class="input-label">URL ไอคอนเว็บ (Favicon)</label>
                        <input type="text" name="siteFavicon" value="<?php echo htmlspecialchars($config['siteFavicon']); ?>" class="input-field">
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
                        <input type="text" name="cloakingBaseUrl" value="<?php echo htmlspecialchars($config['cloakingBaseUrl']); ?>" class="input-field">
                        <p class="text-[10px] text-gray-400 mt-2">ระบบจะสร้างลิงก์เป็น: base_url&url=encoded_product_url&source=api_product</p>
                    </div>
                    <div>
                        <label class="input-label">URL Cloaking Token</label>
                        <input type="text" name="cloakingToken" value="<?php echo htmlspecialchars($config['cloakingToken']); ?>" class="input-field">
                    </div>
                </div>
            </div>

            <!-- Categories -->
            <div class="card">
                <h2 class="section-title">
                    <i class="fas fa-tags text-indigo-600"></i> หมวดหมู่สินค้า
                </h2>
                <div class="space-y-4">
                    <div class="flex gap-3">
                        <input type="text" id="new_cat_name" placeholder="เพิ่มหมวดหมู่..." class="input-field">
                        <button type="button" onclick="addCategory()" class="btn-primary px-6"><i class="fas fa-plus"></i></button>
                    </div>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-2xl border border-gray-100">
                            <div class="flex items-center gap-3">
                                <i class="fas fa-file-csv text-gray-400"></i>
                                <span class="font-bold text-gray-700">สินค้าแนะนำ (หน้าแรก)</span>
                                <span class="text-xs text-gray-400">main.csv</span>
                            </div>
                            <button type="button" onclick="openUploadModal('main')" class="text-sm font-bold text-indigo-600 hover:underline flex items-center gap-2">
                                <i class="fas fa-upload"></i> อัปโหลด CSV
                            </button>
                        </div>
                        <?php foreach ($config['categories'] as $cat): ?>
                            <div class="flex items-center justify-between p-4 bg-white rounded-2xl border border-gray-100">
                                <div class="flex items-center gap-3">
                                    <i class="fas fa-folder text-gray-400"></i>
                                    <span class="font-bold text-gray-700"><?php echo htmlspecialchars($cat['name']); ?></span>
                                    <span class="text-xs text-gray-400"><?php echo $cat['csvFile'] ?: 'ยังไม่มีไฟล์'; ?></span>
                                </div>
                                <div class="flex items-center gap-6">
                                    <button type="button" onclick="openUploadModal('<?php echo $cat['id']; ?>')" class="text-sm font-bold text-indigo-600 hover:underline flex items-center gap-2">
                                        <i class="fas fa-upload"></i> อัปโหลด CSV
                                    </button>
                                    <a href="admin.php?delete_cat=<?php echo $cat['id']; ?>" class="text-red-400 hover:text-red-600 transition-colors"><i class="fas fa-times"></i></a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Keywords & Prefix -->
            <div class="card">
                <h2 class="section-title">
                    <i class="fas fa-magic text-indigo-600"></i> ฟีเจอร์เสริม
                </h2>
                <div class="space-y-8">
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
                    <div>
                        <label class="input-label">คำค้นหาแนะนำ (คั่นด้วยคอมม่า ,)</label>
                        <textarea name="keywords" class="input-field h-32 py-6"><?php echo implode(', ', $config['keywords']); ?></textarea>
                    </div>
                    <div>
                        <label class="input-label">คำนำหน้าชื่อสินค้า (Prefix Words)</label>
                        <textarea name="prefixWords" class="input-field h-32 py-6"><?php echo implode(', ', $config['prefixWords']); ?></textarea>
                    </div>
                </div>
            </div>

            <div class="fixed bottom-10 left-0 right-0 flex justify-center pointer-events-none z-50">
                <button type="submit" name="save_config" class="pointer-events-auto btn-primary py-5 px-16 rounded-full shadow-2xl transition-transform hover:scale-105">
                    <i class="fas fa-save text-xl"></i> บันทึกการตั้งค่าทั้งหมด
                </button>
            </div>
        </form>
    </main>

    <!-- Upload Modal -->
    <div id="uploadModal" class="fixed inset-0 bg-black/50 hidden z-[100] flex items-center justify-center p-4 backdrop-blur-sm">
        <div class="bg-white rounded-[2.5rem] p-10 w-full max-w-md shadow-2xl">
            <h3 class="text-2xl font-black mb-6">อัปโหลดไฟล์ CSV</h3>
            <form method="POST" enctype="multipart/form-data" class="space-y-6">
                <input type="hidden" name="cat_id" id="modal_cat_id">
                <div class="border-2 border-dashed border-gray-200 rounded-2xl p-8 text-center hover:border-indigo-300 transition-colors">
                    <input type="file" name="csv_file" accept=".csv" class="hidden" id="csv_input" required>
                    <label for="csv_input" class="cursor-pointer">
                        <i class="fas fa-cloud-upload-alt text-4xl text-indigo-400 mb-4"></i>
                        <p class="text-sm font-bold text-gray-500">คลิกเพื่อเลือกไฟล์ .csv</p>
                    </label>
                </div>
                <div class="flex gap-3">
                    <button type="button" onclick="closeUploadModal()" class="flex-1 py-4 bg-gray-100 text-gray-600 rounded-2xl font-bold">ยกเลิก</button>
                    <button type="submit" name="upload_csv" class="flex-1 py-4 bg-indigo-600 text-white rounded-2xl font-black shadow-lg shadow-indigo-100">อัปโหลด</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openUploadModal(id) {
            document.getElementById('modal_cat_id').value = id;
            document.getElementById('uploadModal').classList.remove('hidden');
        }
        function closeUploadModal() {
            document.getElementById('uploadModal').classList.add('hidden');
        }
        function addCategory() {
            const name = document.getElementById('new_cat_name').value;
            if (!name) return;
            const form = document.createElement('form');
            form.method = 'POST';
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'cat_name';
            input.value = name;
            const btn = document.createElement('input');
            btn.type = 'hidden';
            btn.name = 'add_category';
            form.appendChild(input);
            form.appendChild(btn);
            document.body.appendChild(form);
            form.submit();
        }
    </script>
</body>
</html>
