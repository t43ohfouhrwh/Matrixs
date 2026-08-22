<?php
session_start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['url'])) {
    echo json_encode(['error' => 'Bad request']);
    exit();
}

$key = $_SESSION['key'];
$url = trim($_POST['url']);

// Add https if missing
if (!preg_match('/^https?:\/\//i', $url)) {
    $url = 'https://' . $url;
}

// Validate
if (!filter_var($url, FILTER_VALIDATE_URL)) {
    echo json_encode(['error' => 'Invalid URL']);
    exit();
}

// Encrypt
$cipher = "aes-256-cbc";
$ivlen = openssl_cipher_iv_length($cipher);
$iv = openssl_random_pseudo_bytes($ivlen);
$encrypted = openssl_encrypt($url, $cipher, $key, 0, $iv);
$encrypted_url = base64_encode($iv . $encrypted);

// Generate short token
$token = bin2hex(random_bytes(8));
$_SESSION['tokens'][$token] = $encrypted_url;

// Limit token storage to prevent memory issues
if (count($_SESSION['tokens']) > 100) {
    array_shift($_SESSION['tokens']);
}

echo json_encode(['token' => $token]);
?>
