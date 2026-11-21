<?php
require __DIR__.'/config.php';manager_only();$u=user();
if($_SERVER['REQUEST_METHOD']==='POST'){
 verify_post();$name=post('name');$old=post('current_password');$new=post('new_password');
 if(mb_strlen($name)<3)notice('Nama minimal 3 karakter.','danger');elseif($new!==''&&!password_verify($old,$u['password_hash']))notice('Password lama tidak cocok.','danger');elseif($new!==''&&strlen($new)<10)notice('Password baru minimal 10 karakter.','danger');else{$q=db()->prepare('UPDATE users SET name=?,password_hash=? WHERE id=?');$q->execute([$name,$new!==''?password_hash($new,PASSWORD_DEFAULT):$u['password_hash'],$u['id']]);notice('Profil berhasil disimpan.');}go('profil.php');
}
$pageTitle='Profil';$active='profil';require __DIR__.'/partials/admin_header.php';
?><div class="page-head"><div><h1>Profil pengurus</h1><p>Perbarui nama dan password akun Anda.</p></div></div><section class="card" style="max-width:600px"><form method="post" class="form-grid"><?=csrf_input()?><label class="full">Nama<input required name="name" minlength="3" value="<?=h($u['name'])?>"></label><label class="full">Email (tidak dapat diubah di sini)<input value="<?=h($u['email'])?>" disabled></label><label class="full">Password saat ini (jika akan diganti)<input name="current_password" type="password"></label><label class="full">Password baru (jika akan diganti)<input name="new_password" minlength="10" type="password"></label><button class="btn primary">Simpan profil</button></form></section><?php require __DIR__.'/partials/admin_footer.php'; ?>
