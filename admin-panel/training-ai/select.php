<?php
header('Content-Type: application/json');
require_once '../../db.php'; // Koneksi ke database

try {
    // Eksekusi query
    $stmt = $conn->query("SELECT * FROM ai_training_messages ORDER BY id DESC");
    
    // Ambil semua data sebagai array asosiatif
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Output dalam format JSON
    echo json_encode($data);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Gagal mengambil data: ' . $e->getMessage()]);
}
