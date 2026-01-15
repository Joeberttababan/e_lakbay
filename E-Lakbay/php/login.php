<?php
// Start the session to store user login data
session_start();

// Include the database connection file
include "db.php";

// Variable to store error messages
$error = "";

// Check if the form was submitted using POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Get and trim the email input from the login form
    $email = trim($_POST['email'] ?? '');

    // Get and trim the password input from the login form
    $password = trim($_POST['password'] ?? '');

    // Check if email or password fields are empty
    if (empty($email) || empty($password)) {
        // Set error message if fields are empty
        $error = "Please fill in all fields.";
    } else {

        // Prepare SQL query to fetch user data based on email
        $stmt = $conn->prepare("SELECT user_id, username, user_password, role FROM users WHERE user_email = ?");

        // Bind the email parameter to the prepared SQL statement
        $stmt->bind_param("s", $email);

        // Execute the prepared statement
        $stmt->execute();

        // Store the result of the query
        $stmt->store_result();

        // Check if a user with the given email exists
        if ($stmt->num_rows === 1) {

            // Bind the returned database values to PHP variables
            $stmt->bind_result($user_id, $username, $hashed_password, $role);

            // Fetch the user record
            $stmt->fetch();

            // Verify the entered password against the hashed password
            if (password_verify($password, $hashed_password)) {

                // Store user ID in session
                $_SESSION['user_id'] = $user_id;

                // Store username in session
                $_SESSION['username'] = $username;

                // Store user role in session
                $_SESSION['role'] = $role;

                // Redirect the user to the dashboard after successful login
                header("Location: user_dash.php");

                // Stop script execution after redirect
                exit();
            } else {
                // Set error message if password is incorrect
                $error = "Invalid email or password.";
            }
        } else {
            // Set error message if email does not exist
            $error = "Invalid email or password.";
        }

        // Close the prepared statement
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>e-Lakbay | Login</title>
    <link rel="stylesheet" href="../css/logIn.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>

<header class="navbar">
    <div class="logo">e-Lakbay</div>
    <nav>
        <a href="../php/index.php">Home</a>
        <a href="">Destination</a>
        <a href="">Products</a>
        <a href="">Search</a>
    </nav>
</header>

<section class="hero">
    <div class="overlay"></div>

    <div class="hero-content">
        <h1>Explore<br>
            <span>2nd District of</span><br>
            <strong>Ilocos Sur</strong>
        </h1>
        <p class="tagline">“Explore, taste, and enjoy the culture of every town.”</p>
    </div>

    <div class="login-box">
        <h2>Log In</h2>
        <p class="subtitle">"Log in & Take a Step Towards Success!"</p>

        <!-- ERROR MESSAGE -->
        <?php if (!empty($error)): ?>
            <p style="color:red; font-size:14px;"><?php echo $error; ?></p>
        <?php endif; ?>

        <!-- LOGIN FORM -->
        <form method="POST" action="">
            <input type="email" name="email" placeholder="Enter your email address" required>

            <div class="password-field">
                <input type="password" name="password" placeholder="Enter your password" required>
                <span class="eye">👁</span>
            </div>

            <button type="submit" class="login-btn">Login</button>
        </form>

        <button class="google-btn">
            <img src="https://www.gstatic.com/firebasejs/ui/2.0.0/images/auth/google.svg">
            Login with Google
        </button>

        <a href="#" class="forgot">Forgot Password?</a>
    </div>
</section>

</body>
</html>
