<?php
require_once __DIR__.'/includes/bootstrap.php';
$client=envv('GOOGLE_CLIENT_ID');$secret=envv('GOOGLE_CLIENT_SECRET');$redirectUri=envv('GOOGLE_REDIRECT_URI');
if(!$client || !$secret || !$redirectUri){http_response_code(503);exit('Google sign-in is not configured.');}
function google_json(string $url,?array $post=null,?string $bearer=null): array {
 if(!function_exists('curl_init'))throw new RuntimeException('PHP cURL extension required');
 $ch=curl_init($url);$headers=['Accept: application/json'];if($bearer!==null)$headers[]='Authorization: Bearer '.$bearer;
 $opts=[CURLOPT_RETURNTRANSFER=>true,CURLOPT_TIMEOUT=>10,CURLOPT_SSL_VERIFYPEER=>true,CURLOPT_HTTPHEADER=>$headers];
 if($post!==null){$opts[CURLOPT_POST]=true;$opts[CURLOPT_POSTFIELDS]=http_build_query($post);}
 curl_setopt_array($ch,$opts);$body=curl_exec($ch);$status=curl_getinfo($ch,CURLINFO_HTTP_CODE);curl_close($ch);
 if(!is_string($body)||$status<200||$status>=300)throw new RuntimeException('OAuth provider request failed');
 $decoded=json_decode($body,true);if(!is_array($decoded))throw new RuntimeException('Invalid OAuth response');return $decoded;
}
if(isset($_GET['error'])){flash('error','Google sign-in was cancelled.');redirect('login.php');}
if(!isset($_GET['code'])){
 $_SESSION['oauth_state']=bin2hex(random_bytes(24));
 $url='https://accounts.google.com/o/oauth2/v2/auth?'.http_build_query(['client_id'=>$client,'redirect_uri'=>$redirectUri,'response_type'=>'code','scope'=>'openid email profile','state'=>$_SESSION['oauth_state'],'access_type'=>'online','prompt'=>'select_account']);
 header('Location: '.$url);exit;
}
$state=(string)($_GET['state']??'');$expected=$_SESSION['oauth_state']??'';unset($_SESSION['oauth_state']);
if(!$expected || !hash_equals($expected,$state)){http_response_code(400);exit('Invalid OAuth state.');}
try{
 $tokens=google_json('https://oauth2.googleapis.com/token',['code'=>(string)$_GET['code'],'client_id'=>$client,'client_secret'=>$secret,'redirect_uri'=>$redirectUri,'grant_type'=>'authorization_code']);
 if(empty($tokens['access_token']))throw new RuntimeException('Missing token');
 $info=google_json('https://openidconnect.googleapis.com/v1/userinfo',null,(string)$tokens['access_token']);
 if(($info['email_verified']??false)!==true || empty($info['sub']) || !valid_email((string)($info['email']??'')))throw new RuntimeException('Unverified Google identity');
 $subject=(string)$info['sub'];$email=strtolower((string)$info['email']);$name=mb_substr((string)($info['name']??'Google user'),0,120);
 $user=one('SELECT id,mfa_secret FROM shopusers WHERE oauth_provider=? AND oauth_subject=?',['google',$subject]);
 if(!$user){
  // Avoid automatically merging a Google identity into a password account.
  if(one('SELECT id FROM shopusers WHERE email=?',[$email])){flash('error','An account with this email exists. Sign in with your password.');redirect('login.php');}
  query('INSERT INTO shopusers(name,email,oauth_provider,oauth_subject) VALUES(?,?,?,?)',[$name,$email,'google',$subject]);
  $user=one('SELECT id,mfa_secret FROM shopusers WHERE oauth_provider=? AND oauth_subject=?',['google',$subject]);
 }
 session_regenerate_id(true);$_SESSION['uid']=(int)$user['id'];$_SESSION['mfa_ok']=empty($user['mfa_secret']);
 redirect($_SESSION['mfa_ok']?'index.php':'mfa.php');
}catch(Throwable $e){error_log('OAuth failure: '.$e->getMessage());flash('error','Google sign-in failed.');redirect('login.php');}
