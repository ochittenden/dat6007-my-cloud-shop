<?php

require_once __DIR__ . '/includes/bootstrap.php';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    check_post();

    $name = trim((string) ($_POST['name'] ?? ''));
    $email = strtolower(trim((string) ($_POST['email'] ?? '')));
    $password = (string) ($_POST['password'] ?? '');
    $confirm = (string) ($_POST['confirm'] ?? '');

    // Basic validation
    if (mb_strlen($name) >= 2 && valid_email($email) && strlen($password) >= 12 && strlen($password) <= 200 && $password === $confirm) {

        try {

            // Check whether the email is already registered
            $existing_user = one('SELECT id FROM shopusers WHERE email = ?', array($email));

            if ($existing_user) {

                header("Location: register.php?error=" . urlencode("Email already registered"));
                exit();
            }

            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insert into database
            $sql = "INSERT INTO shopusers (name, email, password) VALUES (?, ?, ?)";
            $params = array($name, $email, $hashed_password);

            query($sql, $params);

            // Redirect to success page
            header("Location: success.php");
            exit();

        } catch (Throwable $error) {

            error_log('Registration failed: ' . $error->getMessage());
            header("Location: register.php?error=" . urlencode("Registration failed. Please try again."));
            exit();
        }

    } else {

        header("Location: register.php?error=" . urlencode("Please check all fields. Passwords must match and contain at least 12 characters."));
        exit();
    }

} else {

    // Prevent direct access
    header("Location: register.php");
    exit();
}

?>
