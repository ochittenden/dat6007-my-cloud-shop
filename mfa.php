<?php require_once __DIR__.'/includes/layout.php';require_once __DIR__.'/includes/totp.php';
if(empty($_SESSION['uid']) || !empty($_SESSION['mfa_ok']))redirect('login.php');
$user=one('SELECT id,mfa_secret FROM shopusers WHERE id=?',[(int)$_SESSION['uid']]);
if(!$user || !$user['mfa_secret'])redirect('login.php');
if($_SERVER['REQUEST_METHOD']==='POST'){check_post();if(totp_verify($user['mfa_secret'],request_string('code',6))){$_SESSION['mfa_ok']=true;session_regenerate_id(true);redirect('index.php');}flash('error','Invalid authenticator code.');redirect('mfa.php');}
page_header('Two-factor verification');?>
<div class="auth-card">
    <p>Enter the 6-digit code from your authenticator app.</p>
    <form method="post"><?=csrf_field()?>
        <label for="code">Authenticator code</label>
        <input id="code" name="code" inputmode="numeric" pattern="[0-9]{6}" autocomplete="one-time-code" maxlength="6" required>
        <div class="form-actions">
            <button class="btn">Verify</button>
        </div>
    </form>
</div>
<?php page_footer(); ?>
