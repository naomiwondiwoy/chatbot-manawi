<?php
header('Content-Type: application/json');
require_once '../../db.php'; // $conn = PDO

try {
    // Query untuk mengambil data pesan
    $sql = "SELECT id, user_message, bot_response, created_at FROM chat_history ORDER BY created_at DESC";
    $stmt = $conn->query($sql); // Karena tidak ada parameter, langsung pakai query()

    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($data);
} catch (PDOException $e) {
    echo json_encode([
        'error' => true,
        'message' => 'Gagal mengambil data: ' . $e->getMessage()
    ]);
}
?>
