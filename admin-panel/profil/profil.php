<?php
header('Content-Type: application/json');
require_once '../../db.php'; // Koneksi ke database

// Ambil ID user dari URL
$id = 1;

$data = [];

if ($id > 0) {
    // Perbaiki: gunakan parameter ? agar bind_param berfungsi
    $sql = "SELECT id, username FROM users WHERE id = ?";
    
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("i", $id); // "i" = integer
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            $data = $result->fetch_assoc();
        }

        $stmt->close();
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Gagal menyiapkan statement SQL']);
        exit;
    }
}

$conn->close();

// Output JSON
echo json_encode($data);
?>
