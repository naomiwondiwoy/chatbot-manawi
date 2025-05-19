<?php
header('Content-Type: application/json; charset=utf-8');
require_once 'aiml_parser.php';
require_once 'db.php';

$apiToken = getenv('API_TOKEN');
$model = getenv('MODEL_NAME');

$message = isset($_POST['message']) ? $_POST['message'] : '';
$message = trim($message);

if (!$message) {
    echo json_encode(['error' => 'Silakan ketik pesan terlebih dahulu.']);
    exit;
}

// Cek dulu respons AIML
$response = parseAIML($message);

if ($response === null) {
    // Kalau AIML tidak ketemu, panggil Groq API

    $payload = [
        'model' => $model,
        'messages' => [
            [
                'role' => 'system',
                'content' => 'Kamu adalah ChatBot AI Belajar Bahasa Menawi yang ramah dan singkat. Fokus membantu belajar Bahasa Menawi (Papua). Jangan memberikan jawaban panjang dan terlalu meluas.'
            ],
            [
                'role' => 'system',
                'content' => 'Kamu selalu senang membantu orang belajar Bahasa Menawi. Jika ada yang bertanya tentang hal lain, jawab dengan singkat dan langsung ke intinya.'
            ],
            [
                'role' => 'system',
                'content' => 'Kamu selalu mengajak pengguna untuk bertanya lebih banyak lagi untuk belajar bahasa Menawi (Papua).'
            ],
            [
                'role' => 'system',
                'content' => 'anda selalu memberikan jawaban yang berbeda dari jawaban sebelumnya, jika ada yang bertanya sama dan jawabanya diambil dari database AIML'
            ],
            [
                'role' => 'system',
                'content' => 'jika adan ditanya untuk membuat daftar kata, maka kata-kata tersebut diambil dari database AIML'
            ],
            [
                'role' => 'system',
                'content' => 'kamu akan mengatakan tidak tau jika ada yang bertanya diluar konteks bahasa Menawi (Papua)'
            ],
            [
                'role' => 'user',
                'content' => $message
            ]
        ],
        'temperature' => 0.3,     // buat respons lebih terkontrol dan singkat
        'max_tokens' => 150,      // batasi panjang respons
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
        $response = trim($responseData['choices'][0]['message']['content']);
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
