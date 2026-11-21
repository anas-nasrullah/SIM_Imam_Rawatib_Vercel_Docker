<?php
declare(strict_types=1);
function get_month_schedules(string $month,?int $imamId=null): array {
    [$first,$last]=month_bounds($month);
    $sql='SELECT s.*,i.name imam_name FROM schedules s JOIN imams i ON i.id=s.imam_id WHERE s.prayer_date BETWEEN ? AND ?';
    $params=[$first,$last];
    if($imamId!==null){$sql.=' AND s.imam_id=?';$params[]=$imamId;}
    $sql.=" ORDER BY s.prayer_date,FIELD(s.prayer,'Subuh','Dzuhur','Ashar','Maghrib','Isya')";
    $q=db()->prepare($sql);$q->execute($params);return $q->fetchAll();
}
function all_imams(bool $onlyActive=false): array {return db()->query('SELECT * FROM imams'.($onlyActive?' WHERE active=1':'').' ORDER BY name')->fetchAll();}
function earnings(string $month,int $imamId): array {
    [$first,$last]=month_bounds($month);
    $q=db()->prepare("SELECT COUNT(*) total, SUM(confirmation='bisa') confirmed, SUM(confirmation='tidak_bisa') unavailable, SUM(attendance='hadir') hadir, SUM(is_extra=1 AND confirmation='bisa') extra FROM schedules WHERE imam_id=? AND prayer_date BETWEEN ? AND ?");
    $q->execute([$imamId,$first,$last]);$row=$q->fetch()?:[];
    $extra=(int)($row['extra']??0);
    // Kesediaan 'tidak bisa' harus tetap tercatat jika pengurus menunjuk pengganti.
    $q=db()->prepare("SELECT COUNT(*) FROM schedule_refusals WHERE imam_id=? AND prayer_date BETWEEN ? AND ?");
    $q->execute([$imamId,$first,$last]);$history=(int)$q->fetchColumn();
    $q=db()->prepare("SELECT COUNT(*) FROM schedules s WHERE imam_id=? AND prayer_date BETWEEN ? AND ? AND confirmation='tidak_bisa' AND NOT EXISTS (SELECT 1 FROM schedule_refusals r WHERE r.schedule_id=s.id AND r.imam_id=s.imam_id)");
    $q->execute([$imamId,$first,$last]);$unavailable=$history+(int)$q->fetchColumn();
    $adj=db()->prepare("SELECT COALESCE(SUM(CASE WHEN kind='tambahan' THEN amount ELSE 0 END),0) tambahan,COALESCE(SUM(CASE WHEN kind='potongan' THEN amount ELSE 0 END),0) potongan FROM honor_adjustments WHERE imam_id=? AND month=?");
    $adj->execute([$imamId,$first]);$a=$adj->fetch()?:[];
    $pay=db()->prepare('SELECT COALESCE(SUM(amount),0) paid FROM honor_payments WHERE imam_id=? AND month=?');$pay->execute([$imamId,$first]);$paid=(int)$pay->fetchColumn();
    $base=(int)setting('honor_base','1000000');
    $bonus=$extra*(int)setting('extra_per_schedule','0')+(int)($a['tambahan']??0);
    $deduction=$unavailable*(int)setting('deduction_per_unavailable','0')+(int)($a['potongan']??0);
    $net=max(0,$base+$bonus-$deduction);
    return ['total'=>(int)($row['total']??0),'confirmed'=>(int)($row['confirmed']??0),'unavailable'=>$unavailable,'hadir'=>(int)($row['hadir']??0),'extra'=>$extra,'base'=>$base,'bonus'=>$bonus,'deduction'=>$deduction,'net'=>$net,'paid'=>$paid,'balance'=>max(0,$net-$paid)];
}
function request_imam_id(): int {return (int)(user()['imam_id']??0);}
function guard_month(): string {return month_selected();}
