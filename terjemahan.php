<?php
require_once 'env.php';
loadEnv(__DIR__ . '/.env');

// Koneksi ke database dengan PDO
$host = getenv('DB_HOST');
$user = getenv('DB_USER');
$pass = getenv('DB_PASS');
$dbname = getenv('DB_NAME');

$dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";

try {
    $conn = new PDO($dsn, $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Koneksi gagal: " . $e->getMessage());
}

// Ambil input dari client
$keyword = isset($_POST['keyword']) ? trim($_POST['keyword']) : '';

$response = ['status' => 'not_found'];

if (!empty($keyword)) {
    $stmt = $conn->prepare("SELECT menawi, audio_menawi FROM dictionary WHERE indonesia LIKE :keyword LIMIT 1");
    $search = '%' . $keyword . '%';
    $stmt->bindParam(':keyword', $search, PDO::PARAM_STR);
    $stmt->execute();

    if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $response = [
            'status' => 'found',
            'menawi' => $row['menawi'],
            'audio' => $row['audio_menawi']
        ];
    }
}

header('Content-Type: application/json');
echo json_encode($response);

// Simpan log ke file (untuk debug jika perlu)
file_put_contents('log.json', json_encode([
    'keyword' => $keyword,
    'response' => $response
], JSON_PRETTY_PRINT));
