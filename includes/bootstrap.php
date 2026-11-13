<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_name('cloudcart_session');
    session_set_cookie_params(['lifetime'=>0,'path'=>'/','secure'=>!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off','httponly'=>true,'samesite'=>'Lax']);
    session_start();
}
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Content-Security-Policy: default-src \'self\'; img-src \'self\' data: https://images.unsplash.com; style-src \'self\' \'unsafe-inline\'; script-src \'self\'; base-uri \'self\'; form-action \'self\'');
header('Cache-Control: private, no-store');

function envv(string $key, string $default = ''): string { $v=getenv($key); return $v===false ? $default : $v; }
function db() {
    static $conn=null;
    if ($conn!==null) return $conn;
    if (!function_exists('sqlsrv_connect')) throw new RuntimeException('Microsoft sqlsrv PHP extension is not installed.');
    $host=envv('DB_HOST'); $name=envv('DB_NAME'); $user=envv('DB_USER'); $password=envv('DB_PASSWORD');
    if ($host==='' || $name==='' || $user==='' || $password==='') throw new RuntimeException('Database environment variables are not configured.');
    $conn=sqlsrv_connect($host,['Database'=>$name,'UID'=>$user,'PWD'=>$password,'Encrypt'=>true,'TrustServerCertificate'=>false,'CharacterSet'=>'UTF-8','ReturnDatesAsStrings'=>true]);
    if (!$conn) { error_log('CloudCart DB connection failed: '.json_encode(sqlsrv_errors())); throw new RuntimeException('Unable to connect to the database.'); }
    return $conn;
}
function query(string $sql, array $params=[]) {
    $stmt=sqlsrv_query(db(),$sql,$params);
    if ($stmt===false) { error_log('CloudCart SQL error: '.json_encode(sqlsrv_errors())); throw new RuntimeException('Database operation failed.'); }
    return $stmt;
}
function rows(string $sql, array $params=[]): array {
    $s=query($sql,$params); $out=[];
    while (is_array($r=sqlsrv_fetch_array($s,SQLSRV_FETCH_ASSOC))) $out[]=$r;
    if ($r===false) throw new RuntimeException('Database fetch failed.');
    sqlsrv_free_stmt($s); return $out;
}
function one(string $sql, array $params=[]): ?array { $r=rows($sql,$params); return $r[0]??null; }
function e($value): string { return htmlspecialchars((string)$value,ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8'); }
function money($p): string { return '£'.number_format((float)$p,2); }
function csrf_token(): string { return $_SESSION['csrf'] ??= bin2hex(random_bytes(32)); }
function csrf_field(): string { return '<input type="hidden" name="csrf" value="'.e(csrf_token()).'">'; }
function check_post(): void {
    if ($_SERVER['REQUEST_METHOD']!=='POST') { http_response_code(405); exit('Method not allowed'); }
    if (!hash_equals(csrf_token(),(string)($_POST['csrf']??''))) { http_response_code(419); exit('Invalid CSRF token. Refresh the page and try again.'); }
}
function flash(string $type,string $message): void { $_SESSION['flash'][]=['type'=>$type,'message'=>$message]; }
function redirect(string $page): never { header('Location: '.$page, true, 303); exit; }
function current_user(): ?array {
    if (empty($_SESSION['uid']) || empty($_SESSION['mfa_ok'])) return null;
    return one('SELECT id,name,email,role,mfa_secret FROM shopusers WHERE id=?',[(int)$_SESSION['uid']]);
}
function require_login(): array { $user=current_user(); if (!$user) redirect('login.php'); return $user; }
function require_admin(): array { $user=require_login(); if ($user['role']!=='admin') {http_response_code(403); exit('Forbidden');} return $user; }
function user_id(): int { return (int)(current_user()['id']??0); }
function valid_email(string $email): bool { return strlen($email)<=254 && filter_var($email,FILTER_VALIDATE_EMAIL)!==false; }
function request_string(string $key, int $max=255): string { return mb_substr(trim((string)($_POST[$key]??'')),0,$max); }
function check_amount(string $value): ?string { return preg_match('/^(0|[1-9]\d{0,6})(\.\d{1,2})?$/',$value) && (float)$value>0 ? number_format((float)$value,2,'.','') : null; }
function log_event(string $type, ?int $userId=null): void { error_log('CloudCart event='.preg_replace('/[^a-z_]/','',$type).' user_id='.(string)($userId??0)); }
