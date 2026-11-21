<?php
require __DIR__.'/config.php';manager_only();
if($_SERVER['REQUEST_METHOD']==='POST'){
 verify_post();$name=post('masjid_name');$base=post('honor_base');$extra=post('extra_per_schedule');$ded=post('deduction_per_unavailable');
 $vals=['masjid_name'=>$name,'honor_base'=>$base,'extra_per_schedule'=>$extra,'deduction_per_unavailable'=>$ded];
 foreach(prayers() as $p)$vals['time_'.$p]=post('time_'.$p);
 $ok=mb_strlen($name)>=4&&mb_strlen($name)<=120;
 foreach(['honor_base','extra_per_schedule','deduction_per_unavailable'] as $key){if(!preg_match('/^\d{1,10}$/',$vals[$key])||(int)$vals[$key]>1000000000)$ok=false;}
 foreach(prayers() as $p)if(!preg_match('/^([01]\d|2[0-3]):[0-5]\d$/',$vals['time_'.$p]))$ok=false;
 if(!$ok)notice('Pengaturan tidak valid. Nilai nominal harus 0–1 miliar dan waktu berformat HH:MM.','danger');else{ $q=db()->prepare('INSERT INTO settings (`key`,`value`) VALUES (?,?) ON DUPLICATE KEY UPDATE `value`=VALUES(`value`)');foreach($vals as $k=>$v)$q->execute([$k,$v]);audit('ubah_pengaturan','Aturan bisyaroh dan jam default');notice('Pengaturan berhasil disimpan.'); }go('pengaturan.php');
}
$pageTitle='Pengaturan masjid';$active='settings';require __DIR__.'/partials/admin_header.php';
?><div class="page-head"><div><div class="section-eyebrow">KONFIGURASI SISTEM</div><h1>Pengaturan</h1><p>Aturan bisyaroh dan jam default untuk jadwal baru. Perubahan tidak mengubah jam jadwal yang sudah ada.</p></div></div><div class="card"><form method="post" class="form-grid"><?=csrf_input()?><label class="full">Nama masjid<input required maxlength="120" name="masjid_name" value="<?=h(setting('masjid_name','Masjid Raya Taman Cahaya'))?>"></label><label>Bisyaroh dasar per imam per bulan (Rp)<input required name="honor_base" type="number" min="0" max="1000000000" step="1" value="<?=h(setting('honor_base','1000000'))?>"></label><label>Tambahan per tugas ekstra yang disanggupi (Rp)<input required name="extra_per_schedule" type="number" min="0" max="1000000000" step="1" value="<?=h(setting('extra_per_schedule','0'))?>"></label><label>Potongan untuk tiap jawaban 'tidak bisa' (Rp)<input required name="deduction_per_unavailable" type="number" min="0" max="1000000000" step="1" value="<?=h(setting('deduction_per_unavailable','0'))?>"></label><div class="full"><h2>Jam default penugasan (WIB)</h2><p class="caption">Sebagai isian awal otomatis, bukan jadwal resmi dari masjid atau sumber waktu salat.</p></div><?php foreach(prayers() as $p):?><label><?=h($p)?><input type="time" name="<?=h('time_'.$p)?>" required value="<?=h(setting('time_'.$p,time_default()[$p]))?>"></label><?php endforeach;?><div class="admin-form-actions"><button class="btn primary">Simpan pengaturan</button></div></form></div>
<?php require __DIR__.'/partials/admin_footer.php'; ?>
