<?php
<<<<<<< HEAD
// db.php - file koneksi database
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'menawi_chatbot_db';
=======
require_once 'env.php';
loadEnv(__DIR__ . '/.env');

$host = getenv('DB_HOST');
$user = getenv('DB_USER');
$pass = getenv('DB_PASS');
$dbname = getenv('DB_NAME');
>>>>>>> 35c12b6309e567e9037e391a341d6e0afab22c7c

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
