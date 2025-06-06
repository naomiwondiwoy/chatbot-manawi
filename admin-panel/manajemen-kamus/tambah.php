<?php
header('Content-Type: application/json');
require_once '../../db.php'; // sesuaikan path koneksi DB

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

$indonesia = $conn->real_escape_string(trim($_POST['indonesia']));
$menawi = $conn->real_escape_string(trim($_POST['menawi']));
$id = isset($_POST['id']) ? intval($_POST['id']) : 0;

$audioPath = '';
if (isset($_FILES['audio_menawi']) && $_FILES['audio_menawi']['error'] == UPLOAD_ERR_OK) {
    $fileTmpPath = $_FILES['audio_menawi']['tmp_name'];
    $fileName = basename($_FILES['audio_menawi']['name']);
    $fileSize = $_FILES['audio_menawi']['size'];
    $fileType = $_FILES['audio_menawi']['type'];

    $allowedExtensions = ['mp3', 'wav', 'ogg', 'm4a'];
    $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

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

if ($id > 0) {
    // Update data
    if (!empty($audioPath)) {
        $sql = "UPDATE dictionary SET indonesia='$indonesia', menawi='$menawi', audio_menawi='$audioPath' WHERE id=$id";
    } else {
        $sql = "UPDATE dictionary SET indonesia='$indonesia', menawi='$menawi' WHERE id=$id";
    }

    if ($conn->query($sql) === TRUE) {
        echo json_encode(['success' => true, 'message' => 'Data berhasil diperbarui']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Gagal memperbarui data: ' . $conn->error]);
    }
} else {
    // Insert data
    $sql = "INSERT INTO dictionary (indonesia, menawi, audio_menawi) VALUES ('$indonesia', '$menawi', '$audioPath')";

    if ($conn->query($sql) === TRUE) {
        echo json_encode(['success' => true, 'message' => 'Data berhasil ditambahkan']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Gagal menambahkan data: ' . $conn->error]);
    }
}

$conn->close();
