<?php
session_start();
include "db.php";

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// ================================
// HANDLE PROFILE PICTURE UPLOAD
// ================================
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_FILES['profile_pic'])) {

    // Upload directory (php/uploads/)
    $uploadDir = __DIR__ . "/uploads/";

    // Create folder if it doesn't exist
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // File details
    $file = $_FILES['profile_pic'];
    $filename = time() . "_" . basename($file['name']);
    $path = $uploadDir . $filename;

    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $path)) {

        // Update profile picture in database
        $stmt = $conn->prepare("UPDATE users SET profile_pic = ? WHERE user_id = ?");
        $stmt->bind_param("si", $filename, $user_id);
        $stmt->execute();

        // Update session value
        $_SESSION['profile_pic'] = $filename;
    }

    // Prevent form resubmission
    header("Location: user_dash.php");
    exit();
}

// Fallback profile picture
$profilePic = $_SESSION['profile_pic'] ?? 'default.png';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>e-Lakbay | Profile</title>
    <link rel="stylesheet" href="../css/user_dash.css">
</head>
<body>

<div class="container">

    <!-- HEADER -->
    <div class="header">
        <h2>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?> 🌍</h2>
        <a href="logout.php" class="logout">Logout</a>
    </div>

    <!-- PROFILE CARD -->
    <div class="profile-card">
        <img 
            src="uploads/<?php echo htmlspecialchars($profilePic); ?>" 
            alt="Profile Picture" 
            class="profile-pic"
        >

        <!-- SAME PAGE UPLOAD -->
        <form method="POST" enctype="multipart/form-data">
            <input type="file" name="profile_pic" accept="image/*" required>
            <button type="submit">Change Profile Picture</button>
        </form>
    </div>

    <!-- CREATE POST -->
    <div class="post-box">
        <form action="create_post.php" method="POST">
            <textarea name="content" placeholder="Share your travel experience..." required></textarea>
            <button type="submit">Post</button>
        </form>
    </div>

    <!-- POSTS FEED -->
    <?php
    $sql = "SELECT posts.*, users.username 
            FROM posts 
            JOIN users ON posts.user_id = users.user_id 
            ORDER BY created_at DESC";
    $result = $conn->query($sql);

    while ($row = $result->fetch_assoc()):
    ?>
        <div class="post">
            <div class="post-header">
                <strong><?php echo htmlspecialchars($row['username']); ?></strong>
                <span><?php echo date("M d, Y", strtotime($row['created_at'])); ?></span>
            </div>

            <p><?php echo nl2br(htmlspecialchars($row['content'])); ?></p>

            <!-- DELETE POST -->
            <?php if ($row['user_id'] == $user_id): ?>
                <a class="delete-btn" href="delete_post.php?id=<?php echo $row['post_id']; ?>">Delete</a>
            <?php endif; ?>

            <!-- COMMENT FORM -->
            <form action="comment.php" method="POST" class="comment-form">
                <input type="hidden" name="post_id" value="<?php echo $row['post_id']; ?>">
                <input type="text" name="comment" placeholder="Write a comment..." required>
                <button type="submit">Comment</button>
            </form>
        </div>
    <?php endwhile; ?>

</div>

</body>
</html>
