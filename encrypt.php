<?php
session_start();
$key = $_SESSION['key'];
$url = $_POST['url'];

// Validate URL
if (!filter_var($url, FILTER_VALIDATE_URL)) {
    die("Invalid URL, motherfucker.");
}

// Encrypt
$cipher = "aes-256-cbc";
$ivlen = openssl_cipher_iv_length($cipher);
$iv = openssl_random_pseudo_bytes($ivlen);
$encrypted = openssl_encrypt($url, $cipher, $key, 0, $iv);
$encrypted_url = base64_encode($iv . $encrypted);

// Redirect to proxy
header("Location: proxy.php?q=" . urlencode($encrypted_url));
exit();
?>
