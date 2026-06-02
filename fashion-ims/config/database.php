<?php

define('DB_HOST', 'localhost');
define('DB_NAME', 'inventaris_busana');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

define('BASE_URL', '/fashion-ims');
define('UPLOAD_PATH', __DIR__ . '/../assets/uploads/');
define('UPLOAD_URL', BASE_URL . '/assets/uploads/');

try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    http_response_code(500);
    die('<div style="font-family:monospace;padding:2rem;background:#1A2540;color:#E05C5C;border-radius:8px;">
        <h2>
            <svg style="width:24px;height:24px;color:#E05C5C;display:inline-block;vertical-align:middle;margin-right:0.5rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            Database Connection Failed
        </h2>
        <p>' . htmlspecialchars($e->getMessage()) . '</p>
        <p style="color:#8A9BBF;">Pastikan Laragon sudah running dan database <strong>inventaris_busana</strong> sudah diimport.</p>
        <p><a href="' . BASE_URL . '/setup.php" style="color:#D4A853;">→ Jalankan Setup</a></p>
    </div>');
}

function setFlash(string $type, string $message): void {
    if (session_status() === PHP_SESSION_NONE) session_start();
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function rupiah(float $amount): string {
    return 'Rp ' . number_format($amount, 0, ',', '.');
}

function redirect(string $url): void {
    header('Location: ' . BASE_URL . $url);
    exit;
}

function e(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}
