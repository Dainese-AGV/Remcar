<?php
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

$configPath = __DIR__ . '/config.php';
if (!file_exists($configPath)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server not configured']);
    exit;
}

$config = require $configPath;

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid JSON']);
    exit;
}

// Honeypot — если заполнено, молча «успех» (бот думает, что прошёл)
if (!empty($input['website'])) {
    echo json_encode(['success' => true]);
    exit;
}

$name    = trim($input['name'] ?? '');
$phone   = trim($input['phone'] ?? '');
$comment = trim($input['comment'] ?? '');

if (mb_strlen($name) < 2 || mb_strlen($name) > 100) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid name']);
    exit;
}

$phoneDigits = preg_replace('/\D/', '', $phone);
if (strlen($phoneDigits) < 9 || strlen($phoneDigits) > 15) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid phone']);
    exit;
}

if (mb_strlen($comment) > 1000) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Comment too long']);
    exit;
}

$consent = $input['consent'] ?? false;
if ($consent !== true) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Consent required']);
    exit;
}

// Rate limit по IP
$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$rateFile = sys_get_temp_dir() . '/sto_callback_' . md5($ip) . '.json';
$now = time();
$requests = [];

if (file_exists($rateFile)) {
    $requests = json_decode(file_get_contents($rateFile), true) ?: [];
    $requests = array_filter($requests, fn($t) => $t > $now - 3600);
}

if (count($requests) >= ($config['rate_limit'] ?? 5)) {
    http_response_code(429);
    echo json_encode(['success' => false, 'error' => 'Too many requests']);
    exit;
}

$requests[] = $now;
file_put_contents($rateFile, json_encode($requests));

$safeName    = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
$safePhone   = htmlspecialchars($phone, ENT_QUOTES, 'UTF-8');
$safeComment = htmlspecialchars($comment, ENT_QUOTES, 'UTF-8');

$message = "Новая заявка с сайта\n\n";
$message .= "Имя: {$name}\n";
$message .= "Телефон: {$phone}\n";
if ($comment) {
    $message .= "Комментарий: {$comment}\n";
}
$message .= "\nСогласие на обработку и передачу в Telegram: да";
$message .= "\n" . date('d.m.Y H:i');

$telegramOk = sendTelegram($config, $message);
$emailOk    = sendEmail($config, $safeName, $safePhone, $safeComment);

if (!$telegramOk && !$emailOk) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Delivery failed']);
    exit;
}

echo json_encode(['success' => true]);

function sendTelegram(array $config, string $message): bool
{
    $token  = $config['telegram_bot_token'] ?? '';
    $chatId = $config['telegram_chat_id'] ?? '';

    if (!$token || !$chatId || $token === 'YOUR_BOT_TOKEN_HERE') {
        return false;
    }

    $url = "https://api.telegram.org/bot{$token}/sendMessage";
    $payload = [
        'chat_id' => $chatId,
        'text'    => $message,
    ];

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($payload),
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 10,
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) {
        error_log("Telegram send failed: HTTP {$httpCode}, response: {$response}");
        return false;
    }

    return true;
}

function sendEmail(array $config, string $name, string $phone, string $comment): bool
{
    $to      = $config['email_to'] ?? '';
    $from    = $config['email_from'] ?? '';
    $subject = $config['email_subject'] ?? 'Новая заявка';

    if (!$to || !$from) {
        return false;
    }

    $body  = "Новая заявка с сайта\n\n";
    $body .= "Имя: {$name}\n";
    $body .= "Телефон: {$phone}\n";
    if ($comment) {
        $body .= "Комментарий: {$comment}\n";
    }
    $body .= "\nСогласие на обработку и передачу в Telegram: да";
    $body .= "\nДата: " . date('d.m.Y H:i');

    $headers = "From: {$from}\r\nContent-Type: text/plain; charset=UTF-8";

    $result = mail($to, $subject, $body, $headers);
    if (!$result) {
        error_log("Email send failed to {$to}");
    }
    return $result;
}
