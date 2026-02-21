<?php
require_once 'includes/functions.php';

// Handle Login
if (isset($_POST['login'])) {
    if (login($_POST['username'] ?? '', $_POST['password'] ?? '')) {
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
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
        <style>
            * { font-family: 'Prompt', sans-serif !important; }
        </style>
    </head>
    <body class="bg-gray-50 flex items-center justify-center min-h-screen p-4">
        <div class="bg-white p-8 rounded-3xl shadow-xl w-full max-w-sm border border-gray-100">
            <div class="text-center mb-8">
                <div class="h-16 w-16 text-white rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg" style="background-color: #ff6b00;">
                    <i class="fas fa-lock text-2xl"></i>
                </div>
                <h1 class="text-2xl font-black text-gray-900">เข้าสู่ระบบ Admin</h1>
                <p class="text-gray-400 text-sm mt-1">กรุณากรอกชื่อผู้ใช้และรหัสผ่าน</p>
            </div>
            <?php if (isset($error)): ?>
                <div class="bg-red-50 text-red-600 p-3 rounded-xl mb-5 text-sm font-semibold flex items-center gap-2">
                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            <form method="POST" class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-600 mb-1.5">ชื่อผู้ใช้</label>
                    <input type="text" name="username" placeholder="ชื่อผู้ใช้"
                           class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 focus:ring-2 focus:border-orange-400 outline-none transition-all text-sm" required>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-600 mb-1.5">รหัสผ่าน</label>
                    <input type="password" name="password" placeholder="รหัสผ่าน"
                           class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 focus:ring-2 focus:border-orange-400 outline-none transition-all text-sm" required>
                </div>
                <button type="submit" name="login" 
                        class="w-full py-3 text-white rounded-xl font-bold hover:opacity-90 transition-all shadow-lg flex items-center justify-center gap-2 mt-2"
                        style="background-color: #ff6b00;">
                    <i class="fas fa-sign-in-alt"></i> เข้าสู่ระบบ
                </button>
            </form>
        </div>
    </body>
    </html>
    <?php
    exit;
}

$config = get_config();
$theme_color = $config['themeColor'] ?? '#ff6b00';

// Handle Import Config
if (isset($_POST['import_config']) && isset($_FILES['config_file']) && $_FILES['config_file']['error'] === UPLOAD_ERR_OK) {
    $file_content = file_get_contents($_FILES['config_file']['tmp_name']);
    $imported = json_decode($file_content, true);
    if (is_array($imported)) {
        $config = array_merge($config, $imported);
        save_config($config);
        $success = "นำเข้าการตั้งค่าเรียบร้อยแล้ว";
        $config = get_config();
        $theme_color = $config['themeColor'] ?? '#ff6b00';
    } else {
        $error = "ไฟล์ JSON ไม่ถูกต้อง";
    }
}

// Handle Export Config
if (isset($_GET['export'])) {
    header('Content-Type: application/json');
    header('Content-Disposition: attachment; filename="config_' . date('Ymd_His') . '.json"');
    echo json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

// Handle Save Config
if (isset($_POST['save_config'])) {
    $config['siteName']          = trim($_POST['siteName'] ?? $config['siteName']);
    $config['siteFavicon']       = trim($_POST['siteFavicon'] ?? $config['siteFavicon']);
    $config['themeColor']        = trim($_POST['themeColor'] ?? $config['themeColor']);
    $config['themeName']         = trim($_POST['themeName'] ?? $config['themeName']);
    $config['cloakingBaseUrl']   = trim($_POST['cloakingBaseUrl'] ?? $config['cloakingBaseUrl']);
    $config['cloakingToken']     = trim($_POST['cloakingToken'] ?? $config['cloakingToken']);
    $config['flashSaleEnabled']  = isset($_POST['flashSaleEnabled']);
    $config['aiReviewsEnabled']  = isset($_POST['aiReviewsEnabled']);
    $config['prefixWordsEnabled']= isset($_POST['prefixWordsEnabled']);
    
    if (isset($_POST['keywords'])) {
        $kws = array_filter(array_map('trim', explode(',', $_POST['keywords'])));
        $config['keywords'] = array_values($kws);
    }
    if (isset($_POST['prefixWords'])) {
        $pws = array_filter(array_map('trim', explode(',', $_POST['prefixWords'])));
        $config['prefixWords'] = array_values($pws);
    }

    save_config($config);
    $success = "บันทึกการตั้งค่าเรียบร้อยแล้ว";
    // Reload config after save
    $config = get_config();
    $theme_color = $config['themeColor'] ?? '#ff6b00';
}

// Handle Add Category
if (isset($_POST['add_category']) && !empty($_POST['cat_name'])) {
    $new_cat = [
        'id'      => uniqid('cat_'),
        'name'    => trim($_POST['cat_name']),
        'csvFile' => ''
    ];
    $config['categories'][] = $new_cat;
    save_config($config);
    header('Location: admin.php#categories');
    exit;
}

// Handle Delete Category
if (isset($_GET['delete_cat'])) {
    $del_id = $_GET['delete_cat'];
    $config['categories'] = array_values(array_filter($config['categories'], function($c) use ($del_id) {
        $c_id = is_array($c) ? ($c['id'] ?? '') : '';
        return $c_id !== $del_id;
    }));
    save_config($config);
    header('Location: admin.php#categories');
    exit;
}

// Handle CSV Upload
if (isset($_POST['upload_csv']) && isset($_FILES["csv_file"]) && $_FILES["csv_file"]["error"] === UPLOAD_ERR_OK) {
    $file_name   = basename($_FILES["csv_file"]["name"]);
    // FIX: Added "/" separator
    $target_file = DATA_DIR . '/' . $file_name;
    
    if (move_uploaded_file($_FILES["csv_file"]["tmp_name"], $target_file)) {
        $cat_id = $_POST['cat_id'] ?? '';
        if ($cat_id === 'main') {
            // FIX: Added "/" separator
            rename($target_file, DATA_DIR . '/main.csv');
        } else {
            foreach ($config['categories'] as &$cat) {
                if (is_array($cat) && ($cat['id'] ?? '') === $cat_id) {
                    $cat['csvFile'] = $file_name;
                    break;
                }
            }
            save_config($config);
        }
        clear_cache();
        $success = "อัปโหลดไฟล์ CSV เรียบร้อยแล้ว";
    } else {
        $error = "อัปโหลดไฟล์ไม่สำเร็จ กรุณาตรวจสอบสิทธิ์การเขียนไฟล์";
    }
}

$themes = [
    ['name' => 'orange', 'color' => '#ff6b00', 'label' => 'ส้ม (ค่าเริ่มต้น)'],
    ['name' => 'blue',   'color' => '#2563eb', 'label' => 'น้ำเงิน'],
    ['name' => 'green',  'color' => '#10b981', 'label' => 'เขียว'],
    ['name' => 'purple', 'color' => '#8b5cf6', 'label' => 'ม่วง'],
    ['name' => 'red',    'color' => '#ef4444', 'label' => 'แดง'],
    ['name' => 'teal',   'color' => '#0d9488', 'label' => 'เขียวน้ำทะเล'],
    ['name' => 'pink',   'color' => '#db2777', 'label' => 'ชมพู'],
    ['name' => 'indigo', 'color' => '#4f46e5', 'label' => 'คราม'],
];
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel — <?php echo htmlspecialchars($config['siteName']); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        * { font-family: 'Prompt', sans-serif !important; }
        :root { --primary: <?php echo $theme_color; ?>; }
        .card { background: white; border-radius: 1.25rem; border: 1px solid #f3f4f6; padding: 1.5rem; margin-bottom: 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.06); }
        .section-title { font-size: 1rem; font-weight: 800; color: #111827; margin-bottom: 1.25rem; display: flex; align-items: center; gap: 0.625rem; }
        .input-label { display: block; font-size: 0.8125rem; font-weight: 600; color: #6b7280; margin-bottom: 0.375rem; }
        .input-field { width: 100%; padding: 0.75rem 1rem; border-radius: 0.75rem; background: #f9fafb; border: 1px solid #e5e7eb; outline: none; transition: all 0.2s; font-size: 0.875rem; }
        .input-field:focus { border-color: var(--primary); box-shadow: 0 0 0 3px color-mix(in srgb, var(--primary) 15%, transparent); }
        .btn-save { background-color: var(--primary); color: white; padding: 0.875rem 2.5rem; border-radius: 999px; font-weight: 700; font-size: 0.9375rem; display: flex; align-items: center; gap: 0.5rem; transition: all 0.2s; box-shadow: 0 4px 15px color-mix(in srgb, var(--primary) 30%, transparent); }
        .btn-save:hover { opacity: 0.9; transform: translateY(-1px); }
        .theme-card { border-radius: 0.875rem; border: 2px solid #f3f4f6; padding: 0.875rem 1rem; cursor: pointer; transition: all 0.2s; display: flex; align-items: center; gap: 0.625rem; }
        .theme-card:hover { border-color: #d1d5db; }
        .theme-card.active { border-color: var(--primary); background-color: color-mix(in srgb, var(--primary) 8%, white); }
        .toggle-switch { position: relative; display: inline-flex; align-items: center; cursor: pointer; }
        .toggle-switch input { opacity: 0; width: 0; height: 0; }
        .toggle-track { width: 44px; height: 24px; background: #d1d5db; border-radius: 999px; transition: 0.3s; }
        .toggle-switch input:checked + .toggle-track { background-color: var(--primary); }
        .toggle-thumb { position: absolute; left: 2px; top: 2px; width: 20px; height: 20px; background: white; border-radius: 50%; transition: 0.3s; box-shadow: 0 1px 3px rgba(0,0,0,0.2); }
        .toggle-switch input:checked ~ .toggle-thumb { transform: translateX(20px); }
        .cat-row { display: flex; align-items: center; justify-content: space-between; padding: 0.75rem 1rem; background: #f9fafb; border-radius: 0.75rem; border: 1px solid #f3f4f6; }
        .upload-btn { font-size: 0.8125rem; font-weight: 600; color: var(--primary); display: flex; align-items: center; gap: 0.375rem; padding: 0.375rem 0.75rem; border-radius: 0.5rem; transition: all 0.2s; }
        .upload-btn:hover { background-color: color-mix(in srgb, var(--primary) 10%, white); }
        @media (max-width: 768px) { .upload-btn { font-size: 0.75rem; padding: 0.25rem 0.5rem; } }
    </style>
</head>
<body class="bg-gray-50 min-h-screen pb-24">

    <!-- Header -->
    <header class="bg-white border-b border-gray-100 sticky top-0 z-50 shadow-sm">
        <div class="container mx-auto px-4 h-16 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="index.php" class="flex items-center gap-2">
                    <div class="h-9 w-9 text-white rounded-xl flex items-center justify-center shadow-md" style="background-color: <?php echo $theme_color; ?>;">
                        <i class="fas fa-shopping-bag text-sm"></i>
                    </div>
                    <span class="text-base font-black text-gray-900"><?php echo htmlspecialchars($config['siteName']); ?></span>
                </a>
                <span class="text-gray-300">|</span>
                <span class="text-sm font-bold text-gray-500">🛠 Admin Panel</span>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" onclick="document.getElementById('importConfigInput').click()" class="text-sm font-semibold text-gray-500 hover:text-gray-700 px-3 py-2 rounded-xl hover:bg-gray-100 transition-all flex items-center gap-1.5">
                    <i class="fas fa-file-import text-xs"></i> Import Config
                </button>
                <input type="file" id="importConfigInput" accept=".json" style="display:none" onchange="submitImportForm(this)">
                <a href="admin.php?export=1" class="text-sm font-semibold text-gray-500 hover:text-gray-700 px-3 py-2 rounded-xl hover:bg-gray-100 transition-all flex items-center gap-1.5">
                    <i class="fas fa-file-export text-xs"></i> Export Config
                </a>
                <a href="admin.php?logout=1" class="text-sm font-semibold text-red-500 hover:text-red-600 px-3 py-2 rounded-xl hover:bg-red-50 transition-all flex items-center gap-1.5">
                    <i class="fas fa-sign-out-alt text-xs"></i> ออกจากระบบ
                </a>
            </div>
            <form id="importForm" method="POST" enctype="multipart/form-data" style="display:none;">
                <input type="file" id="importConfigFile" name="config_file" accept=".json">
                <input type="hidden" name="import_config" value="1">
            </form>
        </div>
    </header>

    <main class="container mx-auto px-4 py-8 max-w-4xl">

        <!-- Page Title -->
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-black text-gray-900 flex items-center gap-3">
                <i class="fas fa-tools" style="color: <?php echo $theme_color; ?>"></i> Admin Panel
            </h1>
            <button form="configForm" type="submit" name="save_config" class="btn-save">
                <i class="fas fa-save"></i> บันทึกการตั้งค่าทั้งหมด
            </button>
        </div>

        <?php if (isset($success)): ?>
            <div class="bg-green-50 text-green-700 p-4 rounded-2xl mb-6 font-semibold flex items-center gap-3 border border-green-100">
                <i class="fas fa-check-circle text-lg"></i> <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>
        <?php if (isset($error)): ?>
            <div class="bg-red-50 text-red-600 p-4 rounded-2xl mb-6 font-semibold flex items-center gap-3 border border-red-100">
                <i class="fas fa-exclamation-circle text-lg"></i> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" id="configForm">

            <!-- Theme Selection -->
            <div class="card">
                <h2 class="section-title">
                    <i class="fas fa-palette" style="color: <?php echo $theme_color; ?>"></i> ธีมสีเว็บไซต์
                </h2>
                <p class="text-sm text-gray-400 mb-4">เลือกสีหลักของเว็บไซต์ จะมีผลทันทีหลังบันทึก</p>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    <?php foreach ($themes as $theme): ?>
                        <label class="theme-card <?php echo ($config['themeName'] === $theme['name']) ? 'active' : ''; ?>" 
                               onclick="selectTheme('<?php echo $theme['name']; ?>', '<?php echo $theme['color']; ?>')">
                            <input type="radio" name="themeName" value="<?php echo $theme['name']; ?>" class="hidden" 
                                   <?php echo ($config['themeName'] === $theme['name']) ? 'checked' : ''; ?>>
                            <div class="h-5 w-5 rounded-full shadow-sm flex-shrink-0" style="background-color: <?php echo $theme['color']; ?>;"></div>
                            <span class="text-sm font-semibold text-gray-700"><?php echo $theme['label']; ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
                <input type="hidden" name="themeColor" id="themeColorInput" value="<?php echo htmlspecialchars($theme_color); ?>">
            </div>

            <!-- Site Info -->
            <div class="card">
                <h2 class="section-title">
                    <i class="fas fa-globe" style="color: <?php echo $theme_color; ?>"></i> ข้อมูลเว็บไซต์
                </h2>
                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label class="input-label">ชื่อเว็บไซต์</label>
                        <input type="text" name="siteName" value="<?php echo htmlspecialchars($config['siteName']); ?>" 
                               class="input-field" placeholder="ตัวอย่าง: ThaiDeals">
                    </div>
                    <div>
                        <label class="input-label">URL ไอคอนเว็บ (Favicon)</label>
                        <input type="text" name="siteFavicon" value="<?php echo htmlspecialchars($config['siteFavicon'] ?? '/favicon.ico'); ?>" 
                               class="input-field" placeholder="ตัวอย่าง: /favicon.ico หรือ https://...">
                    </div>
                </div>
            </div>

            <!-- URL Cloaking -->
            <div class="card">
                <h2 class="section-title">
                    <i class="fas fa-link" style="color: <?php echo $theme_color; ?>"></i> URL Cloaking
                </h2>
                <div class="space-y-4">
                    <div>
                        <label class="input-label">URL Cloaking Base URL</label>
                        <input type="text" name="cloakingBaseUrl" id="cloakingBaseUrl"
                               value="<?php echo htmlspecialchars($config['cloakingBaseUrl']); ?>" 
                               class="input-field" oninput="updateCloakPreview()">
                        <p class="text-xs text-gray-400 mt-1.5">URL ที่สร้าง: base_url&url=encoded_product_url&source=api_product</p>
                    </div>
                    <div>
                        <label class="input-label">URL Cloaking Token</label>
                        <input type="text" name="cloakingToken" id="cloakingToken"
                               value="<?php echo htmlspecialchars($config['cloakingToken']); ?>" 
                               class="input-field" oninput="updateCloakPreview()">
                    </div>
                    <div class="bg-gray-50 rounded-xl p-3 text-xs text-gray-400 font-mono break-all" id="cloakPreview">
                        ตัวอย่าง: <?php echo htmlspecialchars($config['cloakingBaseUrl']); ?>&url=https%3A%2F%2Fshopee.co.th%2Fproduct&source=api_product
                    </div>
                </div>
            </div>

            <!-- Features -->
            <div class="card">
                <h2 class="section-title">
                    <i class="fas fa-fire" style="color: <?php echo $theme_color; ?>"></i> ฟีเจอร์เสริม
                </h2>
                <div class="space-y-4">
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl">
                        <div>
                            <p class="font-semibold text-gray-900 text-sm">Flash Sale Countdown</p>
                            <p class="text-xs text-gray-400">แสดงเวลานับถอยหลังสำหรับสินค้าลดราคา</p>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" name="flashSaleEnabled" <?php echo $config['flashSaleEnabled'] ? 'checked' : ''; ?>>
                            <div class="toggle-track"></div>
                            <div class="toggle-thumb"></div>
                        </label>
                    </div>
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl">
                        <div>
                            <p class="font-semibold text-gray-900 text-sm">AI Reviews</p>
                            <p class="text-xs text-gray-400">แสดงรีวิวที่สร้างโดย AI เพื่อความน่าเชื่อถือ</p>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" name="aiReviewsEnabled" <?php echo $config['aiReviewsEnabled'] ? 'checked' : ''; ?>>
                            <div class="toggle-track"></div>
                            <div class="toggle-thumb"></div>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Keywords -->
            <div class="card">
                <h2 class="section-title">
                    <i class="fas fa-search" style="color: <?php echo $theme_color; ?>"></i> คำค้นหาแนะนำ
                </h2>
                <div>
                    <label class="input-label">คำค้นหา (คั่นด้วยคอมม่า)</label>
                    <textarea name="keywords" class="input-field h-24 resize-none" 
                              placeholder="ตัวอย่าง: iPhone, Samsung, Nike"><?php echo htmlspecialchars(implode(', ', $config['keywords'] ?? [])); ?></textarea>
                </div>
            </div>

            <!-- Prefix Words -->
            <div class="card">
                <h2 class="section-title">
                    <i class="fas fa-tag" style="color: <?php echo $theme_color; ?>"></i> คำนำหน้าชื่อสินค้า (Prefix Words)
                </h2>
                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl mb-4">
                    <div>
                        <p class="font-semibold text-gray-900 text-sm">เปิดใช้งาน Prefix Words</p>
                        <p class="text-xs text-gray-400">เพิ่มคำนำหน้าชื่อสินค้าเพื่อดึงดูดความสนใจ</p>
                    </div>
                    <label class="toggle-switch">
                        <input type="checkbox" name="prefixWordsEnabled" <?php echo $config['prefixWordsEnabled'] ? 'checked' : ''; ?>>
                        <div class="toggle-track"></div>
                        <div class="toggle-thumb"></div>
                    </label>
                </div>
                <div>
                    <label class="input-label">รายการคำนำหน้า (คั่นด้วยคอมม่า)</label>
                    <textarea name="prefixWords" class="input-field h-24 resize-none"
                              placeholder="ตัวอย่าง: ลดราคา, ขายดี, แนะนำ"><?php echo htmlspecialchars(implode(', ', $config['prefixWords'] ?? [])); ?></textarea>
                </div>
            </div>

        </form>

        <!-- Categories (separate form for uploads) -->
        <div class="card" id="categories">
            <h2 class="section-title">
                <i class="fas fa-tags" style="color: <?php echo $theme_color; ?>"></i> หมวดหมู่สินค้า
            </h2>
            
            <!-- Add Category -->
            <form method="POST" class="flex gap-2 mb-4">
                <input type="text" name="cat_name" placeholder="เพิ่มหมวดหมู่..." 
                       class="input-field flex-1" required>
                <button type="submit" name="add_category" 
                        class="px-4 py-2.5 text-white rounded-xl font-bold flex items-center gap-1.5 text-sm flex-shrink-0"
                        style="background-color: <?php echo $theme_color; ?>">
                    <i class="fas fa-plus"></i> เพิ่ม
                </button>
            </form>

            <div class="space-y-2">
                <!-- Main CSV -->
                <div class="cat-row">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-file-csv text-gray-400 text-sm"></i>
                        <div>
                            <p class="font-semibold text-gray-800 text-sm">สินค้าแนะนำ (หน้าแรก)</p>
                            <p class="text-xs text-gray-400">main.csv <?php echo file_exists(DATA_DIR . '/main.csv') ? '✓ มีไฟล์แล้ว' : '— ยังไม่มีไฟล์'; ?></p>
                        </div>
                    </div>
                    <button type="button" onclick="openUploadModal('main')" class="upload-btn" style="color: <?php echo $theme_color; ?>">
                        <i class="fas fa-upload text-xs"></i> อัปโหลด CSV
                    </button>
                </div>

                <?php foreach ($config['categories'] as $cat): ?>
                    <?php 
                        $cat_id   = is_array($cat) ? ($cat['id'] ?? uniqid('cat_')) : $cat;
                        $cat_name = is_array($cat) ? ($cat['name'] ?? $cat) : $cat;
                        $cat_csv  = is_array($cat) ? ($cat['csvFile'] ?? '') : '';
                        $has_file = !empty($cat_csv) && file_exists(DATA_DIR . '/' . $cat_csv);
                    ?>
                    <div class="cat-row">
                        <div class="flex items-center gap-3">
                            <i class="fas fa-folder text-gray-400 text-sm"></i>
                            <div>
                                <p class="font-semibold text-gray-800 text-sm"><?php echo htmlspecialchars($cat_name); ?></p>
                                <p class="text-xs text-gray-400"><?php echo $has_file ? htmlspecialchars($cat_csv) . ' ✓' : 'ยังไม่มีไฟล์ CSV'; ?></p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <button type="button" onclick="openUploadModal('<?php echo htmlspecialchars($cat_id); ?>')" 
                                    class="upload-btn" style="color: <?php echo $theme_color; ?>">
                                <i class="fas fa-upload text-xs"></i> แนบ CSV
                            </button>
                            <a href="admin.php?delete_cat=<?php echo urlencode($cat_id); ?>" 
                               onclick="return confirm('ลบหมวดหมู่ <?php echo htmlspecialchars($cat_name); ?> ใช่หรือไม่?')"
                               class="text-red-400 hover:text-red-600 transition-colors p-1">
                                <i class="fas fa-times text-sm"></i>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Fixed Save Button -->
        <div class="fixed bottom-8 left-0 right-0 flex justify-center pointer-events-none z-50">
            <button form="configForm" type="submit" name="save_config" 
                    class="pointer-events-auto btn-save py-4 px-12 rounded-full shadow-2xl hover:scale-105 transition-transform">
                <i class="fas fa-save text-lg"></i> บันทึกการตั้งค่าทั้งหมด
            </button>
        </div>
    </main>

    <!-- Upload Modal -->
    <div id="uploadModal" class="fixed inset-0 bg-black/50 hidden z-[100] flex items-center justify-center p-4 backdrop-blur-sm">
        <div class="bg-white rounded-3xl p-8 w-full max-w-md shadow-2xl">
            <h3 class="text-xl font-black mb-2">อัปโหลดไฟล์ CSV</h3>
            <p class="text-sm text-gray-400 mb-6">รองรับไฟล์ .csv เท่านั้น</p>
            <form method="POST" enctype="multipart/form-data" class="space-y-5">
                <input type="hidden" name="cat_id" id="modal_cat_id">
                <div class="border-2 border-dashed border-gray-200 rounded-2xl p-8 text-center hover:border-gray-300 transition-colors cursor-pointer" 
                     onclick="document.getElementById('csv_input').click()">
                    <input type="file" name="csv_file" accept=".csv" class="hidden" id="csv_input" 
                           onchange="document.getElementById('csv_filename').textContent = this.files[0]?.name || 'คลิกเพื่อเลือกไฟล์'" required>
                    <i class="fas fa-cloud-upload-alt text-4xl text-gray-300 mb-3"></i>
                    <p class="text-sm font-semibold text-gray-400" id="csv_filename">คลิกเพื่อเลือกไฟล์ .csv</p>
                </div>
                <div class="flex gap-3">
                    <button type="button" onclick="closeUploadModal()" 
                            class="flex-1 py-3 bg-gray-100 text-gray-600 rounded-xl font-semibold hover:bg-gray-200 transition-all">
                        ยกเลิก
                    </button>
                    <button type="submit" name="upload_csv" 
                            class="flex-1 py-3 text-white rounded-xl font-bold shadow-lg transition-all hover:opacity-90"
                            style="background-color: <?php echo $theme_color; ?>">
                        <i class="fas fa-upload mr-1"></i> อัปโหลด
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Import config function
        function submitImportForm(input) {
            if (input.files && input.files[0]) {
                const formData = new FormData();
                formData.append('config_file', input.files[0]);
                formData.append('import_config', '1');
                const form = document.getElementById('importForm');
                form.appendChild(input);
                form.submit();
            }
        }

        // Theme selection
        function selectTheme(name, color) {
            document.getElementById('themeColorInput').value = color;
            document.querySelectorAll('.theme-card').forEach(el => el.classList.remove('active'));
            event.currentTarget.classList.add('active');
        }

        // Upload modal
        function openUploadModal(id) {
            document.getElementById('modal_cat_id').value = id;
            document.getElementById('csv_filename').textContent = 'คลิกเพื่อเลือกไฟล์ .csv';
            document.getElementById('uploadModal').classList.remove('hidden');
        }
        function closeUploadModal() {
            document.getElementById('uploadModal').classList.add('hidden');
        }
        document.getElementById('uploadModal').addEventListener('click', function(e) {
            if (e.target === this) closeUploadModal();
        });

        // Cloak preview
        function updateCloakPreview() {
            const base = document.getElementById('cloakingBaseUrl').value;
            const preview = document.getElementById('cloakPreview');
            if (preview) {
                preview.textContent = 'ตัวอย่าง: ' + base + '&url=https%3A%2F%2Fshopee.co.th%2Fproduct&source=api_product';
            }
        }
    </script>
</body>
</html>
