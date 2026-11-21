<?php
require __DIR__.'/config.php';
if((int)db()->query('SELECT COUNT(*) FROM users')->fetchColumn()>0)go('login.php');
$error='';
$setupKey=(string)(getenv('ADMIN_SETUP_KEY') ?: '');
if ($setupKey === '') { http_response_code(503); exit('ADMIN_SETUP_KEY wajib diatur sebagai Environment Variable Vercel sebelum membuat admin pertama.'); }
if($_SERVER['REQUEST_METHOD']==='POST'){
    verify_post();$name=post('name');$email=strtolower(post('email'));$pass=post('password');
    if (!hash_equals($setupKey, post('setup_key'))) { $error='Kunci aktivasi tidak benar.'; }
    else {
    if(mb_strlen($name)<3||!filter_var($email,FILTER_VALIDATE_EMAIL)||strlen($pass)<10)$error='Isi nama, email yang benar, dan password minimal 10 karakter.';
    else {try{$q=db()->prepare("INSERT INTO users (name,email,password_hash,role) VALUES (?,?,?,'admin')");$q->execute([$name,$email,password_hash($pass,PASSWORD_DEFAULT)]);session_regenerate_id(true);$_SESSION['uid']=(int)db()->lastInsertId();go('dashboard.php');}catch(PDOException $e){$error='Akun tidak dapat dibuat. Periksa email atau database.';}}
    }
}
?><!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Setup administrator</title><link rel="stylesheet" href="<?=h(link_to('assets/style.css'))?>"></head><body class="auth-page"><form method="post" class="auth-card"><?=csrf_input()?><div class="brand-kicker">MASJID RAYA TAMAN CAHAYA</div><h1>Siapkan administrator</h1><p>Akun pertama dibuat satu kali. Tidak ada password bawaan.</p><?php if($error):?><div class="notice danger"><?=h($error)?></div><?php endif;?><label>Kunci aktivasi (ADMIN_SETUP_KEY)<input type="password" name="setup_key" required autocomplete="off"></label><label>Nama lengkap<input name="name" required minlength="3"></label><label>Email<input type="email" name="email" required></label><label>Password <small>(minimal 10 karakter)</small><input type="password" name="password" required minlength="10"></label><button class="btn primary block">Buat administrator</button></form></body></html>
