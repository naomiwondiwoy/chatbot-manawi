<?php
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 0); // suppress warning to avoid HTML response
error_reporting(E_ALL);

require_once 'env.php';
require_once 'aiml_parser.php';
require_once 'db.php'; // pastikan $conn sudah siap
loadEnv(__DIR__ . '/.env');

// Ambil konfigurasi
$apiToken = getenv('API_TOKEN') ?: '';
$model = getenv('MODEL_NAME') ?: '';

// Validasi jika variabel belum terisi
if (!$apiToken || !$model) {
    echo json_encode(['error' => 'Konfigurasi model atau token tidak ditemukan.']);
    exit;
}

// Fungsi ambil data training
function getMessagesFromDB($conn) {
    try {
        $sql = "SELECT role, content FROM ai_training_messages ORDER BY id ASC";
        $stmt = $conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}

// Ambil input user
$message = isset($_POST['message']) ? trim($_POST['message']) : '';
if (!$message) {
    echo json_encode(['error' => 'Silakan ketik pesan terlebih dahulu.']);
    exit;
}

// Coba jawab dari AIML parser
$response = parseAIML($message);

// Jika tidak ditemukan di AIML, pakai Groq
if ($response === null) {
    $messages = getMessagesFromDB($conn);

    // Prompt sistem
    array_unshift($messages, [
        'role' => 'system',
        'content' => 'Jawablah dengan data yang sudah ada, tapi jika tidak cukup, kamu boleh memberikan jawaban berdasarkan pengetahuan umum.'
    ]);

    // Tambah input user
    $messages[] = [
        'role' => 'user',
        'content' => $message
    ];

    // Buat payload
    $payload = [
        'model' => $model,
        'messages' => $messages,
        'temperature' => 0.7,
        'max_tokens' => 150
    ];

    // Kirim ke Groq
    $ch = curl_init('https://api.groq.com/openai/v1/chat/completions');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $apiToken,
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

    $result = curl_exec($ch);
    $curlErr = curl_error($ch);
    curl_close($ch);

    if ($result === false) {
        echo json_encode(['error' => 'Gagal terhubung ke Groq API: ' . $curlErr]);
        exit;
    }

    $responseData = json_decode($result, true);

    // ✅ Perbaikan bagian ini
    if (!is_array($responseData) || !isset($responseData['choices'][0]['message']['content'])) {
        $apiError = is_array($responseData) && isset($responseData['error']['message'])
            ? $responseData['error']['message']
            : 'Respon Groq tidak dikenali.';
        echo json_encode(['error' => 'Groq API error: ' . $apiError]);
        exit;
    }

    $response = trim($responseData['choices'][0]['message']['content']);
}

// Simpan ke database
try {
    $stmt = $conn->prepare("INSERT INTO chat_history (user_message, bot_response) VALUES (:user_message, :bot_response)");
    $stmt->bindParam(':user_message', $message);
    $stmt->bindParam(':bot_response', $response);
    $stmt->execute();
} catch (PDOException $e) {
    echo json_encode(['error' => 'Gagal menyimpan riwayat: ' . $e->getMessage()]);
    exit;
}

// Kirim response ke frontend
echo json_encode(['reply' => $response]);
exit;
