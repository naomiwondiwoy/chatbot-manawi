<?php
// Koneksi database
$host = "localhost";
$user = "root";
$pass = "";
$db   = "menawi_chatbot_db";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Query untuk menghitung jumlah berdasarkan user_message
$sql = "SELECT user_message, COUNT(*) AS jumlah 
        FROM chat_history 
        GROUP BY user_message 
        ORDER BY jumlah DESC 
        LIMIT 10"; // Tampilkan 10 kelompok teratas

$result = $conn->query($sql);

$data = [];

while ($row = $result->fetch_assoc()) {
    $data[] = [
        "label" => $row['user_message'],
        "jumlah" => (int)$row['jumlah']
    ];
}

header('Content-Type: application/json');
echo json_encode($data);

$conn->close();
?>
