<?php
header('Content-Type: application/json');
require_once '../db.php'; // Pastikan $conn adalah objek mysqli

$response = [];

try {
    // Jumlah chat_history
    $resultChat = $conn->query("SELECT COUNT(*) as total FROM chat_history");
    $chatRow = $resultChat->fetch_assoc();
    $response['jumlahPercakapan'] = $chatRow['total'];

    // Jumlah dictionary
    $resultDict = $conn->query("SELECT COUNT(*) as total FROM dictionary");
    $dictRow = $resultDict->fetch_assoc();
    $response['jumlahKosakata'] = $dictRow['total'];

    echo json_encode($response);
} catch (Exception $e) {
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage()
    ]);
}
?>
