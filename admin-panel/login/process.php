<?php


// $hashed = password_hash("admin123", PASSWORD_BCRYPT);
// echo $hashed;


// Set header untuk JSON response
header('Content-Type: application/json');

// Ambil data JSON dari JavaScript
$data = json_decode(file_get_contents('php://input'), true);

// Cek data
if (!isset($data['username']) || !isset($data['password'])) {
    echo json_encode(['success' => false, 'message' => 'Data tidak lengkap']);
    exit;
}

$username = $data['username'];
$password = $data['password'];

require_once '../../db.php'; // Koneksi ke database

// Query cari user
$stmt = $conn->prepare("SELECT password FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 1) {
    $stmt->bind_result($hashed_password);
    $stmt->fetch();

    if (password_verify($password, $hashed_password)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Password salah']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Username tidak ditemukan']);
}

$stmt->close();
$conn->close();
?>
