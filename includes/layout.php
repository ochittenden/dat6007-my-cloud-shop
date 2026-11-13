<?php

require_once __DIR__ . '/bootstrap.php';

// Show the navigation on the home page and other pages
function navigation_links() {

    $user = current_user();

    ?>
    <div class="navigation">
        <a href="index.php">Home</a>
        <a href="cart.php">Shopping Cart</a>

        <?php if ($user): ?>
            <a href="wishlist.php">Wishlist</a>
            <a href="orders.php">My Orders</a>
            <a href="account.php">My Account</a>

            <?php if ($user['role'] == 'admin'): ?>
                <a href="admin.php">Admin</a>
            <?php endif; ?>

            <form method="post" action="logout.php" class="inline">
                <?php echo csrf_field(); ?>
                <button type="submit" class="link-button">Logout</button>
            </form>
        <?php else: ?>
            <a href="login.php">Login</a>
            <a href="register.php">Register</a>
        <?php endif; ?>
    </div>
    <?php
}

// Common header for the additional pages
function page_header($title) {

    $messages = $_SESSION['flash'] ?? array();
    unset($_SESSION['flash']);

    ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($title); ?> - MyShop</title>

    <link rel="stylesheet" href="assets/style.css">
</head>
<body>

<div class="header">
    <div class="container">
        <h1>MyShop</h1>
        <p>Your one-stop destination for amazing products</p>
    </div>
</div>

<div class="container">
    <?php navigation_links(); ?>

    <h2><?php echo e($title); ?></h2>

    <?php foreach ($messages as $message): ?>
        <div class="error"><?php echo e($message['message']); ?></div>
    <?php endforeach; ?>
    <?php
}

// Common closing tags
function page_footer() {

    ?>
</div>

</body>
</html>
    <?php
}

// Display a product using the same card and button style as registration
function product_card($product) {

    ?>
    <div class="product-item">
        <?php if (!empty($product['image_url'])): ?>
            <img src="<?php echo e($product['image_url']); ?>" alt="<?php echo e($product['name']); ?>">
        <?php endif; ?>

        <h3><a href="product.php?id=<?php echo (int) $product['id']; ?>"><?php echo e($product['name']); ?></a></h3>
        <p><?php echo e($product['category']); ?></p>
        <p><?php echo e(mb_strimwidth($product['description'], 0, 100, '...')); ?></p>
        <p><strong><?php echo money($product['price']); ?></strong></p>
        <p><?php echo (int) $product['stock'] > 0 ? 'In stock' : 'Out of stock'; ?></p>

        <form method="post" action="cart.php">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="action" value="add">
            <input type="hidden" name="id" value="<?php echo (int) $product['id']; ?>">
            <button type="submit" class="btn" <?php echo (int) $product['stock'] < 1 ? 'disabled' : ''; ?>>Add to Cart</button>
        </form>
    </div>
    <?php
}

?>
