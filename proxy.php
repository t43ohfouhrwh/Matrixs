<?php
session_start();
header('Content-Type: text/html; charset=utf-8');

if (!isset($_GET['t'])) {
    http_response_code(400);
    echo 'Missing token';
    exit();
}

$token = $_GET['t'];
if (!isset($_SESSION['tokens'][$token])) {
    http_response_code(400);
    echo 'Invalid or expired token';
    exit();
}

$key = $_SESSION['key'];
$encrypted_url = $_SESSION['tokens'][$token];
$decoded = base64_decode($encrypted_url);
$cipher = "aes-256-cbc";
$ivlen = openssl_cipher_iv_length($cipher);
$iv = substr($decoded, 0, $ivlen);
$encrypted = substr($decoded, $ivlen);
$url = openssl_decrypt($encrypted, $cipher, $key, 0, $iv);

if (!$url) {
    http_response_code(400);
    echo 'Decryption failed';
    exit();
}

// Fetch the page
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36");
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
$content = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$content_type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
curl_close($ch);

if ($http_code !== 200) {
    echo "<p>Error fetching page (HTTP $http_code)</p>";
    exit();
}

// Rewrite links
$content = preg_replace_callback(
    '/(href|src|action)=["\'](https?:\/\/[^"\']+)["\']/i',
    function($matches) use ($key, $cipher, $ivlen) {
        $iv = openssl_random_pseudo_bytes($ivlen);
        $enc = openssl_encrypt($matches[2], $cipher, $key, 0, $iv);
        $enc_url = base64_encode($iv . $enc);
        $token = bin2hex(random_bytes(8));
        $_SESSION['tokens'][$token] = $enc_url;
        return $matches[1] . '="proxy.php?t=' . $token . '"';
    },
    $content
);

// Add base tag to fix relative links
$base_tag = '<base href="' . htmlspecialchars($url) . '">';
$content = str_replace('<head>', "<head>\n$base_tag", $content);

echo $content;
?>
