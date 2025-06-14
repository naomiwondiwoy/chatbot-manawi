<?php
header('Content-Type: application/json');
require_once '../../db.php'; // $conn adalah objek PDO

try {
    $sql = "DELETE FROM chat_history";
    $stmt = $conn->prepare($sql);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Gagal menghapus data'
        ]);
    }
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
