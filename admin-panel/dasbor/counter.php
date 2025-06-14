<?php
header('Content-Type: application/json');
require_once '../../db.php'; // $conn adalah objek PDO

$response = [];

try {
    // Jumlah chat_history
    $stmtChat = $conn->query("SELECT COUNT(*) as total FROM chat_history");
    $chatRow = $stmtChat->fetch(PDO::FETCH_ASSOC);
    $response['jumlahPercakapan'] = $chatRow['total'];

    // Jumlah dictionary
    $stmtDict = $conn->query("SELECT COUNT(*) as total FROM dictionary");
    $dictRow = $stmtDict->fetch(PDO::FETCH_ASSOC);
    $response['jumlahKosakata'] = $dictRow['total'];

    echo json_encode($response);
} catch (PDOException $e) {
    echo json_encode([
        'error' => true,
        'message' => 'Terjadi kesalahan: ' . $e->getMessage()
    ]);
}
?>
