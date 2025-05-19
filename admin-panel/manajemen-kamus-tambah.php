<?php
header('Content-Type: application/json');
require_once '../db.php'; // sesuaikan path koneksi DB

// Pastikan folder "audio" ada dan bisa ditulis
$uploadDir = '../audio/'; // folder penyimpanan relatif dari lokasi script ini

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Cek input teks
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

// Handle upload file audio jika ada
$audioPath = '';
if (isset($_FILES['audio_menawi']) && $_FILES['audio_menawi']['error'] == UPLOAD_ERR_OK) {
    $fileTmpPath = $_FILES['audio_menawi']['tmp_name'];
    $fileName = basename($_FILES['audio_menawi']['name']);
    $fileSize = $_FILES['audio_menawi']['size'];
    $fileType = $_FILES['audio_menawi']['type'];

    // Bisa tambah validasi tipe file audio dan ukuran file di sini
    $allowedExtensions = ['mp3', 'wav', 'ogg', 'm4a'];
    $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    if (!in_array($ext, $allowedExtensions)) {
        http_response_code(400);
        echo json_encode(['error' => 'Format file audio tidak didukung']);
        exit;
    }

    // Buat nama file baru agar unik (misal timestamp + original name)
    $newFileName = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $fileName);

    $destPath = $uploadDir . $newFileName;

    if (move_uploaded_file($fileTmpPath, $destPath)) {
        // Simpan path relatif ke DB, contoh: audio/namafile.mp3
        $audioPath = 'audio/' . $newFileName;
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Gagal menyimpan file audio']);
        exit;
    }
}

// Query insert data ke database
$sql = "INSERT INTO dictionary (indonesia, menawi, audio_menawi) VALUES ('$indonesia', '$menawi', '$audioPath')";

if ($conn->query($sql) === TRUE) {
    echo json_encode(['success' => true, 'message' => 'Data berhasil ditambahkan']);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Gagal menambahkan data: ' . $conn->error]);
}

$conn->close();
