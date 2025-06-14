<?php
require_once 'db.php';

$username = 'admin';
$password = password_hash('admin123', PASSWORD_BCRYPT);

$stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (:username, :password)");
$stmt->bindParam(':username', $username);
$stmt->bindParam(':password', $password);
$stmt->execute();

echo "User admin berhasil ditambahkan.";
?>
