<?php

require_once __DIR__ . '/includes/layout.php';

// Process login form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    check_post();

    $email = strtolower(trim((string) ($_POST['email'] ?? '')));
    $password = (string) ($_POST['password'] ?? '');

    // Check email and password
    $user = valid_email($email) ? one('SELECT id, password, mfa_secret FROM shopusers WHERE email = ?', array($email)) : null;

    if ($user && is_string($user['password']) && password_verify($password, $user['password'])) {

        session_regenerate_id(true);
        $_SESSION['uid'] = (int) $user['id'];
        $_SESSION['mfa_ok'] = empty($user['mfa_secret']);

        if (!$_SESSION['mfa_ok']) {
            redirect('mfa.php');
        }

        log_event('login', (int) $user['id']);
        redirect('index.php');

    } else {

        flash('error', 'Invalid email or password.');
        redirect('login.php');
    }
}

page_header('Login');

?>
<div class="auth-card">
    <h2>Sign In</h2>

    <form method="post" action="login.php">
        <?php echo csrf_field(); ?>

        <div class="form-group">
            <label>Email *</label>
            <input type="email" name="email" placeholder="Enter your email" required>
        </div>

        <div class="form-group">
            <label>Password *</label>
            <input type="password" name="password" placeholder="Enter your password" required>
        </div>

        <div class="form-group">
            <button type="submit" class="btn">Login</button>
        </div>
    </form>

    <?php if (envv('GOOGLE_CLIENT_ID') !== '' && envv('GOOGLE_CLIENT_SECRET') !== ''): ?>
        <p><a href="oauth_google.php">Sign in with Google</a></p>
    <?php endif; ?>

    <p>Don't have an account? <a href="register.php">Register</a></p>
</div>

<?php page_footer(); ?>
