<?php

require_once __DIR__ . '/includes/layout.php';

$search = trim((string) ($_GET['q'] ?? ''));
$category = trim((string) ($_GET['category'] ?? ''));
$search = mb_substr($search, 0, 100);
$category = mb_substr($category, 0, 80);

$filters = array('active = 1');
$params = array();

if ($search !== '') {
    $filters[] = '(name LIKE ? OR description LIKE ?)';
    $params[] = '%' . $search . '%';
    $params[] = '%' . $search . '%';
}

if ($category !== '') {
    $filters[] = 'category = ?';
    $params[] = $category;
}

$products = rows('SELECT id, name, description, category, price, stock, image_url FROM products WHERE ' . implode(' AND ', $filters) . ' ORDER BY id DESC', $params);
$categories = rows('SELECT DISTINCT category FROM products WHERE active = 1 ORDER BY category');

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MyShop - Welcome</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }

        .header {
            background-color: #2c3e50;
            color: white;
            padding: 20px 0;
            text-align: center;
            margin-bottom: 30px;
        }

        .header h1 {
            margin: 0;
            font-size: 2.5em;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .welcome-text {
            text-align: center;
            font-size: 1.2em;
            color: #555;
            margin-top: 50px;
        }

        .signup-link {
            display: inline-block;
            background-color: #007bff;
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 5px;
            font-size: 18px;
            margin-top: 20px;
        }

        .signup-link:hover {
            background-color: #0056b3;
        }

        .navigation {
            text-align: center;
            margin-top: 20px;
        }

        .navigation a {
            color: #007bff;
            margin: 0 10px;
        }

        .product-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }

        .product-item {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        .product-item img {
            width: 100%;
            height: 160px;
            object-fit: cover;
        }

        .product-item .btn {
            width: 100%;
        }

        .search-form {
            text-align: center;
            margin-top: 20px;
        }

        .btn {
            display: inline-block;
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .btn {
            display: inline-block;
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .search-form input,
        .search-form select {
            padding: 10px;
            margin: 5px;
        }
    </style>
</head>
<body>

<div class="header">
    <div class="container">
        <h1>MyShop</h1>
        <p>Your one-stop destination for amazing products</p>
    </div>
</div>

<div class="container">
    <div class="welcome-text">
        <h2>Welcome to MyShop</h2>
        <p>Join our community today to access exclusive deals and features!</p>

        <a href="register.php" class="signup-link">
            Create Your Account
        </a>

        <?php navigation_links(); ?>
    </div>

    <h2 id="products">Our Products</h2>

    <form method="get" class="search-form">
        <input type="text" name="q" placeholder="Search products" value="<?php echo e($search); ?>">

        <select name="category">
            <option value="">All Categories</option>
            <?php foreach ($categories as $item): ?>
                <option value="<?php echo e($item['category']); ?>" <?php echo $category === $item['category'] ? 'selected' : ''; ?>>
                    <?php echo e($item['category']); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button type="submit" class="btn">Search</button>
    </form>

    <div class="product-list">
        <?php foreach ($products as $product): ?>
            <?php product_card($product); ?>
        <?php endforeach; ?>
    </div>

    <?php if (empty($products)): ?>
        <p>No products found.</p>
    <?php endif; ?>
</div>

</body>
</html>
