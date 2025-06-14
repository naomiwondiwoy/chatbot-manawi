<?php
header('Content-Type: application/json; charset=utf-8');
require_once 'aiml_parser.php';
require_once 'db.php'; // pastikan db.php pakai PDO

$apiToken = getenv('API_TOKEN');
$model = getenv('MODEL_NAME');

// Ambil pesan dari database
function getMessagesFromDB($conn) {
    $sql = "SELECT role, content FROM ai_training_messages ORDER BY id ASC";
    $stmt = $conn->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$message = isset($_POST['message']) ? trim($_POST['message']) : '';

if (!$message) {
    echo json_encode(['error' => 'Silakan ketik pesan terlebih dahulu.']);
    exit;
}

// Coba jawab via AIML
$response = parseAIML($message);

if ($response === null) {
    $messages = getMessagesFromDB($conn);

    // Tambahkan prompt sistem
    array_unshift($messages, [
        'role' => 'system',
        'content' => 'Jawablah dengan data yang sudah ada, tapi jika tidak cukup, kamu boleh memberikan jawaban berdasarkan pengetahuan umum.'
    ]);

    // Tambahkan pesan user
    $messages[] = [
        'role' => 'user',
        'content' => $message
    ];

    // Buat payload ke Groq
    $payload = [
        'model' => $model,
        'messages' => $messages,
        'temperature' => 0.7,
        'max_tokens' => 150
    ];

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

    if (isset($responseData['error'])) {
        echo json_encode(['error' => 'Groq API error: ' . $responseData['error']['message']]);
        exit;
    }

    $response = trim($responseData['choices'][0]['message']['content'] ?? "Maaf, saya belum bisa merespons saat ini.");
}

// Simpan chat ke database
try {
    $stmt = $conn->prepare("INSERT INTO chat_history (user_message, bot_response) VALUES (:user_message, :bot_response)");
    $stmt->bindParam(':user_message', $message);
    $stmt->bindParam(':bot_response', $response);
    $stmt->execute();
} catch (PDOException $e) {
    echo json_encode(['error' => 'Gagal menyimpan riwayat: ' . $e->getMessage()]);
    exit;
}

echo json_encode(['reply' => $response]);
