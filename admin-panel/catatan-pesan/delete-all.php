<?php
header('Content-Type: application/json');
require_once '../../db.php'; // Sesuaikan path jika perlu

try {
    $sql = "DELETE FROM chat_history";
    if ($conn->query($sql) === TRUE) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => $conn->error
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
