<?php
header('Content-Type: application/json');
require_once '../../db.php'; // Koneksi PDO

$uploadDir = '../../audio/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

if (
    !isset($_POST['indonesia']) || empty(trim($_POST['indonesia'])) ||
    !isset($_POST['menawi']) || empty(trim($_POST['menawi']))
) {
    http_response_code(400);
    echo json_encode(['error' => 'Field indonesia dan menawi wajib diisi']);
    exit;
}

$indonesia = trim($_POST['indonesia']);
$menawi = trim($_POST['menawi']);
$id = isset($_POST['id']) ? intval($_POST['id']) : 0;

$audioPath = '';
if (isset($_FILES['audio_menawi']) && $_FILES['audio_menawi']['error'] == UPLOAD_ERR_OK) {
    $fileTmpPath = $_FILES['audio_menawi']['tmp_name'];
    $fileName = basename($_FILES['audio_menawi']['name']);
    $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    $allowedExtensions = ['mp3', 'wav', 'ogg', 'm4a'];

    if (!in_array($ext, $allowedExtensions)) {
        http_response_code(400);
        echo json_encode(['error' => 'Format file audio tidak didukung']);
        exit;
    }

    $newFileName = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $fileName);
    $destPath = $uploadDir . $newFileName;

    if (move_uploaded_file($fileTmpPath, $destPath)) {
        $audioPath = 'audio/' . $newFileName;
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Gagal menyimpan file audio']);
        exit;
    }
}

try {
    if ($id > 0) {
        // Update
        if (!empty($audioPath)) {
            $sql = "UPDATE dictionary SET indonesia = :indonesia, menawi = :menawi, audio_menawi = :audio WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':indonesia' => $indonesia,
                ':menawi' => $menawi,
                ':audio' => $audioPath,
                ':id' => $id
            ]);
        } else {
            $sql = "UPDATE dictionary SET indonesia = :indonesia, menawi = :menawi WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':indonesia' => $indonesia,
                ':menawi' => $menawi,
                ':id' => $id
            ]);
        }

        echo json_encode(['success' => true, 'message' => 'Data berhasil diperbarui']);
    } else {
        // Insert
        $sql = "INSERT INTO dictionary (indonesia, menawi, audio_menawi) VALUES (:indonesia, :menawi, :audio)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':indonesia' => $indonesia,
            ':menawi' => $menawi,
            ':audio' => $audioPath
        ]);

        echo json_encode(['success' => true, 'message' => 'Data berhasil ditambahkan']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>
