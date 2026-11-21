<?php
declare(strict_types=1);
function db(): PDO {
    static $instance = null;
    if ($instance instanceof PDO) return $instance;
    if (DB_HOST === '' || DB_NAME === '' || DB_USER === '' || DB_PASS === '') {
        http_response_code(503);
        exit('Konfigurasi database belum lengkap. Isi DB_HOST, DB_PORT, DB_NAME, DB_USER, DB_PASS di Vercel Environment Variables.');
    }
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_TIMEOUT => 6,
    ];
    // Jika penyedia DB mewajibkan CA, pasang sertifikat di image dan set DB_SSL_CA_PATH.
    $ca = (string)(getenv('DB_SSL_CA_PATH') ?: '');
    if ($ca !== '') {
        if (!is_readable($ca)) { http_response_code(503); exit('Sertifikat CA MySQL tidak ditemukan pada path DB_SSL_CA_PATH.'); }
        $options[PDO::MYSQL_ATTR_SSL_CA] = $ca;
        $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = true;
    }
    try {
        $instance = new PDO(
            'mysql:host='.DB_HOST.';port='.DB_PORT.';dbname='.DB_NAME.';charset=utf8mb4',
            DB_USER, DB_PASS, $options
        );
    } catch (PDOException $exception) {
        error_log('SIM Imam Rawatib: koneksi MySQL tidak tersedia ('.(int)$exception->getCode().').');
        http_response_code(503);
        exit('Database tidak dapat dihubungi. Pastikan database cloud aktif, port dan kredensial benar, serta koneksi Vercel diizinkan.');
    }
    return $instance;
}
