<?php
header('Content-Type: application/json');
require_once '../../db.php'; // Koneksi ke database

// Ambil data JSON dari body request
$input = json_decode(file_get_contents('php://input'), true);

// Validasi ID
if (!isset($input['id']) || !is_numeric($input['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'ID data tidak valid atau tidak ditemukan']);
    exit;
}

$id = intval($input['id']);

try {
    // Siapkan dan eksekusi statement
    $stmt = $conn->prepare("DELETE FROM ai_training_messages WHERE id = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Data berhasil dihapus']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Gagal menghapus data']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Kesalahan database: ' . $e->getMessage()]);
}
