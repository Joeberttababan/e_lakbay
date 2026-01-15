
<?php
session_start();
include "db.php";

// Constants
const UPLOAD_DIR = __DIR__ . "/uploads/";
const MAX_FILE_SIZE = 5 * 1024 * 1024; // 5MB
const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif'];
const ALLOWED_MIME_TYPES = ['image/jpeg', 'image/png', 'image/gif'];
const DEFAULT_PROFILE_PIC = 'default.png';

// Variables
$postsPerPage = 10;

// Redirect if not logged in
if (!isset($_SESSION['user_id']) || !is_numeric($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = (int) $_SESSION['user_id'];
$username = $_SESSION['username'] ?? 'User';

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

// Initialize messages
$message = "";

// Pagination
$page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
$offset = ($page - 1) * $postsPerPage;

// ================================
// HANDLE PROFILE PICTURE UPLOAD
// ================================
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_FILES['profile_pic']) && isset($_POST['csrf_token']) && hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    $file = $_FILES['profile_pic'];
    $errors = [];

    // Validate file
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = "Upload error: " . $file['error'];
    }
    if ($file['size'] > MAX_FILE_SIZE) {
        $errors[] = "File size exceeds 5MB.";
    }
    $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($fileExtension, ALLOWED_EXTENSIONS)) {
        $errors[] = "Invalid file type. Allowed: JPG, PNG, GIF.";
    }
    $mimeType = mime_content_type($file['tmp_name']);
    if (!in_array($mimeType, ALLOWED_MIME_TYPES)) {
        $errors[] = "Invalid MIME type.";
    }

    if (empty($errors)) {
        if (!is_dir(UPLOAD_DIR)) {
            mkdir(UPLOAD_DIR, 0755, true);
        }
        $filename = time() . "_" . preg_replace("/[^a-zA-Z0-9\._-]/", "", basename($file['name']));
        $path = UPLOAD_DIR . $filename;

        if (move_uploaded_file($file['tmp_name'], $path)) {
            try {
                $stmt = $conn->prepare("UPDATE users SET profile_pic = ? WHERE user_id = ?");
                $stmt->bind_param("si", $filename, $user_id);
                $stmt->execute();
                $_SESSION['profile_pic'] = $filename;
                $message = "Profile picture updated!";
            } catch (Exception $e) {
                error_log("Database error: " . $e->getMessage());
                $message = "Error updating profile picture.";
            }
        } else {
            $message = "Failed to upload file.";
        }
    } else {
        $message = implode("<br>", $errors);
    }
    header("Location: user_dash.php");
    exit();
}

// ================================
// HANDLE POST CREATION (Text, Product, Spot, Event with Images)
// ================================
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['csrf_token']) && hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    $type = $_POST['type'] ?? 'post';

    if ($type === 'post' && isset($_POST['content'])) {
        $content = trim($_POST['content']);
        $image = null;
        if (!empty($_FILES['post_image']['name']) && $_FILES['post_image']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['post_image'];
            $filename = time() . "_" . preg_replace("/[^a-zA-Z0-9\._-]/", "", basename($file['name']));
            $path = UPLOAD_DIR . $filename;
            if (move_uploaded_file($file['tmp_name'], $path)) {
                $image = $filename;
            }
        }
        if (!empty($content) || $image) {
            try {
                $stmt = $conn->prepare("INSERT INTO posts (user_id, content, image, created_at) VALUES (?, ?, ?, NOW())");
                $stmt->bind_param("iss", $user_id, $content, $image);
                $stmt->execute();
                $message = "Post created!";
            } catch (Exception $e) {
                error_log("Database error: " . $e->getMessage());
                $message = "Error creating post.";
            }
        } else {
            $message = "Content or image is required.";
        }
    } elseif ($type === 'product' && isset($_POST['product_name'], $_FILES['product_image'])) {
        try {
            $name = trim($_POST['product_name']);
            $desc = trim($_POST['description']);
            $price = (float) $_POST['price'];
            $town = trim($_POST['town']);
            $file = $_FILES['product_image'];

            if (!empty($name) && $file['error'] === UPLOAD_ERR_OK) {
                $filename = time() . "_" . preg_replace("/[^a-zA-Z0-9\._-]/", "", basename($file['name']));
                $path = UPLOAD_DIR . $filename;
                if (move_uploaded_file($file['tmp_name'], $path)) {
                    $stmt = $conn->prepare("INSERT INTO products (product_name, description, price, town, image, user_id, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
                    $stmt->bind_param("ssdssi", $name, $desc, $price, $town, $filename, $user_id);
                    $stmt->execute();
                    $message = "Product added!";
                } else {
                    $message = "Failed to upload image.";
                }
            } else {
                $message = "Invalid product data.";
            }
        } catch (Exception $e) {
            error_log("Error adding product: " . $e->getMessage());
            $message = "Error adding product.";
        }
    } elseif ($type === 'spot' && isset($_POST['spot_name'], $_FILES['spot_image'])) {
        try {
            $name = trim($_POST['spot_name']);
            $desc = trim($_POST['description']);
            $location = trim($_POST['location']);
            $file = $_FILES['spot_image'];

            if (!empty($name) && $file['error'] === UPLOAD_ERR_OK) {
                $filename = time() . "_" . preg_replace("/[^a-zA-Z0-9\._-]/", "", basename($file['name']));
                $path = UPLOAD_DIR . $filename;
                if (move_uploaded_file($file['tmp_name'], $path)) {
                    $stmt = $conn->prepare("INSERT INTO tourist_spots (name, description, location, image, user_id, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
                    $stmt->bind_param("ssssi", $name, $desc, $location, $filename, $user_id);
                    $stmt->execute();
                    $message = "Tourist spot added!";
                } else {
                    $message = "Failed to upload image.";
                }
            } else {
                $message = "Invalid spot data.";
            }
        } catch (Exception $e) {
            error_log("Error adding spot: " . $e->getMessage());
            $message = "Error adding spot.";
        }
    } elseif ($type === 'event' && isset($_POST['event_title'], $_FILES['event_image'])) {
        try {
            $title = trim($_POST['event_title']);
            $desc = trim($_POST['description']);
            $date = $_POST['event_date'];
            $location = trim($_POST['location']);
            $file = $_FILES['event_image'];

            if (!empty($title) && !empty($date) && $file['error'] === UPLOAD_ERR_OK) {
                $filename = time() . "_" . preg_replace("/[^a-zA-Z0-9\._-]/", "", basename($file['name']));
                $path = UPLOAD_DIR . $filename;
                if (move_uploaded_file($file['tmp_name'], $path)) {
                    $stmt = $conn->prepare("INSERT INTO events (title, description, date, location, image, user_id, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
                    $stmt->bind_param("sssssi", $title, $desc, $date, $location, $filename, $user_id);
                    $stmt->execute();
                    $message = "Event posted!";
                } else {
                    $message = "Failed to upload image.";
                }
            } else {
                $message = "Invalid event data.";
            }
        } catch (Exception $e) {
            error_log("Error posting event: " . $e->getMessage());
            $message = "Error posting event.";
        }
    }

    header("Location: user_dash.php");
    exit();
}

// Fallback profile picture
$profilePic = $_SESSION['profile_pic'] ?? DEFAULT_PROFILE_PIC;
if (!file_exists(UPLOAD_DIR . $profilePic)) {
    $profilePic = DEFAULT_PROFILE_PIC;
}

// Fetch unified feed (posts, products, spots, events) with pagination
$feed = [];
try {
    // Fetch posts
    $result = $conn->query("SELECT 'post' AS type, posts.*, users.username FROM posts JOIN users ON posts.user_id = users.user_id ORDER BY created_at DESC LIMIT 50");
    while ($row = $result->fetch_assoc()) {
        $feed[] = $row;
    }
    // Fetch products
    $result = $conn->query("SELECT 'product' AS type, products.*, users.username FROM products JOIN users ON products.user_id = users.user_id ORDER BY created_at DESC LIMIT 50");
    while ($row = $result->fetch_assoc()) {
        $feed[] = $row;
    }
    // Fetch spots
    $result = $conn->query("SELECT 'spot' AS type, tourist_spots.*, users.username FROM tourist_spots JOIN users ON tourist_spots.user_id = users.user_id ORDER BY created_at DESC LIMIT 50");
    while ($row = $result->fetch_assoc()) {
        $feed[] = $row;
    }
    // Fetch events
    $result = $conn->query("SELECT 'event' AS type, events.*, users.username FROM events JOIN users ON events.user_id = users.user_id ORDER BY created_at DESC LIMIT 50");
    while ($row = $result->fetch_assoc()) {
        $feed[] = $row;
    }
} catch (Exception $e) {
    error_log("Error fetching feed: " . $e->getMessage());
    $message = "Error loading feed.";
}

// Sort and paginate the feed
usort($feed, function($a, $b) {
    return strtotime($b['created_at']) - strtotime($a['created_at']);
});
$totalItems = count($feed);
$paginatedFeed = array_slice($feed, $offset, $postsPerPage);
$totalPages = ceil($totalItems / $postsPerPage);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>e-Lakbay | Dashboard</title>
    <link rel="stylesheet" href="../css/user_dash.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
</head>
<style>
    /* ===============================
   GLOBAL RESET & BASE
================================ */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Poppins', sans-serif;
}

body {
    background:white;
    color: #333;
}

/* ===============================
   DASHBOARD LAYOUT
================================ */
.dashboard {
    display: flex;
    min-height: 100vh;
}

/* ===============================
   SIDEBAR
================================ */
.sidebar {
    width: 320px;
    background: #0f4c75;
    color: #fff;
    padding: 20px;
    overflow-y: auto;
    transition: transform 0.3s ease;
}

.sidebar.open {
    transform: translateX(0);
}

.sidebar-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.sidebar-header h2 {
    font-size: 22px;
    font-weight: 600;
}

.toggle-btn {
    background: none;
    border: none;
    font-size: 22px;
    color: #fff;
    cursor: pointer;
}

/* ===============================
   PROFILE CARD
================================ */
.profile-card {
    text-align: center;
    background: #ffffff;
    color: #333;
    padding: 20px;
    border-radius: 10px;
    margin-bottom: 20px;
}

.profile-pic {
    width: 90px;
    height: 90px;
    border-radius: 50%;
    object-fit: cover;
    margin-bottom: 10px;
}

.profile-card h3 {
    margin-bottom: 10px;
    font-size: 18px;
}

/* ===============================
   FORMS
================================ */
.post-forms {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.form-card {
    background: #ffffff;
    padding: 15px;
    border-radius: 10px;
}

.form-card h4 {
    margin-bottom: 10px;
    font-size: 16px;
    color: #0f4c75;
}

.form-card input,
.form-card textarea,
.form-card button {
    width: 100%;
    margin-bottom: 10px;
    padding: 8px;
    border-radius: 6px;
    border: 1px solid #ccc;
    font-size: 14px;
}

.form-card textarea {
    resize: none;
    min-height: 80px;
}

.btn-primary {
    background: #0f4c75;
    color: #fff;
    border: none;
    cursor: pointer;
    transition: background 0.3s;
}

.btn-primary:hover {
    background: #1b6ca8;
}

/* ===============================
   MAIN CONTENT
================================ */
.main-content {
    flex: 1;
    padding: 25px;
}

/* ===============================
   HEADER
================================ */
.main-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.main-header h1 {
    font-size: 24px;
}

.logout-btn {
    text-decoration: none;
    background: #dc3545;
    color: #fff;
    padding: 8px 14px;
    border-radius: 6px;
    font-size: 14px;
}

.logout-btn:hover {
    background: #b02a37;
}

/* ===============================
   MESSAGES
================================ */
.message {
    background: #d1e7dd;
    color: #0f5132;
    padding: 10px;
    border-radius: 6px;
    margin-bottom: 15px;
}

/* ===============================
   FEED
================================ */
.feed {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.no-content {
    text-align: center;
    color: #777;
    font-size: 15px;
}

/* ===============================
   FEED CARD
================================ */
.feed-card {
    background: #ffffff;
    border-radius: 10px;
    padding: 15px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.08);
}

.card-header {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 10px;
}

.user-pic {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    object-fit: cover;
}

.card-header strong {
    font-size: 14px;
}

.card-header span {
    display: block;
    font-size: 12px;
    color: #777;
}

.delete-btn {
    margin-left: auto;
    color: #dc3545;
    text-decoration: none;
    font-size: 18px;
}

.delete-btn:hover {
    color: #b02a37;
}

/* ===============================
   CARD CONTENT
================================ */
.card-content p {
    font-size: 14px;
    margin-bottom: 8px;
}

.card-content h4 {
    font-size: 16px;
    margin-bottom: 6px;
    color: #0f4c75;
}

.card-image {
    width: 100%;
    max-height: 350px;
    object-fit: cover;
    border-radius: 8px;
    margin-top: 10px;
}

/* ===============================
   RESPONSIVE
================================ */
@media (max-width: 900px) {
    .dashboard {
        flex-direction: column;
    }

    .sidebar {
        width: 100%;
    }
}

</style>
<body>
    <div class="dashboard">
        <!-- Sidebar for Forms -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2>Profile</h2>
                <button class="toggle-btn" onclick="toggleSidebar()">☰</button>
            </div>
            <div class="profile-card">
                <img src="uploads/<?php echo htmlspecialchars($profilePic); ?>" alt="Profile Picture" class="profile-pic">
                <h3><?php echo htmlspecialchars($username); ?></h3>
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                    <input type="file" name="profile_pic" accept="image/*" required>
                    <button type="submit" class="btn-primary">Update Pic</button>
                </form>
            </div>
            <div class="post-forms">
                <div class="form-card">
                    <h4>Share a Post</h4>
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                        <input type="hidden" name="type" value="post">
                        <textarea name="content" placeholder="What's on your mind?" required></textarea>
                        <input type="file" name="post_image" accept="image/*">
                        <button type="submit" class="btn-primary">Post</button>
                    </form>
                </div>
                <div class="form-card">
                    <h4>Add Product</h4>
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                        <input type="hidden" name="type" value="product">
                        <input type="text" name="product_name" placeholder="Product Name" required>
                        <textarea name="description" placeholder="Description"></textarea>
                        <input type="number" step="0.01" name="price" placeholder="Price" required>
                        <input type="text" name="town" placeholder="Town" required>
                        <input type="file" name="product_image" accept="image/*" required>
                        <button type="submit" class="btn-primary">Add</button>
                    </form>
                </div>
                <div class="form-card">
                    <h4>Add Tourist Spot</h4>
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                        <input type="hidden" name="type" value="spot">
                        <input type="text" name="spot_name" placeholder="Spot Name" required>
                        <textarea name="description" placeholder="Description"></textarea>
                        <input type="text" name="location" placeholder="Location" required>
                        <input type="file" name="spot_image" accept="image/*" required>
                        <button type="submit" class="btn-primary">Add</button>
                    </form>
                </div>
                <div class="form-card">
                    <h4>Post Event</h4>
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                        <input type="hidden" name="type" value="event">
                        <input type="text" name="event_title" placeholder="Event Title" required>
                        <textarea name="description" placeholder="Description"></textarea>
                        <input type="date" name="event_date" required>
                        <input type="text" name="location" placeholder="Location" required>
                        <input type="file" name="event_image" accept="image/*" required>
                        <button type="submit" class="btn-primary">Post</button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- Main Feed -->
        <main class="main-content">
    <header class="main-header">
        <h1>One Ilocos Sur!</h1>
        <a href="logout.php" class="logout-btn">Logout</a>
    </header>

    <?php if ($message): ?>
        <div class="message"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <div class="feed">
        <?php if (empty($paginatedFeed)): ?>
            <p class="no-content">No content yet. Start sharing your adventures!</p>
        <?php else: ?>
            <?php foreach ($paginatedFeed as $item): ?>
                <div class="feed-card">
                    <div class="card-header">
                        <img src="uploads/<?php echo htmlspecialchars($profilePic); ?>" 
                             alt="User Pic" 
                             class="user-pic">

                        <div>
                            <strong><?php echo htmlspecialchars($item['username']); ?></strong>
                            <span>
                                <?php echo date("M d, Y", strtotime($item['created_at'])); ?> • 
                                <?php echo ucfirst($item['type']); ?>
                            </span>
                        </div>

                        <?php if ($item['user_id'] == $user_id): ?>
                            <a href="delete_post.php?id=<?php echo $item['post_id']; ?>&csrf_token=<?php echo htmlspecialchars($csrf_token); ?>" 
                               class="delete-btn">🗑️</a>
                        <?php endif; ?>
                    </div>

                    <div class="card-content">
                        <?php if ($item['type'] === 'post'): ?>
                            <p><?php echo nl2br(htmlspecialchars($item['content'])); ?></p>

                            <?php if (!empty($item['image'])): ?>
                                <img src="uploads/<?php echo htmlspecialchars($item['image']); ?>" 
                                     alt="Post Image" 
                                     class="card-image">
                            <?php endif; ?>

                        <?php elseif ($item['type'] === 'product'): ?>
                            <h4><?php echo htmlspecialchars($item['product_name']); ?></h4>

                            <p>
                                <?php echo htmlspecialchars($item['description'] ?: 'No description available.'); ?>
                            </p>

                            <p>
                                <strong>Price:</strong> 
                                ₱<?php echo number_format($item['price'], 2); ?>
                            </p>

                            <?php if (!empty($item['image'])): ?>
                                <img src="uploads/<?php echo htmlspecialchars($item['image']); ?>" 
                                     alt="Product Image" 
                                     class="card-image">
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</main>

</div> <!-- end dashboard -->

</div> <!-- end .dashboard -->

<script>
    function toggleSidebar() {
        document.querySelector('.sidebar').classList.toggle('open');
    }
</script>

</body>
</html>