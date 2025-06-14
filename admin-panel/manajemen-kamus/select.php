<?php
header('Content-Type: application/json');
require_once '../../db.php'; // $conn adalah objek PDO

try {
    // Query untuk mengambil data
    $sql = "SELECT id, indonesia, menawi, audio_menawi FROM dictionary ORDER BY indonesia ASC";
    $stmt = $conn->prepare($sql);
    $stmt->execute();

    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($data);
} catch (PDOException $e) {
    echo json_encode([
        'error' => true,
        'message' => 'Gagal mengambil data: ' . $e->getMessage()
    ]);
}
?>
