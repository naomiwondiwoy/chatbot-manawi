<?php
// db.php - file koneksi database
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'menawi_chatbot_db';

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
