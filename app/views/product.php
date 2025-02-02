<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product List</title>
</head>
<body>
    <h1>Products</h1>
    <ul>
        <?php foreach ($products as $product): ?>
            <li><?= $product['name'] ?> - $<?= $product['price'] ?></li>
        <?php endforeach; ?>
    </ul>
    <a href="/">Back to Home</a>
</body>
</html>
