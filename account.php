<?php require_once __DIR__.'/includes/layout.php';require_once __DIR__.'/includes/totp.php';$u=require_login();
if($_SERVER['REQUEST_METHOD']==='POST'){
 check_post();$action=request_string('action',20);
 if($action==='start_mfa' && !$u['mfa_secret']) {$_SESSION['pending_mfa']=base32_random();redirect('account.php');}
 if($action==='enable_mfa' && !$u['mfa_secret'] && !empty($_SESSION['pending_mfa'])){
  if(totp_verify($_SESSION['pending_mfa'],request_string('code',6))){query('UPDATE shopusers SET mfa_secret=? WHERE id=?',[$_SESSION['pending_mfa'],$u['id']]);unset($_SESSION['pending_mfa']);flash('success','Authenticator MFA enabled.');}else flash('error','Incorrect code. MFA not enabled.');redirect('account.php');
 }
 if($action==='disable_mfa' && $u['mfa_secret']){
  if(!totp_verify($u['mfa_secret'],request_string('code',6))){flash('error','Incorrect code.');redirect('account.php');}
  query('UPDATE shopusers SET mfa_secret=NULL WHERE id=?',[$u['id']]);flash('success','Authenticator MFA disabled.');redirect('account.php');
 }
}
page_header('My account');?>
<div class="panel">
    <h2><?=e($u['name'])?>
    </h2>
    <p><?=e($u['email'])?>
    </p>
    <p class="muted">Role: <?=e($u['role'])?>
    </p>
</div>
<div class="panel">
    <h2>Two-factor authentication</h2>
    <?php if($u['mfa_secret']): ?>
    <p>Authenticator-based MFA is <strong>enabled</strong>.</p>
    <form method="post"><?=csrf_field()?>
        <input type="hidden" name="action" value="disable_mfa">
        <label>Code to disable MFA</label>
        <input name="code" required pattern="[0-9]{6}" inputmode="numeric">
        <div class="form-actions">
            <button class="btn danger">Disable MFA</button>
        </div>
    </form>
    <?php elseif(!empty($_SESSION['pending_mfa'])): ?>
    <p>Add a new TOTP account in Google Authenticator, Microsoft Authenticator or another compatible app. Enter this secret manually (store it securely):</p>
    <p>
        <code><?=e($_SESSION['pending_mfa'])?>
    </code>
</p>
<p class="small muted">Issuer: CloudCart · Account: <?=e($u['email'])?>
</p>
<form method="post"><?=csrf_field()?>
    <input type="hidden" name="action" value="enable_mfa">
    <label>Enter current 6-digit code to activate</label>
    <input name="code" inputmode="numeric" pattern="[0-9]{6}" required>
    <div class="form-actions">
        <button class="btn">Activate MFA</button>
    </div>
</form>
<?php else: ?>
    <p>Protect sign-ins with a one-time code from an authenticator app.</p>
    <form method="post"><?=csrf_field()?>
        <input type="hidden" name="action" value="start_mfa">
        <button class="btn">Set up MFA</button>
    </form>
<?php endif; ?>
</div>
<?php page_footer(); ?>
