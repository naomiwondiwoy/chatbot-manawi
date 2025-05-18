<?php
header('Content-Type: application/json');

// Koneksi ke database
$host = "localhost";
$dbname = "menawi_chatbot_db"; // Ganti dengan nama database Anda
$dbuser = "root"; // Ganti jika perlu
$dbpass = "root"; // Ganti jika perlu

// Buat koneksi
$conn = new mysqli($host, $dbuser, $dbpass, $dbname);

// Cek koneksi
if ($conn->connect_error) {
    die(json_encode(['error' => 'Koneksi gagal: ' . $conn->connect_error]));
}

// Query untuk mengambil data pesan
$sql = "SELECT user_message, bot_response, created_at FROM chat_history ORDER BY created_at DESC";
$result = $conn->query($sql);

$data = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
}

$conn->close();

// Output dalam format JSON
echo json_encode($data);
?>
