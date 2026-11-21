<?php
require __DIR__.'/config.php';$u=imam_only();$imamId=request_imam_id();$id=(int)(queryv('id','0'));
if($_SERVER['REQUEST_METHOD']==='POST'){
 verify_post();$id=(int)post('id');$answer=post('answer');$note=post('note');
 if(!in_array($answer,['bisa','tidak_bisa'],true)){notice('Pilihan konfirmasi tidak valid.','danger');go('imam_home.php');}
 $q=db()->prepare("UPDATE schedules SET confirmation=?,confirmation_at=NOW(),confirmation_note=? WHERE id=? AND imam_id=?");
 $q->execute([$answer,mb_substr($note,0,255),$id,$imamId]);
 if($q->rowCount()){audit('konfirmasi_jadwal','Jadwal #'.$id.' · '.$answer);notice($answer==='bisa'?'Kesediaan Anda berhasil dikonfirmasi.':'Ketidaksediaan sudah dilaporkan kepada pengurus.');}
 else notice('Jadwal tidak ditemukan atau bukan penugasan Anda.','danger');
 go('imam_konfirmasi.php',['id'=>$id]);
}
if(!$id){$q=db()->prepare("SELECT id FROM schedules WHERE imam_id=? AND confirmation='pending' AND prayer_date>=? ORDER BY prayer_date LIMIT 1");$q->execute([$imamId,date('Y-m-d')]);$id=(int)$q->fetchColumn();}
$s=null;if($id){$q=db()->prepare('SELECT * FROM schedules WHERE id=? AND imam_id=?');$q->execute([$id,$imamId]);$s=$q->fetch();}
$pageTitle='Konfirmasi jadwal';$active='jadwal';require __DIR__.'/partials/mobile_header.php';
?><h1>Konfirmasi jadwal</h1><p class="caption"><?=h(user()['name'])?></p>
<?php if(!$s):?><div class="phone-card"><h3>Tidak ada jadwal untuk dikonfirmasi</h3><a class="phone-cta" href="<?=h(link_to('imam_jadwal.php'))?>">Kembali ke jadwal</a></div><?php else:?>
<div class="phone-card mt16"><?php if($s['confirmation']==='pending'):?><span class="tag">Menunggu konfirmasi</span><?php else:?><?=pill('Jawaban: '.status_confirmation($s['confirmation']),$s['confirmation']==='tidak_bisa'?'bad':'good')?><?php endif;?><h2 style="margin-top:14px"><?=h($s['prayer'])?></h2><p class="caption" style="margin-top:6px"><?=h(pretty_date($s['prayer_date']))?><br><?=h(substr($s['start_time'],0,5))?> WIB · <?=h(setting('masjid_name'))?></p><div class="divider"></div><h3>Bisa mengisi <?=h($s['prayer'])?>?</h3><p>Jawaban akan dicatat agar pengurus mengetahui kesediaan Anda sebelum jadwal.</p><form method="post"><?=csrf_input()?><input type="hidden" name="id" value="<?=h($s['id'])?>"><label>Catatan (opsional)<textarea name="note" rows="2" placeholder="Contoh: perlu pengganti karena ada acara"><?=h($s['confirmation_note'])?></textarea></label><div class="mt16"><button type="submit" name="answer" value="bisa" class="btn primary block">Ya, saya bisa</button><button type="submit" name="answer" value="tidak_bisa" class="btn outline block mt8">Tidak bisa</button><a class="phone-cta mt8" href="<?=h(link_to('imam_jadwal.php',['tanggal'=>$s['prayer_date']]))?>">Kembali ke jadwal</a></div></form></div>
<div class="phone-card soft"><h3>Dampak pada bisyaroh</h3><p><b>Bersedia:</b> jadwal tetap tercatat. Bila ditandai tugas ekstra dan disanggupi, ada tambahan <?=h(money(setting('extra_per_schedule','0')))?> per tugas.</p><p><b>Tidak bisa:</b> pengurus mendapat informasi kesediaan dan estimasi potongan <?=h(money(setting('deduction_per_unavailable','0')))?> sesuai aturan masjid.</p><p>Konfirmasi kesediaan tidak sama dengan absensi. Pengurus mencatat kehadiran aktual secara terpisah.</p></div><?php endif;?>
<?php require __DIR__.'/partials/mobile_footer.php'; ?>
