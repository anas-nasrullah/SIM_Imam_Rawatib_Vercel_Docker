<?php
require __DIR__.'/config.php';if(user())go(login_destination());
if((int)db()->query('SELECT COUNT(*) FROM users')->fetchColumn()===0)go('setup.php');
$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){
 verify_post();$attempts=$_SESSION['login_fail']??[];$attempts=array_values(array_filter($attempts,fn($x)=>$x>time()-900));
 if(count($attempts)>=7)$error='Terlalu banyak percobaan. Coba lagi 15 menit kemudian.';
 else { $email=strtolower(post('email'));$pass=post('password');$q=db()->prepare('SELECT * FROM users WHERE email=? AND active=1');$q->execute([$email]);$r=$q->fetch();
 if($r&&password_verify($pass,$r['password_hash'])){session_regenerate_id(true);$_SESSION['uid']=(int)$r['id'];unset($_SESSION['login_fail']);go($r['role']==='imam'?'imam_home.php':'dashboard.php');}
 $attempts[]=time();$_SESSION['login_fail']=$attempts;$error='Email atau password tidak cocok.';}
}
?><!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Masuk · SIM Imam Rawatib</title><link rel="stylesheet" href="<?=h(link_to('assets/style.css'))?>"></head><body class="auth-page"><form method="post" class="auth-card"><?=csrf_input()?><span class="brand-kicker">MASJID RAYA TAMAN CAHAYA</span><h1>Assalamu’alaikum.</h1><p>Masuk ke SIM Imam Rawatib untuk mengelola jadwal dan bisyaroh.</p><?php if($error):?><div class="notice danger"><?=h($error)?></div><?php endif;?><label>Alamat email<input type="email" name="email" autocomplete="username" required></label><label>Password<input type="password" name="password" autocomplete="current-password" required></label><button type="submit" class="btn primary block">Masuk</button><small class="muted">Akun imam dibuat oleh administrator masjid.</small></form></body></html>
