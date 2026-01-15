<?php
include "db.php";

$products = [];
try {
    $stmt = $conn->prepare("SELECT products.*, users.username FROM products JOIN users ON products.user_id = users.user_id ORDER BY created_at DESC");
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
} catch (Exception $e) {
    error_log("Error fetching products: " . $e->getMessage());
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products - e-Lakbay</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<header>
    <nav class="navbar">
        <div class="logo">e-Lakbay</div>
        <ul class="nav-links">
            <li><a href="index.php">Home</a></li>
            <li><a href="products.php">Products</a></li>
        </ul>
    </nav>
</header>

<main>
    <section class="products-grid">
        <h1>Local Products</h1>
        <?php if (empty($products)): ?>
            <p>No products available.</p>
        <?php else: ?>
            <?php foreach ($products as $product): ?>
                <div class="product-card">
                    <img src="uploads/<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['product_name']); ?>">
                    <h3><?php echo htmlspecialchars($product['product_name']); ?></h3>
                    <p><?php echo htmlspecialchars($product['description']); ?></p>
                    <p>Price: ₱<?php echo number_format($product['price'], 2); ?> | Town: <?php echo htmlspecialchars($product['town']); ?></p>
                    <p>By: <?php echo htmlspecialchars($product['username']); ?></p>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </section>
</main>
</body>
</html>