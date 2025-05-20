<?php
header('Content-Type: application/json');
require_once '../../db.php'; // Koneksi ke database

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'ID data tidak ditemukan']);
    exit;
}

$id = intval($input['id']);

// Ambil nama file audio sebelum menghapus
$getAudio = $conn->prepare("SELECT audio_menawi FROM dictionary WHERE id = ?");
$getAudio->bind_param("i", $id);
$getAudio->execute();
$result = $getAudio->get_result();
$audioFile = '';

if ($row = $result->fetch_assoc()) {
    $audioFile = $row['audio_menawi'];
}
$getAudio->close();

// Lanjut hapus data dari database
$sql = "DELETE FROM dictionary WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    // Hapus file audio jika ada
    if (!empty($audioFile)) {
        $filePath = "../../" . $audioFile; // Pastikan path sesuai
        if (file_exists($filePath)) {
            unlink($filePath); // Hapus file
        }
    }

    echo json_encode(['success' => true, 'message' => 'Data dan audio berhasil dihapus']);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Gagal menghapus data: ' . $stmt->error]);
}

$stmt->close();
?>
