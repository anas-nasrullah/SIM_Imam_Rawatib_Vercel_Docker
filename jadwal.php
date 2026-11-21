<?php
require __DIR__.'/config.php';manager_only();$month=guard_month();
if($_SERVER['REQUEST_METHOD']==='POST'){
 verify_post();$action=post('action');$month=post('bulan');if(!valid_month($month))$month=date('Y-m');
 try {
 if($action==='generate'){
   $ims=db()->query('SELECT id FROM imams WHERE active=1 ORDER BY id')->fetchAll(PDO::FETCH_COLUMN);
   if(!$ims)throw new RuntimeException('Tambahkan imam aktif terlebih dahulu.');
   $days=(int)date('t',strtotime($month.'-01'));$times=time_default();$done=0;
   $q=db()->prepare("INSERT IGNORE INTO schedules (prayer_date,prayer,imam_id,start_time,created_by) VALUES (?,?,?,?,?)");
   for($d=1;$d<=$days;$d++)foreach(prayers() as $k=>$p){$imam=$ims[(($d-1)*3+$k)%count($ims)];$q->execute([$month.'-'.str_pad((string)$d,2,'0',STR_PAD_LEFT),$p,$imam,setting('time_'.$p,$times[$p]),user()['id']]);$done+=$q->rowCount();}
   audit('buat_jadwal_masal',$month.' · '.$done.' jadwal baru');notice($done.' jadwal berhasil dibuat. Jadwal yang sudah ada tidak ditimpa.');
 }elseif($action==='save'){
   $id=(int)post('id');$date=post('date');$prayer=post('prayer');$imam=(int)post('imam_id');$time=post('start_time');$isExtra=isset($_POST['is_extra'])?1:0;
   if(!valid_date($date)||!in_array($prayer,prayers(),true)||$imam<1||!preg_match('/^([01]\d|2[0-3]):[0-5]\d$/',$time))throw new RuntimeException('Isi tanggal, waktu, imam, dan jenis sholat dengan benar.');
   $chk=db()->prepare('SELECT COUNT(*) FROM imams WHERE id=? AND active=1');$chk->execute([$imam]);if(!$chk->fetchColumn())throw new RuntimeException('Imam tidak ada atau nonaktif.');
   if($id>0){
      $old=db()->prepare('SELECT * FROM schedules WHERE id=?');$old->execute([$id]);$o=$old->fetch();if(!$o)throw new RuntimeException('Jadwal tidak ditemukan.');
      $changed=((int)$o['imam_id']!==$imam);
      if($changed && $o['attendance']!=='belum')throw new RuntimeException('Penugasan telah memiliki absensi. Koreksi absensi terlebih dahulu sebelum mengganti imam.');
      db()->beginTransaction();
      if($changed && $o['confirmation']==='tidak_bisa'){
         $qRef=db()->prepare('INSERT IGNORE INTO schedule_refusals (schedule_id,imam_id,prayer_date,prayer,note) VALUES (?,?,?,?,?)');
         $qRef->execute([$id,$o['imam_id'],$o['prayer_date'],$o['prayer'],$o['confirmation_note']]);
      }
      $q=db()->prepare("UPDATE schedules SET prayer_date=?,prayer=?,imam_id=?,start_time=?,is_extra=?,confirmation=IF(?=1,'pending',confirmation),confirmation_at=IF(?=1,NULL,confirmation_at),attendance=IF(?=1,'belum',attendance),attendance_at=IF(?=1,NULL,attendance_at) WHERE id=?");
      $q->execute([$date,$prayer,$imam,$time,$isExtra,(int)$changed,(int)$changed,(int)$changed,(int)$changed,$id]);
      db()->commit();
      audit('ubah_jadwal','Jadwal #'.$id.($changed?' · imam diganti':''));notice('Penugasan berhasil diperbarui.');
   }else{$q=db()->prepare('INSERT INTO schedules (prayer_date,prayer,imam_id,start_time,is_extra,created_by) VALUES (?,?,?,?,?,?)');$q->execute([$date,$prayer,$imam,$time,$isExtra,user()['id']]);audit('tambah_jadwal',$date.' · '.$prayer);notice('Jadwal baru ditambahkan.');}
   $month=substr($date,0,7);
 }elseif($action==='delete'){$id=(int)post('id');$q=db()->prepare('DELETE FROM schedules WHERE id=?');$q->execute([$id]);audit('hapus_jadwal','#'.$id);notice('Jadwal dihapus.');}
 else throw new RuntimeException('Aksi tidak dikenali.');
 }catch(PDOException $e){if(db()->inTransaction())db()->rollBack();notice($e->getCode()==='23000'?'Jadwal sholat pada tanggal itu sudah memiliki imam. Edit jadwal yang ada.':'Gagal menyimpan data. Periksa isian dan koneksi database.','danger');}
 catch(RuntimeException $e){if(db()->inTransaction())db()->rollBack();notice($e->getMessage(),'danger');}
 go('jadwal.php',['bulan'=>$month]);
}
$edit=(int)queryv('edit','0');$selected=null;if($edit){$q=db()->prepare('SELECT * FROM schedules WHERE id=?');$q->execute([$edit]);$selected=$q->fetch();}
$rows=get_month_schedules($month);$imams=all_imams(true);$pageTitle='Atur jadwal';$active='jadwal';require __DIR__.'/partials/admin_header.php';
?>
<div class="page-head"><div><div class="section-eyebrow">PENGELOLAAN PENUGASAN</div><h1>Atur jadwal imam</h1><p>Tambahkan atau ubah satu imam untuk setiap waktu sholat di satu tanggal.</p></div><a class="btn outline" href="<?=h(link_to('kalender.php',['bulan'=>$month]))?>">Lihat kalender lengkap</a></div>
<div class="card"><div class="space-between"><div><h2>Jadwal <?=h(month_label($month))?></h2><div class="caption"><?=count($rows)?> slot tersimpan</div></div><div class="flex"><form method="get" class="flex"><input type="month" name="bulan" style="width:160px" value="<?=h($month)?>"><button class="btn small outline">Buka</button></form><form method="post" data-confirm="Buat jadwal otomatis seluruh bulan? Jadwal lama tidak ditimpa."><?=csrf_input()?><input type="hidden" name="bulan" value="<?=h($month)?>"><input type="hidden" name="action" value="generate"><button class="btn primary small">Buat jadwal otomatis</button></form></div></div><div class="table-scroll mt16"><table class="data-table"><thead><tr><th>Tanggal</th><th>Waktu</th><th>Jam</th><th>Nama imam</th><th>Kesediaan</th><th>Absensi</th><th>Aksi</th></tr></thead><tbody><?php foreach($rows as $r):?><tr><td><?=h(date('d/m/Y',strtotime($r['prayer_date'])))?></td><td><?=h($r['prayer'])?><?=$r['is_extra']?' ★':''?></td><td><?=h(substr($r['start_time'],0,5))?></td><td><strong><?=h($r['imam_name'])?></strong></td><td><?=pill(status_confirmation($r['confirmation']),$r['confirmation']==='tidak_bisa'?'bad':'neutral')?></td><td><?=pill(status_attendance($r['attendance']))?></td><td><div class="action-row"><a class="btn small outline" href="<?=h(link_to('jadwal.php',['bulan'=>$month,'edit'=>$r['id']]))?>#form">Edit</a><form method="post" data-confirm="Yakin hapus slot penugasan ini?"><?=csrf_input()?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?=h($r['id'])?>"><input type="hidden" name="bulan" value="<?=h($month)?>"><button class="btn small danger">Hapus</button></form></div></td></tr><?php endforeach;?><?php if(!$rows):?><tr><td colspan="7" class="empty">Belum ada jadwal. Gunakan tombol buat otomatis atau isi formulir.</td></tr><?php endif;?></tbody></table></div></div>
<div class="card" id="form"><h2><?= $selected?'Edit jadwal':'Tambah jadwal manual' ?></h2><p class="caption">Perubahan imam akan mengosongkan kembali konfirmasi kesediaan dan data absensi pada slot terkait.</p><form method="post" class="form-grid mt16"><?=csrf_input()?><input type="hidden" name="action" value="save"><input type="hidden" name="bulan" value="<?=h($month)?>"><input type="hidden" name="id" value="<?=h($selected['id']??'0')?>"><label>Tanggal<input required type="date" name="date" value="<?=h($selected['prayer_date']??$month.'-01')?>"></label><label>Waktu sholat<select name="prayer" required><?php foreach(prayers() as $p):?><option value="<?=h($p)?>" <?=($selected['prayer']??'')===$p?'selected':''?>><?=h($p)?></option><?php endforeach;?></select></label><label>Nama imam<select required name="imam_id"><option value="">Pilih imam</option><?php foreach($imams as $imam):?><option value="<?=h($imam['id'])?>" <?=(int)($selected['imam_id']??0)===(int)$imam['id']?'selected':''?>><?=h($imam['name'])?></option><?php endforeach;?></select></label><label>Jam sholat (WIB)<input required type="time" name="start_time" value="<?=h(substr($selected['start_time']??setting('time_Subuh','04:06'),0,5))?>"></label><label class="full check"><input type="checkbox" name="is_extra" value="1" <?=!empty($selected['is_extra'])?'checked':''?>>Tandai sebagai tugas tambahan/pengganti untuk perhitungan bisyaroh</label><div class="admin-form-actions"><button class="btn primary">Simpan jadwal</button><?php if($selected):?><a href="<?=h(link_to('jadwal.php',['bulan'=>$month]))?>#form" class="btn outline">Batal edit</a><?php endif;?></div></form></div>
<?php require __DIR__.'/partials/admin_footer.php'; ?>
