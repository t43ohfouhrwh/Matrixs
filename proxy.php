<?php
session_start();
$key = $_SESSION['key'];

if (!isset($_GET['q'])) {
    die("No query parameter, dipshit.");
}

$encrypted_url = base64_decode($_GET['q']);
$cipher = "aes-256-cbc";
$ivlen = openssl_cipher_iv_length($cipher);
$iv = substr($encrypted_url, 0, $ivlen);
$encrypted = substr($encrypted_url, $ivlen);
$url = openssl_decrypt($encrypted, $cipher, $key, 0, $iv);

if (!$url) {
    die("Decryption failed. You broke it, asshole.");
}

// Fetch the content
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36");
$content = curl_exec($ch);
curl_close($ch);

// Rewrite absolute links to use proxy (basic shit)
$content = preg_replace('/href="(https?:\/\/[^"]+)"/i', 'href="proxy.php?q=' . urlencode(base64_encode($iv . openssl_encrypt('$1', $cipher, $key, 0, $iv))) . '"', $content);

echo $content;
?>
