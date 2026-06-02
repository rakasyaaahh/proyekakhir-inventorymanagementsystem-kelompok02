<?php
session_start();

define('BASE_URL_SETUP', '/fashion-ims');
define('DB_CONFIG_PATH', __DIR__ . '/config/database.php');

$error = null;
$success = null;
$step = 'init';

function get_db_config() {
    $config = [
        'host' => 'localhost',
        'name' => 'inventaris_busana',
        'user' => 'root',
        'pass' => '',
        'charset' => 'utf8mb4'
    ];
    
    if (file_exists(DB_CONFIG_PATH)) {
        $content = file_get_contents(DB_CONFIG_PATH);
        if (preg_match("/define\(\s*'DB_HOST'\s*,\s*'(.*?)'\s*\)/", $content, $matches)) {
            $config['host'] = $matches[1];
        }
        if (preg_match("/define\(\s*'DB_NAME'\s*,\s*'(.*?)'\s*\)/", $content, $matches)) {
            $config['name'] = $matches[1];
        }
        if (preg_match("/define\(\s*'DB_USER'\s*,\s*'(.*?)'\s*\)/", $content, $matches)) {
            $config['user'] = $matches[1];
        }
        if (preg_match("/define\(\s*'DB_PASS'\s*,\s*'(.*?)'\s*\)/", $content, $matches)) {
            $config['pass'] = $matches[1];
        }
        if (preg_match("/define\(\s*'DB_CHARSET'\s*,\s*'(.*?)'\s*\)/", $content, $matches)) {
            $config['charset'] = $matches[1];
        }
    }
    return $config;
}

$db_cfg = get_db_config();

$connection_status = 'disconnected';
$connection_message = '';
$database_exists = false;
$tables_installed = false;
$installed_tables_count = 0;
$tables_list = [];

try {
    $dsn_server = "mysql:host=" . $db_cfg['host'] . ";charset=" . $db_cfg['charset'];
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => true,
    ];
    $pdo_test = new PDO($dsn_server, $db_cfg['user'], $db_cfg['pass'], $options);
    $connection_status = 'connected_host';
    
    $stmt = $pdo_test->query("SHOW DATABASES LIKE '" . $db_cfg['name'] . "'");
    if ($stmt->fetch()) {
        $database_exists = true;
        
        $dsn_db = "mysql:host=" . $db_cfg['host'] . ";dbname=" . $db_cfg['name'] . ";charset=" . $db_cfg['charset'];
        $pdo_db = new PDO($dsn_db, $db_cfg['user'], $db_cfg['pass'], $options);
        $connection_status = 'connected_db';
        
        $stmt_tables = $pdo_db->query("SHOW TABLES");
        $tables = $stmt_tables->fetchAll(PDO::FETCH_COLUMN);
        
        if (!empty($tables)) {
            $tables_list = $tables;
            $installed_tables_count = count($tables);
            
            if (in_class_array('user', $tables) && in_class_array('produk', $tables)) {
                $tables_installed = true;
            }
        }
    }
} catch (PDOException $e) {
    $connection_status = 'failed';
    $connection_message = $e->getMessage();
}

function in_class_array($needle, $haystack) {
    return in_array(strtolower($needle), array_map('strtolower', $haystack));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'run_setup') {
    try {
        $dsn_server = "mysql:host=" . $db_cfg['host'] . ";charset=" . $db_cfg['charset'];
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => true,
        ];
        $pdo = new PDO($dsn_server, $db_cfg['user'], $db_cfg['pass'], $options);
        
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `" . $db_cfg['name'] . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        
        $pdo->exec("USE `" . $db_cfg['name'] . "`");
        
        $sql_path = __DIR__ . '/database/inventaris_busana.sql';
        if (!file_exists($sql_path)) {
            throw new Exception("Berkas SQL utama tidak ditemukan di: database/inventaris_busana.sql");
        }
        
        $sql_content = file_get_contents($sql_path);
        
        $pdo->exec($sql_content);
        
        $users_to_seed = [
            [
                'email' => 'admin@fashionims.com',
                'password' => 'admin123',
                'name' => 'Administrator',
                'role' => 'admin'
            ],
            [
                'email' => 'staff@fashionims.com',
                'password' => 'staff123',
                'name' => 'Staff Gudang',
                'role' => 'staff'
            ]
        ];
        
        foreach ($users_to_seed as $u) {
            $stmt_del = $pdo->prepare("DELETE FROM user WHERE email = ?");
            $stmt_del->execute([$u['email']]);
            
            $password_hash = password_hash($u['password'], PASSWORD_DEFAULT);
            $stmt_ins = $pdo->prepare("INSERT INTO user (name, email, password, role) VALUES (?, ?, ?, ?)");
            $stmt_ins->execute([$u['name'], $u['email'], $password_hash, $u['role']]);
        }
        
        $success = "Database berhasil diinisialisasi! Akun demo Administrator dan Staff telah dibuat.";
        $step = 'success';
        
        $stmt_tables = $pdo->query("SHOW TABLES");
        $tables_list = $stmt_tables->fetchAll(PDO::FETCH_COLUMN);
        $installed_tables_count = count($tables_list);
        $tables_installed = true;
        $database_exists = true;
        
    } catch (Exception $e) {
        $error = $e->getMessage();
        $step = 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Database — FashionIMS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL_SETUP ?>/assets/css/style.css?v=3.2.0">
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    <style>
        body {
            background: linear-gradient(135deg, #f0f4f5 0%, #dbeafe 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            padding: 1.5rem;
        }
        .setup-container {
            width: 100%;
            max-width: 580px;
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.5);
            border-radius: 16px;
            box-shadow: 0 20px 40px rgba(51, 92, 103, 0.1);
            overflow: hidden;
        }
        .setup-header {
            background: var(--teal);
            color: white;
            padding: 2rem;
            text-align: center;
            position: relative;
        }
        .setup-header h1 {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 1.5rem;
            font-weight: 800;
            margin-top: 0.5rem;
        }
        .setup-header p {
            font-size: 0.85rem;
            color: rgba(255, 255, 255, 0.7);
            margin-top: 0.25rem;
        }
        .setup-body {
            padding: 2rem;
        }
        .status-card {
            background: #ffffff;
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 1.25rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }
        .status-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.5rem 0;
            border-bottom: 1px solid #f1f5f9;
        }
        .status-row:last-child {
            border-bottom: none;
        }
        .status-label {
            font-weight: 600;
            font-size: 0.85rem;
            color: var(--text);
        }
        .setup-steps {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1.5rem;
            position: relative;
        }
        .setup-steps::before {
            content: '';
            position: absolute;
            top: 15px;
            left: 10%;
            right: 10%;
            height: 2px;
            background: var(--border);
            z-index: 1;
        }
        .step-item {
            position: relative;
            z-index: 2;
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 30%;
        }
        .step-circle {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: white;
            border: 2px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.85rem;
            color: var(--muted);
            transition: all 0.3s;
        }
        .step-item.active .step-circle {
            border-color: var(--amber);
            background: var(--amber-light);
            color: var(--amber-dark);
            box-shadow: 0 0 0 4px rgba(224, 159, 62, 0.15);
        }
        .step-item.done .step-circle {
            border-color: var(--green);
            background: var(--green-light);
            color: var(--green);
        }
        .step-text {
            font-size: 0.72rem;
            font-weight: 600;
            color: var(--muted);
            margin-top: 0.5rem;
            text-align: center;
        }
        .step-item.active .step-text {
            color: var(--text);
        }
        .step-item.done .step-text {
            color: var(--green);
        }
        .table-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 0.5rem;
            margin-top: 0.75rem;
        }
        .table-tag {
            background: var(--bg);
            border: 1px solid var(--border);
            border-radius: 6px;
            padding: 0.35rem 0.5rem;
            font-size: 0.78rem;
            font-family: monospace;
            color: var(--teal);
            display: flex;
            align-items: center;
            gap: 0.35rem;
        }
        .btn-loading {
            position: relative;
            color: transparent !important;
            pointer-events: none;
        }
        .btn-loading::after {
            content: '';
            position: absolute;
            width: 16px;
            height: 16px;
            top: 0; left: 0; right: 0; bottom: 0;
            margin: auto;
            border: 2px solid transparent;
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.6s linear infinite;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        .pulse {
            animation: pulse-animation 2s infinite;
        }
        @keyframes pulse-animation {
            0% { box-shadow: 0 0 0 0 rgba(224, 159, 62, 0.4); }
            70% { box-shadow: 0 0 0 10px rgba(224, 159, 62, 0); }
            100% { box-shadow: 0 0 0 0 rgba(224, 159, 62, 0); }
        }
    </style>
</head>
<body>

<div class="setup-container fade-in">
    <div class="setup-header">
        <div style="width: 54px; height: 54px; background: var(--amber); border-radius: 12px; margin: 0 auto; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 14px rgba(224, 159, 62, 0.4);">
            <i data-lucide="database" style="width: 28px; height: 28px; color: white;"></i>
        </div>
        <h1>Setup Database</h1>
        <p>Inisialisasi sistem inventori FashionIMS Anda dengan mudah</p>
    </div>
    
    <div class="setup-body">
        
        <!-- Setup Steps Visualizer -->
        <div class="setup-steps">
            <div class="step-item done">
                <div class="step-circle">✓</div>
                <div class="step-text">Koneksi Host</div>
            </div>
            <div class="step-item <?= $database_exists ? 'done' : 'active' ?>">
                <div class="step-circle"><?= $database_exists ? '✓' : '2' ?></div>
                <div class="step-text">Buat Database</div>
            </div>
            <div class="step-item <?= $tables_installed ? 'done' : ($database_exists ? 'active' : '') ?>">
                <div class="step-circle"><?= $tables_installed ? '✓' : '3' ?></div>
                <div class="step-text">Impor Skema</div>
            </div>
        </div>

        <?php if ($error): ?>
            <!-- Error State Card -->
            <div style="background:#fde8e8; border:1px solid rgba(158,42,43,0.3); border-radius:12px; padding:1.25rem; margin-bottom:1.5rem; color:#9e2a2b;">
                <div style="display:flex; align-items:center; gap:0.5rem; font-weight:700; margin-bottom:0.5rem;">
                    <i data-lucide="alert-triangle" style="width:20px; height:20px; color:#9e2a2b;"></i>
                    Instalasi Gagal
                </div>
                <p style="font-size:0.85rem; line-height:1.5;"><?= htmlspecialchars($error) ?></p>
                <div style="margin-top:1rem; padding-top:0.75rem; border-top:1px solid rgba(158,42,43,0.15); font-size:0.78rem; color:#64748b;">
                    <strong>Saran perbaikan:</strong> Pastikan password user database pada <code>config/database.php</code> sudah sesuai dan database server MySQL Anda menyala.
                </div>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <!-- Success State Card -->
            <div style="background:#dcfce7; border:1px solid rgba(22,163,74,0.3); border-radius:12px; padding:1.25rem; margin-bottom:1.5rem; color:#16a34a;">
                <div style="display:flex; align-items:center; gap:0.5rem; font-weight:700; margin-bottom:0.5rem;">
                    <i data-lucide="check-circle-2" style="width:20px; height:20px; color:#16a34a;"></i>
                    Setup Berhasil!
                </div>
                <p style="font-size:0.85rem; line-height:1.5;"><?= htmlspecialchars($success) ?></p>
            </div>
        <?php endif; ?>

        <!-- Database Connection Information Card -->
        <div class="status-card">
            <h3 style="font-size:0.88rem; font-weight:700; color:var(--text); margin-bottom:0.75rem; display:flex; align-items:center; gap:0.35rem;">
                <i data-lucide="settings" style="width:16px; height:16px; color:var(--muted);"></i>
                Konfigurasi Aktif (<code>config/database.php</code>)
            </h3>
            
            <div class="status-row">
                <span class="status-label">Host</span>
                <span class="badge badge-teal" style="font-family:monospace;"><?= htmlspecialchars($db_cfg['host']) ?></span>
            </div>
            <div class="status-row">
                <span class="status-label">Nama Database</span>
                <span class="badge badge-teal" style="font-family:monospace;"><?= htmlspecialchars($db_cfg['name']) ?></span>
            </div>
            <div class="status-row">
                <span class="status-label">User MySQL</span>
                <span class="badge badge-teal" style="font-family:monospace;"><?= htmlspecialchars($db_cfg['user']) ?></span>
            </div>
            <div class="status-row">
                <span class="status-label">Status Server</span>
                <?php if ($connection_status === 'failed'): ?>
                    <span class="badge badge-red" style="font-size:0.7rem;"><i data-lucide="x" style="width:11px;height:11px;"></i> Terputus</span>
                <?php else: ?>
                    <span class="badge badge-green" style="font-size:0.7rem;"><i data-lucide="check" style="width:11px;height:11px;"></i> Terhubung</span>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($connection_status === 'failed'): ?>
            <!-- Connection Failed View -->
            <div class="card" style="padding:1rem; background:#fff5f5; border-color:rgba(158,42,43,0.15); margin-bottom:1.5rem;">
                <p style="font-size:0.8rem; color:var(--red); font-weight:500; display:flex; align-items:flex-start; gap:0.5rem; line-height:1.4;">
                    <i data-lucide="info" style="width:16px; height:16px; flex-shrink:0;"></i>
                    MySQL error: <code><?= htmlspecialchars($connection_message) ?></code>
                </p>
            </div>
            <div style="display:flex; gap:0.75rem;">
                <a href="<?= BASE_URL_SETUP ?>/setup.php" class="btn btn-white" style="flex:1; justify-content:center;">
                    <i data-lucide="rotate-cw"></i> Coba Hubungkan Kembali
                </a>
            </div>
            
        <?php else: ?>
            <!-- Connection Succeeded View -->
            
            <?php if ($tables_installed && $step !== 'success'): ?>
                <!-- Already Set Up View -->
                <div style="background:var(--teal-light); border:1px solid rgba(51,92,103,0.15); border-radius:12px; padding:1.25rem; margin-bottom:1.5rem;">
                    <div style="display:flex; align-items:center; gap:0.5rem; font-weight:700; color:var(--teal); margin-bottom:0.5rem;">
                        <i data-lucide="check" style="width:20px; height:20px; color:var(--teal);"></i>
                        Database Sudah Siap!
                    </div>
                    <p style="font-size:0.85rem; color:#475569; line-height:1.5;">
                        Seluruh tabel dan skema database telah terinstal secara lengkap (terdapat <strong><?= $installed_tables_count ?> tabel</strong> terdaftar). Anda dapat langsung masuk ke dashboard.
                    </p>
                    
                    <div class="table-grid">
                        <?php foreach ($tables_list as $t): ?>
                            <span class="table-tag"><i data-lucide="table" style="width:12px;height:12px;"></i> <?= htmlspecialchars($t) ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <div style="display:flex; flex-direction:column; gap:0.75rem;">
                    <a href="<?= BASE_URL_SETUP ?>/login.php" class="btn btn-teal" style="justify-content:center; padding:0.75rem; font-size:0.9rem;">
                        <i data-lucide="log-in"></i> Pergi ke Halaman Login
                    </a>
                    <form method="POST" action="" onsubmit="return confirm('PERINGATAN! Tindakan ini akan menghapus ulang database lama Anda dan membuat database segar dengan data sampel baru. Lanjutkan?')">
                        <input type="hidden" name="action" value="run_setup">
                        <button type="submit" class="btn btn-white" style="width:100%; justify-content:center; color:var(--red); border-color:rgba(158,42,43,0.25);">
                            <i data-lucide="refresh-cw"></i> Reset & Impor Ulang Database
                        </button>
                    </form>
                </div>
                
            <?php elseif ($step === 'success'): ?>
                <!-- Success State Navigation -->
                <div style="background:var(--green-light); border:1px solid rgba(22,163,74,0.15); border-radius:12px; padding:1.25rem; margin-bottom:1.5rem;">
                    <h4 style="font-weight:700; color:#16a34a; font-size:0.88rem; margin-bottom:0.5rem;">Tabel Terbuat (<?= $installed_tables_count ?>):</h4>
                    <div class="table-grid">
                        <?php foreach ($tables_list as $t): ?>
                            <span class="table-tag" style="background:white; border-color:rgba(22,163,74,0.25); color:#16a34a;"><i data-lucide="table" style="width:12px;height:12px;"></i> <?= htmlspecialchars($t) ?></span>
                        <?php endforeach; ?>
                    </div>
                    <div style="margin-top:1rem; padding:0.875rem; background:white; border-radius:8px; border:1px solid rgba(22,163,74,0.2); display:flex; flex-direction:column; gap:0.75rem;">
                        <div>
                            <p style="font-size:0.75rem; font-weight:600; color:var(--teal); margin-bottom:0.25rem; display:flex; align-items:center; gap:0.35rem;">
                                <i data-lucide="key" style="width:14px; height:14px;"></i> Akun Demo Administrator (Admin)
                            </p>
                            <p style="font-size:0.8rem; color:#475569; margin: 0;">Email: <strong>admin@fashionims.com</strong></p>
                            <p style="font-size:0.8rem; color:#475569; margin: 0;">Password: <strong>admin123</strong></p>
                        </div>
                        <div style="border-top:1px solid #f1f5f9; padding-top:0.5rem;">
                            <p style="font-size:0.75rem; font-weight:600; color:var(--teal); margin-bottom:0.25rem; display:flex; align-items:center; gap:0.35rem;">
                                <i data-lucide="key" style="width:14px; height:14px;"></i> Akun Demo Staff Gudang (Staff)
                            </p>
                            <p style="font-size:0.8rem; color:#475569; margin: 0;">Email: <strong>staff@fashionims.com</strong></p>
                            <p style="font-size:0.8rem; color:#475569; margin: 0;">Password: <strong>staff123</strong></p>
                        </div>
                    </div>
                </div>
                
                <a href="<?= BASE_URL_SETUP ?>/login.php" class="btn btn-teal" style="width:100%; justify-content:center; padding:0.75rem; font-size:0.9rem;">
                    <i data-lucide="log-in"></i> Pergi ke Halaman Login
                </a>

            <?php else: ?>
                <!-- Pending Setup View (database missing or tables missing) -->
                <div style="background:var(--amber-light); border:1px solid rgba(224,159,62,0.15); border-radius:12px; padding:1.25rem; margin-bottom:1.5rem; color:var(--amber-dark);">
                    <div style="display:flex; align-items:center; gap:0.5rem; font-weight:700; margin-bottom:0.5rem;">
                        <i data-lucide="help-circle" style="width:20px; height:20px;"></i>
                        Database Belum Siap
                    </div>
                    <p style="font-size:0.85rem; line-height:1.5; color:#64748b;">
                        <?php if (!$database_exists): ?>
                            Database <strong><?= htmlspecialchars($db_cfg['name']) ?></strong> belum ada di server MySQL. Skrip setup akan membuat database ini dan mengimpor data sampel.
                        <?php else: ?>
                            Database <strong><?= htmlspecialchars($db_cfg['name']) ?></strong> ada, tetapi beberapa tabel skema penting belum terinstal dengan benar.
                        <?php endif; ?>
                    </p>
                </div>
                
                <form method="POST" action="" id="setupForm">
                    <input type="hidden" name="action" value="run_setup">
                    <button type="submit" id="btnSubmit" class="btn btn-primary pulse" style="width:100%; justify-content:center; padding:0.75rem; font-size:0.9rem;">
                        <i data-lucide="play"></i> Mulai Instalasi Database
                    </button>
                </form>
            <?php endif; ?>
            
        <?php endif; ?>

    </div>
</div>

<script>
    lucide.createIcons();
    
    const form = document.getElementById('setupForm');
    const btn = document.getElementById('btnSubmit');
    if (form && btn) {
        form.addEventListener('submit', function() {
            btn.classList.add('btn-loading');
            btn.classList.remove('pulse');
        });
    }
</script>
</body>
</html>
