<?php
header('Content-Type: application/json');
require_once '../../db.php'; // sesuaikan path koneksi DB

// Validasi input
if (
    !isset($_POST['role']) || empty(trim($_POST['role'])) ||
    !isset($_POST['content']) || empty(trim($_POST['content']))
) {
    http_response_code(400);
    echo json_encode(['error' => 'Field role dan content wajib diisi']);
    exit;
}

$role = $conn->real_escape_string(trim($_POST['role']));
$content = $conn->real_escape_string(trim($_POST['content']));

// Insert data ke tabel ai_training_messages
$sql = "INSERT INTO ai_training_messages (role, content) VALUES ('$role', '$content')";

if ($conn->query($sql) === TRUE) {
    echo json_encode(['success' => true, 'message' => 'Data berhasil ditambahkan']);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Gagal menambahkan data: ' . $conn->error]);
}

$conn->close();
?>
