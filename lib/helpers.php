<?php
declare(strict_types=1);
function h(mixed $v): string { return htmlspecialchars((string)($v ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function link_to(string $page='', array $params=[]): string { return BASE_PATH.'/'.ltrim($page,'/').($params?'?'.http_build_query($params):''); }
function go(string $page, array $params=[]): never { header('Location: '.link_to($page,$params), true, 303); exit; }
function post(string $key): string { return trim(is_string($_POST[$key]??null)?$_POST[$key]:''); }
function queryv(string $key, string $fallback=''): string { return is_string($_GET[$key]??null)?trim($_GET[$key]):$fallback; }
function csrf(): string { if(empty($_SESSION['csrf']))$_SESSION['csrf']=bin2hex(random_bytes(32)); return $_SESSION['csrf']; }
function csrf_input(): string { return '<input type="hidden" name="csrf" value="'.h(csrf()).'">'; }
function verify_post(): void { if($_SERVER['REQUEST_METHOD']!=='POST'||!hash_equals(csrf(),is_string($_POST['csrf']??null)?$_POST['csrf']:'')){http_response_code(419);exit('Sesi formulir tidak valid. Refresh dan ulangi.');} }
function notice(string $message, string $kind='success'): void { $_SESSION['notice']=['message'=>$message,'kind'=>$kind]; }
function display_notice(): void { if(!empty($_SESSION['notice'])) { $x=$_SESSION['notice'];unset($_SESSION['notice']);echo '<div class="notice '.h($x['kind']).'">'.h($x['message']).'</div>'; } }
function valid_date(string $s): bool { $d=DateTimeImmutable::createFromFormat('!Y-m-d',$s);return $d!==false && $d->format('Y-m-d')===$s; }
function valid_month(string $s): bool { return (bool)preg_match('/^\d{4}-(0[1-9]|1[0-2])$/',$s); }
function month_selected(): string { $v=queryv('bulan',date('Y-m'));return valid_month($v)?$v:date('Y-m'); }
function month_label(string $month): string { $m=[1=>'Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];return $m[(int)substr($month,5,2)].' '.substr($month,0,4); }
function weekday(string $date): string { return ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'][(int)date('w',strtotime($date))]; }
function pretty_date(string $date, bool $weekday=true): string { return ($weekday?weekday($date).', ':'').(int)substr($date,8,2).' '.month_label(substr($date,0,7)); }
function money(float|int|string $v): string { return 'Rp'.number_format((float)$v,0,',','.'); }
function prayers(): array { return ['Subuh','Dzuhur','Ashar','Maghrib','Isya']; }
function time_default(): array { return ['Subuh'=>'04:06','Dzuhur'=>'11:41','Ashar'=>'15:02','Maghrib'=>'17:53','Isya'=>'19:06']; }
function setting(string $key, string $fallback=''): string { static $values=null; if($values===null){$values=db()->query('SELECT `key`,`value` FROM settings')->fetchAll(PDO::FETCH_KEY_PAIR);}return (string)($values[$key]??$fallback); }
function month_bounds(string $month): array { return [$month.'-01',date('Y-m-t',strtotime($month.'-01'))]; }
function shift_month(string $month,int $n): string { return date('Y-m',strtotime($month.'-01 '.($n>=0?'+':'').$n.' month')); }
function pill(string $label,string $tone='neutral'): string { return '<span class="pill '.h($tone).'">'.h($label).'</span>'; }
function status_attendance(string $s): string { return match($s){'hadir'=>'Hadir','izin'=>'Izin','tidak_hadir'=>'Tidak hadir',default=>'Belum diisi'}; }
function status_confirmation(string $s): string { return match($s){'bisa'=>'Bisa','tidak_bisa'=>'Tidak bisa',default=>'Belum konfirmasi'}; }
function csvsafe(mixed $v): string { $x=(string)($v??'');return preg_match('/^\s*[=+\-@\t\r]/u',$x)?"'".$x:$x; }
function audit(string $action, string $detail=''): void { $q=db()->prepare('INSERT INTO audit_logs (user_id,action,detail) VALUES (?,?,?)');$q->execute([(int)(user()['id']??0) ?: null,$action,$detail]); }
