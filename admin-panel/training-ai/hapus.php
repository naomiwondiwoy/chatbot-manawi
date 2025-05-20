<?php
header('Content-Type: application/json');
require_once '../../db.php'; // Koneksi ke database

// Ambil data JSON dari body request
$input = json_decode(file_get_contents('php://input'), true);

// Validasi ID
if (!isset($input['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'ID data tidak ditemukan']);
    exit;
}

$id = intval($input['id']);

// Hapus data dari tabel ai_training_messages
$sql = "DELETE FROM ai_training_messages WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Data berhasil dihapus']);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Gagal menghapus data: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
