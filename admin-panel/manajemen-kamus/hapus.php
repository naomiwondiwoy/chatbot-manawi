<?php
header('Content-Type: application/json');
require_once '../../db.php'; // $conn adalah objek PDO

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'ID data tidak ditemukan']);
    exit;
}

$id = intval($input['id']);

try {
    // Ambil nama file audio sebelum menghapus
    $stmtAudio = $conn->prepare("SELECT audio_menawi FROM dictionary WHERE id = ?");
    $stmtAudio->execute([$id]);
    $audioFile = '';
    
    if ($row = $stmtAudio->fetch(PDO::FETCH_ASSOC)) {
        $audioFile = $row['audio_menawi'];
    }

    // Hapus data dari database
    $stmtDelete = $conn->prepare("DELETE FROM dictionary WHERE id = ?");
    if ($stmtDelete->execute([$id])) {
        // Hapus file audio jika ada
        if (!empty($audioFile)) {
            $filePath = "../../" . $audioFile;
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }

        echo json_encode(['success' => true, 'message' => 'Data dan audio berhasil dihapus']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Gagal menghapus data']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Kesalahan server: ' . $e->getMessage()]);
}
?>
