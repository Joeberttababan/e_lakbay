<?php
session_start();
include "db.php";

$post_id = $_POST['post_id'];
$comment = trim($_POST['comment']);
$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("INSERT INTO comments (post_id, user_id, comment) VALUES (?, ?, ?)");
$stmt->bind_param("iis", $post_id, $user_id, $comment);
$stmt->execute();

header("Location: user_dashboard.php");
