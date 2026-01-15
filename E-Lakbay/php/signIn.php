<?php
// Include database connection (assumes $conn is a mysqli object)
include "db.php";

// Constants for maintainability
const USER_TABLE = 'users';
const DEFAULT_ROLE = 'user';

// Initialize variables
$message = "";
$showPopup = false;
$errors = []; // Array to hold multiple errors

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Handle redirect choice after signup
    if (isset($_POST['final_redirect'])) {
        session_start();
        if ($_POST['final_redirect'] === 'dashboard') {
            header("Location: user_dash.php");
        } else {
            header("Location: ../pages/index.html");
        }
        exit();
    }

    // Signup logic
    $username = trim($_POST['municipality'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm'] ?? '';

    // Validation
    if (empty($username) || empty($email) || empty($password) || empty($confirm)) {
        $errors[] = "All fields are required!";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address!";
    }
    if ($password !== $confirm) {
        $errors[] = "Passwords do not match!";
    }
    // Password strength check (customize as needed)
    if (strlen($password) < 8 || !preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) || !preg_match('/[0-9]/', $password)) {
        $errors[] = "Password must be at least 8 characters long and include at least one uppercase letter, one lowercase letter, and one number!";
    }

    if (empty($errors)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $role = DEFAULT_ROLE;

        // Check if email already exists
        $stmt = $conn->prepare("SELECT user_id FROM " . USER_TABLE . " WHERE user_email = ?");
        if (!$stmt) {
            error_log("Database prepare error: " . $conn->error);
            $errors[] = "Database error occurred. Please try again.";
        } else {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $errors[] = "Email is already registered!";
            } else {
                $stmt->close();

                // Insert new user
                $stmt = $conn->prepare(
                    "INSERT INTO " . USER_TABLE . " (username, user_email, user_password, role) VALUES (?, ?, ?, ?)"
                );
                if (!$stmt) {
                    error_log("Database prepare error: " . $conn->error);
                    $errors[] = "Database error occurred. Please try again.";
                } else {
                    $stmt->bind_param("ssss", $username, $email, $hashedPassword, $role);
                    if ($stmt->execute()) {
                        // Start session and set variables
                        session_start();
                        $_SESSION['user_email'] = $email;
                        $_SESSION['role'] = $role;
                        $showPopup = true;
                    } else {
                        error_log("Database execute error: " . $stmt->error);
                        $errors[] = "Error creating account. Please try again.";
                    }
                }
            }
            $stmt->close();
        }
    }

    // Combine errors into a single message
    if (!empty($errors)) {
        $message = implode("<br>", $errors);
    }
}

// Close database connection
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>e-Lakbay | Sign Up</title>
    <link rel="stylesheet" href="../css/signIn.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        /* Modal styles for better UX */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }
        .modal.show {
            display: flex;
        }
        .modal-content {
            background: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            max-width: 400px;
            width: 90%;
        }
        .modal-content h3 {
            margin-bottom: 10px;
        }
        .modal-content button {
            margin: 10px;
            padding: 10px 20px;
            border: none;
            cursor: pointer;
        }
        .btn-primary {
            background: #007bff;
            color: white;
        }
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        .close-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            background: none;
            border: none;
            font-size: 20px;
            cursor: pointer;
        }
    </style>
</head>
<body>

<header class="navbar">
    <div class="logo">e-Lakbay</div>
    <nav>
        <a href="../php/index.php">Home</a>
        <a href="index.php">Municipalities</a>
        <a href="#">Products</a>
    </nav>
</header>

<section class="hero">
    <div class="content">
        <h1>Explore<br><span>2nd District of</span><br><strong>Ilocos Sur</strong></h1>
        <p>“Explore, taste, and enjoy the culture of every town.”</p>
    </div>

    <div class="form-box">
        <h2>Sign Up</h2>

        <?php if ($message): ?>
            <p class="msg"><?php echo $message; ?></p>
        <?php endif; ?>

        <form method="POST">
            <input type="text" name="municipality" placeholder="Enter Municipality Name" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Enter Your Password" required>
            <input type="password" name="confirm" placeholder="Confirm Your Password" required>

            <button type="submit">Sign Up</button>
        </form>

        <p class="login-link">
            Already have an account?
            <a href="../php/logIn.php">Login</a>
        </p>
    </div>
</section>

<!-- MODAL -->
<div id="redirectModal" class="modal" role="dialog" aria-labelledby="modal-title" aria-hidden="true">
    <div class="modal-content">
        <button class="close-btn" aria-label="Close modal">&times;</button>
        <h3 id="modal-title">🎉 Account Created Successfully</h3>
        <p>Where would you like to go?</p>

        <form method="POST">
            <button type="submit" name="final_redirect" value="dashboard" class="btn-primary">
                Go to Dashboard
            </button>

            <button type="submit" name="final_redirect" value="public" class="btn-secondary">
                Stay on Public Site
            </button>
        </form>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const modal = document.getElementById("redirectModal");
    const closeBtn = modal.querySelector(".close-btn");

    // Show modal if PHP sets it
    <?php if ($showPopup): ?>
        modal.classList.add("show");
        document.body.style.overflow = "hidden";
        modal.setAttribute("aria-hidden", "false");
    <?php endif; ?>

    // Close modal on close button or ESC
    closeBtn.addEventListener("click", closeModal);
    document.addEventListener("keydown", function (e) {
        if (e.key === "Escape" && modal.classList.contains("show")) {
            closeModal();
        }
    });

    function closeModal() {
        modal.classList.remove("show");
        document.body.style.overflow = "";
        modal.setAttribute("aria-hidden", "true");
        // Optional: Redirect to public site on close
        window.location.href = "../pages/index.html";
    }
});
</script>

</body>
</html>