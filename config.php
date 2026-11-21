<?php
declare(strict_types=1);
// Konfigurasi khusus deployment Vercel Container/FrankenPHP.
// Jangan menuliskan password database di repository.
const APP_NAME = 'SIM Imam Rawatib';
const BASE_PATH = '';
define('DB_HOST', (string)(getenv('DB_HOST') ?: '127.0.0.1'));
define('DB_PORT', (string)(getenv('DB_PORT') ?: '3306'));
define('DB_NAME', (string)(getenv('DB_NAME') ?: 'sim_imam_rawatib_lengkap'));
define('DB_USER', (string)(getenv('DB_USER') ?: ''));
define('DB_PASS', (string)(getenv('DB_PASS') ?: ''));
date_default_timezone_set('Asia/Jakarta');
require_once __DIR__.'/lib/db.php';
if (session_status() === PHP_SESSION_NONE) {
    session_name('sim_imam_rawatib');
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.gc_maxlifetime', '7200');
    $secure = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    if (getenv('VERCEL') === '1' || (string)($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https') {
        $secure = true;
    }
    // Vercel Functions bersifat ephemeral: PHP session berbasis file tidak persisten.
    if ((getenv('SESSION_STORE') ?: 'database') === 'database') {
        require_once __DIR__.'/lib/db_sessions.php';
        session_set_save_handler(new DatabaseSessionHandler(db()), true);
    }
    session_set_cookie_params([
        'lifetime' => 0, 'path' => '/', 'httponly' => true,
        'secure' => $secure, 'samesite' => 'Lax'
    ]);
    session_start();
}
require_once __DIR__.'/lib/helpers.php';
require_once __DIR__.'/lib/auth.php';
require_once __DIR__.'/lib/schedule.php';
