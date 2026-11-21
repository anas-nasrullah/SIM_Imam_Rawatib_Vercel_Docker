<?php
declare(strict_types=1);
function user(): ?array {
    static $cache = false;
    if($cache!==false)return $cache;
    $id=(int)($_SESSION['uid']??0);
    if($id<1)return $cache=null;
    $q=db()->prepare('SELECT * FROM users WHERE id=? AND active=1');$q->execute([$id]);
    $r=$q->fetch(); if(!$r){unset($_SESSION['uid']);return $cache=null;} return $cache=$r;
}
function logged(): array { $u=user();if(!$u)go('login.php');return $u; }
function manager(): bool { return in_array(user()['role']??'', ['admin','pengurus'],true); }
function manager_only(): array { $u=logged(); if(!manager()){http_response_code(403);exit('403: Hanya pengurus atau administrator.');}return $u; }
function admin_only(): array { $u=logged();if($u['role']!=='admin'){http_response_code(403);exit('403: Khusus administrator.');}return $u; }
function imam_only(): array { $u=logged();if($u['role']!=='imam'){go('dashboard.php');}return $u; }
function login_destination(): string {return manager()?'dashboard.php':'imam_home.php';}
