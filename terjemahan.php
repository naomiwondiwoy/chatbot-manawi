<?php
require_once 'env.php';
loadEnv(__DIR__ . '/.env');
// Koneksi ke database
$host = getenv('DB_HOST');
$user = getenv('DB_USER');
$pass = getenv('DB_PASS');
$dbname = getenv('DB_NAME');

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}


// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}


// Ambil input dari client
$keyword = isset($_POST['keyword']) ? $_POST['keyword'] : '';

$response = ['status' => 'not_found'];

if (!empty($keyword)) {
    $stmt = $conn->prepare("SELECT menawi, audio_menawi FROM dictionary WHERE indonesia LIKE ?");
    $search = "%" . $keyword . "%";
    $stmt->bind_param("s", $search);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $response = [
            'status' => 'found',
            'menawi' => $row['menawi'],
            'audio' => $row['audio_menawi']
        ];
    }
}

header('Content-Type: application/json');
echo json_encode($response);
file_put_contents('log.json', json_encode([
    'keyword' => $keyword,
    'response' => $response
]));