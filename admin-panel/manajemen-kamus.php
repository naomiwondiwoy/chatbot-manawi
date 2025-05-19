<?php
header('Content-Type: application/json');
require_once '../db.php'; // Koneksi ke database

// Query untuk mengambil data pesan
$sql = "SELECT id, indonesia, menawi, audio_menawi FROM dictionary ORDER BY indonesia ASC";
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
