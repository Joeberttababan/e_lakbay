<?php
// Include database connection (corrected path: db.php is in the same directory)
include "db.php";

// Initialize feed array to hold all content types
$feed = [];

// Fetch posts (text posts from users)
try {
    $result = $conn->query("SELECT 'post' AS type, posts.*, users.username FROM posts JOIN users ON posts.user_id = users.user_id ORDER BY created_at DESC LIMIT 10");
    while ($row = $result->fetch_assoc()) {
        $feed[] = $row;
    }
} catch (Exception $e) {
    error_log("Error fetching posts: " . $e->getMessage());
}

// Fetch products (user-submitted products)
try {
    $result = $conn->query("SELECT 'product' AS type, products.*, users.username FROM products JOIN users ON products.user_id = users.user_id ORDER BY created_at DESC LIMIT 10");
    while ($row = $result->fetch_assoc()) {
        $feed[] = $row;
    }
} catch (Exception $e) {
    error_log("Error fetching products: " . $e->getMessage());
}

// Fetch tourist spots (user-submitted spots)
try {
    $result = $conn->query("SELECT 'spot' AS type, tourist_spots.*, users.username FROM tourist_spots JOIN users ON tourist_spots.user_id = users.user_id ORDER BY created_at DESC LIMIT 10");
    while ($row = $result->fetch_assoc()) {
        $feed[] = $row;
    }
} catch (Exception $e) {
    error_log("Error fetching spots: " . $e->getMessage());
}

// Fetch events (user-posted events)
try {
    $result = $conn->query("SELECT 'event' AS type, events.*, users.username FROM events JOIN users ON events.user_id = users.user_id ORDER BY created_at DESC LIMIT 10");
    while ($row = $result->fetch_assoc()) {
        $feed[] = $row;
    }
} catch (Exception $e) {
    error_log("Error fetching events: " . $e->getMessage());
}

// Sort the entire feed by creation date (newest first)
usort($feed, function($a, $b) {
    return strtotime($b['created_at']) - strtotime($a['created_at']);
});

// Close database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>e-Lakbay</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    <style>
        /* Basic styles for the new feed section */
        html {
            scroll-behavior: smooth;
        }
        .community-feed {
            padding: 50px 5%;
            text-align: center;
        }
        .feed-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }
        .feed-item {
            background: #ffffff;
            border-radius: 8px;
            padding: 15px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.15);
            transition: transform 0.3s ease;
        }
        .feed-item:hover {
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
        /* Styles for clickable municipalities */
        .municipals-grid a {
            text-decoration: none;
            color: inherit;
        }
        .municipals {
            cursor: pointer;
            transition: transform 0.3s ease;
        }
        .municipals:hover {
            transform: scale(1.05);
        }
        @media (max-width: 768px) {
            .feed-container {
                grid-template-columns: 1fr;
            }
            .feed-item {
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

    <!-- HERO SECTION -->
    <section class="hero">
        <div class="hero-content">
            <h1 class="title">Explore</h1>
            <h2 class="subtitle">2nd District of Ilocos Sur</h2>
            <p class="tagline">“Explore, taste, and enjoy the culture of every town.”</p>
        </div>
        <div class="search-box">
            <input type="text" placeholder="Search destinations, products, or towns..." />
            <button type="submit">Search</button>
        </div>
        <!-- LOCATION CARDS -->
        <div class="cards-container">
            <div class="card">
                <h3>Location</h3>
                <p>
                    Lorem ipsum dolor sit amet, consectetur adipiscing elit.
                    Nullam in magna nulla. Quisque id pretium quam. Vivamus malesuada
                    dignissim volutpat. Sed pulvinar tortor et lorem mattis placerat.
                </p>
            </div>
            <div class="card">
                <h3>Location</h3>
                <p>
                    Lorem ipsum dolor sit amet, consectetur adipiscing elit.
                    Nullam in magna nulla. Quisque id pretium quam. Vivamus malesuada
                    dignissim volutpat. Sed pulvinar tortor et lorem mattis placerat.
                </p>
            </div>
            <div class="card">
                <h3>Location</h3>
                <p>
                    Lorem ipsum dolor sit amet, consectetur adipiscing elit.
                    Nullam in magna nulla. Quisque id pretium quam. Vivamus malesuada
                    dignissim volutpat. Sed pulvinar tortor et lorem mattis placerat.
                </p>
            </div>
        </div>
    </section>

    <!-- TOP DESTINATION -->
    <section class="top-destination">
        <h2 class="section-title">Top Destination</h2>
        <p class="section-sub">“Discover the heart of Ilocos Sur — where culture, nature, and history meet.”</p>
        <div class="destination-scroll">
            <img src="../assets/dest1.jpg" alt="Top destination 1"> <!-- Updated path -->
            <img src="../assets/hills.jpg" alt="Scenic hills">
            <img src="../assets/dest3.jpg" alt="Top destination 3">
            <img src="../assets/mhall5.jpg" alt="Municipal hall">
        </div>
        <p class="more"><a href="#">Click Here to View More</a></p>
    </section>

    <!-- LOCAL PRODUCTS -->
    <section id="municipalities" class="municipalities">
        <h2 class="section-title">Municipalities</h2>
        <p class="section-sub">“Discover Our wonderful Municipalities”</p>
        <div class="municipals-grid">
            <a href="muni_prof.php?town=sta_cruz">
                <div class="municipals"><img src="../assets/Scruz.png" alt="Sta. Cruz"><p>Sta. Cruz</p></div>
            </a>
            <a href="muni_prof.php?town=sta_maria">
                <div class="municipals"><img src="../assets/Smaria.png" alt="Sta. Maria"><p>Sta. Maria</p></div>
            </a>
            <a href="muni_prof.php?town=candon">
                <div class="municipals"><img src="../assets/candon.png" alt="Candon"><p>Candon</p></div>
            </a>
            <a href="muni_prof.php?town=gregorio_del_pilar">
                <div class="municipals"><img src="../assets/Gdp.png" alt="Gregorio Del Pilar"><p>Gregorio Del Pilar</p></div>
            </a>
            <a href="muni_prof.php?town=alilem">
                <div class="municipals"><img src="../assets/Alilem.png" alt="Alilem"><p>Alilem</p></div>
            </a>
            <a href="muni_prof.php?town=salcedo">
                <div class="municipals"><img src="../assets/salcedo.png" alt="Salcedo"><p>Salcedo</p></div>
            </a>
            <a href="muni_prof.php?town=san_emilio">
                <div class="municipals"><img src="../assets/Sem.png" alt="San Emilio"><p>San Emilio</p></div>
            </a>
            <a href="muni_prof.php?town=lidlidda">
                <div class="municipals"><img src="../assets/Lda.png" alt="Lidlidda"><p>Lidlidda</p></div>
            </a>
        </div>
    </section>
    <hr>

    <!-- COMMUNITY FEED (New Dynamic Section) -->
    <section class="community-feed">
        <h2 class="section-title">Community Feed</h2>
        <p class="section-sub">See the latest posts, products, tourist spots, and events from our users.</p>
        <?php if (empty($feed)): ?>
            <p>No community content yet. <a href="user_dash.php">Start sharing!</a></p>
        <?php else: ?>
            <div class="feed-container">
                <?php foreach ($feed as $item): ?>
                    <div class="feed-item">
                        <div class="item-header">
                            <strong><?php echo htmlspecialchars($item['username']); ?></strong>
                            <span><?php echo date("M d, Y", strtotime($item['created_at'])); ?></span>
                            <span class="item-type"><?php echo ucfirst($item['type']); ?></span>
                        </div>
                        <?php if ($item['type'] === 'post'): ?>
                            <p><?php echo nl2br(htmlspecialchars($item['content'])); ?></p>
                        <?php elseif ($item['type'] === 'product'): ?>
                            <h3><?php echo htmlspecialchars($item['product_name']); ?></h3>
                            <p><?php echo htmlspecialchars($item['description'] ?: 'No description.'); ?></p>
                            <p><strong>Price:</strong> ₱<?php echo number_format($item['price'], 2); ?> | <strong>Town:</strong> <?php echo htmlspecialchars($item['town']); ?></p>
                            <?php if ($item['image']): ?>
                                <img src="uploads/<?php echo htmlspecialchars($item['image']); ?>" alt="Product Image" class="item-image">
                            <?php endif; ?>
                        <?php elseif ($item['type'] === 'spot'): ?>
                            <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                            <p><?php echo htmlspecialchars($item['description'] ?: 'No description.'); ?></p>
                            <p><strong>Location:</strong> <?php echo htmlspecialchars($item['location']); ?></p>
                            <?php if ($item['image']): ?>
                                <img src="uploads/<?php echo htmlspecialchars($item['image']); ?>" alt="Spot Image" class="item-image">
                            <?php endif; ?>
                        <?php elseif ($item['type'] === 'event'): ?>
                            <h3><?php echo htmlspecialchars($item['title']); ?></h3>
                            <p><?php echo htmlspecialchars($item['description'] ?: 'No description.'); ?></p>
                            <p><strong>Date:</strong> <?php echo htmlspecialchars($item['date']); ?> | <strong>Location:</strong> <?php echo htmlspecialchars($item['location']); ?></p>
                            <?php if ($item['image']): ?>
                                <img src="uploads/<?php echo htmlspecialchars($item['image']); ?>" alt="Event Image" class="item-image">
                            <?php endif; ?>
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