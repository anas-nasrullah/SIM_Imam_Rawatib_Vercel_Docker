<?php
require __DIR__.'/config.php';$u=imam_only();
if($_SERVER['REQUEST_METHOD']==='POST'){
 verify_post();$name=post('name');$old=post('current_password');$new=post('new_password');
 if(mb_strlen($name)<3)notice('Nama terlalu pendek.','danger');elseif($new!==''&&!password_verify($old,$u['password_hash']))notice('Password lama tidak cocok.','danger');elseif($new!==''&&strlen($new)<10)notice('Password baru minimal 10 karakter.','danger');else{$q=db()->prepare('UPDATE users SET name=?,password_hash=? WHERE id=?');$q->execute([$name,$new!==''?password_hash($new,PASSWORD_DEFAULT):$u['password_hash'],$u['id']]);notice('Profil berhasil disimpan.');}go('imam_profil.php');
}
$pageTitle='Profil imam';$active='profil';require __DIR__.'/partials/mobile_header.php';
?><h1>Profil saya</h1><p class="caption">Akun imam dan pengamanan akses.</p><section class="phone-card mt24"><h3>Informasi akun</h3><form method="post"><?=csrf_input()?><label class="mt16">Nama lengkap<input required name="name" minlength="3" value="<?=h($u['name'])?>"></label><label class="mt16">Email akun<input value="<?=h($u['email'])?>" disabled></label><label class="mt16">Password lama (jika mengganti password)<input type="password" name="current_password"></label><label class="mt16">Password baru (minimal 10 karakter)<input type="password" name="new_password" minlength="10"></label><button class="btn primary block mt16">Simpan perubahan</button></form><form method="post" action="<?=h(link_to('logout.php'))?>" class="mt16"><?=csrf_input()?><button class="btn outline block">Keluar akun</button></form></section>
<?php require __DIR__.'/partials/mobile_footer.php'; ?>
