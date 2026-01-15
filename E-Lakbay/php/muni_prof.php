<?php
// Include database connection
include "db.php";

// Get the town parameter from the URL
$town = isset($_GET['town']) ? $_GET['town'] : null;

// Map URL-friendly town names to actual town names (adjust as needed based on your database)
$town_mapping = [
    'sta_cruz' => 'Sta. Cruz',
    'sta_maria' => 'Sta. Maria',
    'candon' => 'Candon',
    'gregorio_del_pilar' => 'Gregorio Del Pilar',
    'alilem' => 'Alilem',
    'salcedo' => 'Salcedo',
    'san_emilio' => 'San Emilio',
    'lidlidda' => 'Lidlidda'
];

// Get the actual town name
$actual_town = isset($town_mapping[$town]) ? $town_mapping[$town] : null;

if (!$actual_town) {
    // Handle invalid or missing town
    echo "<!DOCTYPE html><html><head><title>Error</title></head><body><h1>Invalid Municipality</h1><p>The requested municipality does not exist.</p><a href='index.php'>Go back to Home</a></body></html>";
    exit;
}

// Fetch tourist spots for the town
$spots = [];
try {
    $stmt = $conn->prepare("SELECT tourist_spots.*, users.username FROM tourist_spots JOIN users ON tourist_spots.user_id = users.user_id WHERE tourist_spots.location = ? ORDER BY created_at DESC");
    $stmt->bind_param("s", $actual_town);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $spots[] = $row;
    }
    $stmt->close();
} catch (Exception $e) {
    error_log("Error fetching spots: " . $e->getMessage());
}

// Fetch products for the town
$products = [];
try {
    $stmt = $conn->prepare("SELECT products.*, users.username FROM products JOIN users ON products.user_id = users.user_id WHERE products.town = ? ORDER BY created_at DESC");
    $stmt->bind_param("s", $actual_town);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
    $stmt->close();
} catch (Exception $e) {
    error_log("Error fetching products: " . $e->getMessage());
}

// Fetch events for the town (new addition)
$events = [];
try {
    $stmt = $conn->prepare("SELECT events.*, users.username FROM events JOIN users ON events.user_id = users.user_id WHERE events.location = ? ORDER BY created_at DESC");
    $stmt->bind_param("s", $actual_town);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $events[] = $row;
    }
    $stmt->close();
} catch (Exception $e) {
    error_log("Error fetching events: " . $e->getMessage());
}

// Close database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($actual_town); ?> - e-Lakbay</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    <style>
        /* Basic styles for the profile sections */
        html {
            scroll-behavior: smooth;
        }
        .municipality-profile {
            padding: 50px 5%;
            text-align: center;
        }
        .section-title {
            font-size: 2rem;
            margin-bottom: 10px;
        }
        .section-sub {
            font-size: 1rem;
            color: #666;
            margin-bottom: 30px;
        }
        .grid-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }
        .grid-item {
            background: #ffffff;
            border-radius: 8px;
            padding: 15px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.15);
            transition: transform 0.3s ease;
        }
        .grid-item:hover {
            transform: scale(1.02);
        }
        .item-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
            font-size: 0.9rem;
        }
        .item-type {
            background: #0d458a;
            color: #ffffff;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 0.8rem;
        }
        .item-image {
            max-width: 100%;
            height: auto;
            border-radius: 6px;
            margin-top: 10px;
        }
        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            text-decoration: none;
            color: #0d458a;
            font-weight: bold;
        }
        .back-link:hover {
            text-decoration: underline;
        }
        @media (max-width: 768px) {
            .grid-container {
                grid-template-columns: 1fr;
            }
            .grid-item {
                padding: 10px;
            }
        }
    </style>
</head>
<body>
    <!-- NAVBAR -->
    <nav class="navbar">
        <div class="logo">e-Lakbay</div>
        <!-- HAMBURGER BUTTON -->
        <div class="hamburger" onclick="toggleMenu()">
            <span></span>
            <span></span>
            <span></span>
        </div>
        <ul class="nav-links" id="navLinks">
            <li><a href="index.php">Home</a></li>
            <li><a href="index.php#municipalities">Municipalities</a></li>
            <li><a href="products.php">Products</a></li>
            <li><a href="logIn.php">Log In</a></li>
            <li><a href="signIn.php">Sign In</a></li>
        </ul>
    </nav>

    <!-- MUNICIPALITY PROFILE -->
    <section class="municipality-profile">
        <a href="index.php#municipalities" class="back-link">&larr; Back to Municipalities</a>
        <h1 class="section-title"><?php echo htmlspecialchars($actual_town); ?></h1>
        <p class="section-sub">Explore the tourist spots, local products, and events of <?php echo htmlspecialchars($actual_town); ?>.</p>

        <!-- TOURIST SPOTS SECTION -->
        <h2 class="section-title">Tourist Spots</h2>
        <?php if (empty($spots)): ?>
            <p>No tourist spots available for this municipality yet.</p>
        <?php else: ?>
            <div class="grid-container">
                <?php foreach ($spots as $spot): ?>
                    <div class="grid-item">
                        <div class="item-header">
                            <strong><?php echo htmlspecialchars($spot['username']); ?></strong>
                            <span><?php echo date("M d, Y", strtotime($spot['created_at'])); ?></span>
                            <span class="item-type">Spot</span>
                        </div>
                        <h3><?php echo htmlspecialchars($spot['name']); ?></h3>
                        <p><?php echo htmlspecialchars($spot['description'] ?: 'No description.'); ?></p>
                        <p><strong>Location:</strong> <?php echo htmlspecialchars($spot['location']); ?></p>
                        <?php if ($spot['image']): ?>
                            <img src="uploads/<?php echo htmlspecialchars($spot['image']); ?>" alt="Spot Image" class="item-image">
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- PRODUCTS SECTION -->
        <h2 class="section-title">Local Products</h2>
        <?php if (empty($products)): ?>
            <p>No products available for this municipality yet.</p>
        <?php else: ?>
            <div class="grid-container">
                <?php foreach ($products as $product): ?>
                    <div class="grid-item">
                        <div class="item-header">
                            <strong><?php echo htmlspecialchars($product['username']); ?></strong>
                            <span><?php echo date("M d, Y", strtotime($product['created_at'])); ?></span>
                            <span class="item-type">Product</span>
                        </div>
                        <h3><?php echo htmlspecialchars($product['product_name']); ?></h3>
                        <p><?php echo htmlspecialchars($product['description'] ?: 'No description.'); ?></p>
                        <p><strong>Price:</strong> ₱<?php echo number_format($product['price'], 2); ?> | <strong>Town:</strong> <?php echo htmlspecialchars($product['town']); ?></p>
                        <?php if ($product['image']): ?>
                            <img src="uploads/<?php echo htmlspecialchars($product['image']); ?>" alt="Product Image" class="item-image">
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- EVENTS SECTION (New Addition) -->
        <h2 class="section-title">Events</h2>
        <?php if (empty($events)): ?>
            <p>No events available for this municipality yet.</p>
        <?php else: ?>
            <div class="grid-container">
                <?php foreach ($events as $event): ?>
                    <div class="grid-item">
                        <div class="item-header">
                            <strong><?php echo htmlspecialchars($event['username']); ?></strong>
                            <span><?php echo date("M d, Y", strtotime($event['created_at'])); ?></span>
                            <span class="item-type">Event</span>
                        </div>
                        <h3><?php echo htmlspecialchars($event['title']); ?></h3>
                        <p><?php echo htmlspecialchars($event['description'] ?: 'No description.'); ?></p>
                        <p><strong>Date:</strong> <?php echo htmlspecialchars($event['date']); ?> | <strong>Location:</strong> <?php echo htmlspecialchars($event['location']); ?></p>
                        <?php if ($event['image']): ?>
                            <img src="uploads/<?php echo htmlspecialchars($event['image']); ?>" alt="Event Image" class="item-image">
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

    <!-- JAVASCRIPT FOR BURGER MENU -->
    <script>
        function toggleMenu() {
            document.getElementById("navLinks").classList.toggle("active");
            document.querySelector(".hamburger").classList.toggle("active");
        }
    </script>

    <!-- FOOTER -->
    <footer class="footer">
        <section class="about">
            <h2 class="section-title">About Us</h2>
            <p class="about-text">
                “Discover the 2nd District of Ilocos Sur, where rich culture, scenic destinations, 
                and heritage towns await. Experience the best of local products, crafts, and flavors 
                while supporting community tourism.”
            </p>
            <p style="background-color: rgb(26, 99, 182); padding:50px; color: aliceblue;">Presented by TechForge</p>
        </section>
    </footer>
</body>
</html>