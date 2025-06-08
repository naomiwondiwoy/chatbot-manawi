<?php
// Koneksi database
$host = "localhost";
$user = "root";
$pass = "";
$db   = "menawi_chatbot_db"; // ganti dengan nama database Anda


// require_once '../../db.php'; // Koneksi ke database

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Query untuk menghitung jumlah bot_response per tanggal
$sql = "SELECT DATE(created_at) AS tanggal, COUNT(*) AS jumlah 
        FROM chat_history 
        GROUP BY DATE(created_at)
        ORDER BY tanggal ASC";

$result = $conn->query($sql);

$data = [];

while ($row = $result->fetch_assoc()) {
    $data[] = [
        "tanggal" => $row['tanggal'],
        "jumlah"  => (int)$row['jumlah']
    ];
}

header('Content-Type: application/json');
echo json_encode($data);

$conn->close();
?>