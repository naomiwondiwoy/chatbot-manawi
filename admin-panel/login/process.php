<?php
header('Content-Type: application/json');

// Ambil data dari request
$data = json_decode(file_get_contents('php://input'), true);

// Validasi input
if (!isset($data['username']) || !isset($data['password'])) {
    echo json_encode(['success' => false, 'message' => 'Data tidak lengkap']);
    exit;
}

$username = $data['username'];
$password = $data['password'];

require_once '../../db.php'; // Sudah pakai PDO, bagus

try {
    $stmt = $conn->prepare("SELECT password FROM users WHERE username = :username");
    $stmt->bindParam(':username', $username);
    $stmt->execute();

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Username atau password salah']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Kesalahan server']);
}
?>
