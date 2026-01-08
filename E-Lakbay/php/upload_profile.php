<?php
session_start();
include "db.php";

$user_id = $_SESSION['user_id'];

$file = $_FILES['profile_pic'];
$filename = time() . "_" . $file['name'];
$path = "uploads/" . $filename;

move_uploaded_file($file['tmp_name'], $path);

$stmt = $conn->prepare("UPDATE users SET profile_pic = ? WHERE user_id = ?");
$stmt->bind_param("si", $filename, $user_id);
$stmt->execute();

$_SESSION['profile_pic'] = $filename;

header("Location: user_dashboard.php");
