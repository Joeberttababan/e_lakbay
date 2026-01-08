<?php
session_start();
include "db.php";

$content = trim($_POST['content']);
$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("INSERT INTO posts (user_id, content) VALUES (?, ?)");
$stmt->bind_param("is", $user_id, $content);
$stmt->execute();

header("Location: user_dashboard.php");
