<?php
header('Content-Type: application/json');
require_once '../../db.php'; // sesuaikan path koneksi DB

// Validasi input
if (
    !isset($_POST['role']) || empty(trim($_POST['role'])) ||
    !isset($_POST['content']) || empty(trim($_POST['content']))
) {
    http_response_code(400);
    echo json_encode(['error' => 'Field role dan content wajib diisi']);
    exit;
}

$role = trim($_POST['role']);
$content = trim($_POST['content']);

try {
    // Prepare statement dengan bind parameter untuk keamanan
    $stmt = $conn->prepare("INSERT INTO ai_training_messages (role, content) VALUES (:role, :content)");
    $stmt->bindParam(':role', $role);
    $stmt->bindParam(':content', $content);

    $stmt->execute();

    echo json_encode(['success' => true, 'message' => 'Data berhasil ditambahkan']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Gagal menambahkan data: ' . $e->getMessage()]);
}
?>
