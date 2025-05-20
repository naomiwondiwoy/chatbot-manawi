<?php
header('Content-Type: application/json; charset=utf-8');
require_once 'aiml_parser.php';
require_once 'db.php';

// api.groq.com token and model name
$apiToken = getenv('API_TOKEN');
$model = getenv('MODEL_NAME');

function getMessagesFromDB($conn) {
    $messages = [];

    $sql = "SELECT role, content FROM ai_training_messages ORDER BY id ASC";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $messages[] = [
                'role' => $row['role'],
                'content' => $row['content']
            ];
        }
    }

    return $messages;
}

$message = isset($_POST['message']) ? $_POST['message'] : '';
$message = trim($message);

if (!$message) {
    echo json_encode(['error' => 'Silakan ketik pesan terlebih dahulu.']);
    exit;
}

// Cek dulu respons AIML
$response = parseAIML($message);

if ($response === null) {
    // Fallback: Panggil Groq API
    $messages = getMessagesFromDB($conn);

    $systemMessage = [
        'role' => 'system',
        'content' => 'Jawablah hanya berdasarkan data training yang sudah diberikan dari database. Jangan jawab di luar konteks tersebut.'
    ];
    array_unshift($messages, $systemMessage);

    $messages[] = [
        'role' => 'user',
        'content' => $message
    ];

    $payload = [
        'model' => $model,
        'messages' => $messages,
        'temperature' => 0.3,
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

    if ($result === false) {
        echo json_encode(['error' => 'Gagal terhubung ke Groq API: ' . curl_error($ch)]);
        curl_close($ch);
        exit;
    }

    curl_close($ch);

    $responseData = json_decode($result, true);

    if (isset($responseData['error'])) {
        echo json_encode(['error' => 'Groq API error: ' . $responseData['error']['message']]);
        exit;
    }

    if (!empty($responseData['choices'][0]['message']['content'])) {
        $tempResponse = trim($responseData['choices'][0]['message']['content']);

        // Validasi: pastikan isi jawaban ada dalam data training
        $trainingData = getMessagesFromDB($conn);
        $found = false;

        foreach ($trainingData as $msg) {
            if (stripos($tempResponse, $msg['content']) !== false) {
                $found = true;
                break;
            }
        }

        if ($found) {
            $response = $tempResponse;
        } else {
            $response = "Maaf, saya belum memiliki data yang cukup untuk menjawabnya.";
        }

    } else {
        $response = "Maaf, saya belum bisa merespons saat ini.";
    }
}

// Simpan ke database
$stmt = $conn->prepare("INSERT INTO chat_history (user_message, bot_response) VALUES (?, ?)");
$stmt->bind_param('ss', $message, $response);
$stmt->execute();
$stmt->close();

// Kirim balasan ke frontend
echo json_encode(['reply' => $response]);
