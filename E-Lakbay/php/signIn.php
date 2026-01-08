<?php
// Include the database connection file
include "db.php";

// Variable to store success or error messages
$message = "";

// Check if the form was submitted using POST method
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Get and trim the username (municipality) input from the form
    $username = trim($_POST['municipality'] ?? '');

    // Get and trim the email input from the form
    $email = trim($_POST['email'] ?? '');

    // Get the password input from the form
    $password = $_POST['password'] ?? '';

    // Get the confirm password input from the form
    $confirm = $_POST['confirm'] ?? '';

    // Check if any of the required fields are empty
    if (empty($username) || empty($email) || empty($password) || empty($confirm)) {
        // Set error message if fields are missing
        $message = "All fields are required!";
    
    // Check if password and confirm password do not match
    } elseif ($password !== $confirm) {
        // Set error message if passwords mismatch
        $message = "Passwords do not match!";
    
    } else {
        // Hash the password for security before saving to database
        $hashed = password_hash($password, PASSWORD_DEFAULT);

        // Set the default role for new users
        $role = 'user';

        // Prepare SQL query to check if the email already exists
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE user_email = ?");

        // Bind the email parameter to the prepared query
        $stmt->bind_param("s", $email);

        // Execute the email-check query
        $stmt->execute();

        // Store the result of the query
        $stmt->store_result();

        // Check if the email already exists in the database
        if ($stmt->num_rows > 0) {
            // Set error message if email is already registered
            $message = "Email is already registered!";
        } else {
            // Close the previous prepared statement
            $stmt->close();

            // Prepare SQL query to insert a new user
            $stmt = $conn->prepare(
                "INSERT INTO users (username, user_email, user_password, role) VALUES (?, ?, ?, ?)"
            );

            // Bind user input values to the insert query
            $stmt->bind_param("ssss", $username, $email, $hashed, $role);

            // Execute the insert query
            if ($stmt->execute()) {
                // Set success message if account is created
                $message = "Account created successfully!";
            } else {
                // Set error message if insertion fails
                $message = "Error: " . $stmt->error;
            }
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
    <title>e-Lakbay | Sign Up</title>
    <link rel="stylesheet" href="../css/signIn.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>

<header class="navbar">
    <div class="logo">e-Lakbay</div>
    <nav>
        <a href="../pages/index.html">Home</a>
        <a href="#">Destination</a>
        <a href="#">Products</a>
        <a href="#">Search</a>
    </nav>
</header>

<section class="hero">
    <div class="content">
        <h1\>Explore<br><span>2nd District of</span><br><strong>Ilocos Sur</strong></h1>
        <p>“Explore, taste, and enjoy the culture of every town.”</p>
    </div>

    <div class="form-box">
        <h2>Sign In</h2>
        <p class="sub">"Enter Your Details to Get Started"</p>

        <?php if($message): ?>
            <p class="msg"><?php echo $message; ?></p>
        <?php endif; ?>

        <form method="POST">
            <input type="text" name="municipality" placeholder="Enter Municipality Name" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Enter Your Password" required>
            <input type="password" name="confirm" placeholder="Confirm Your Password" required>

            <button type="submit">Sign Up</button>
        </form>

        <p class="login-link">Already have an account? <a href="../php/logIn.php">Login</a></p>
    </div>
</section>

</body>
</html>
