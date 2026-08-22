<?php
session_start();
$key = $_SESSION['key'];

// Handle token generation
if (isset($_GET['action']) && $_GET['action'] === 'encrypt') {
    $url = $_POST['url'];
    if (!preg_match('/^https?:\/\//i', $url)) {
        $url = 'https://' . $url;
    }
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
    $token = bin2hex(random_bytes(8)); // 16 chars
    $_SESSION['tokens'][$token] = $encrypted_url;
    echo json_encode(['token' => $token]);
    exit();
}

// Handle proxy request via token
if (isset($_GET['t'])) {
    $token = $_GET['t'];
    if (!isset($_SESSION['tokens'][$token])) {
        http_response_code(400);
        echo 'Invalid token';
        exit();
    }
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
    // Fetch content
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36");
    $content = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $content_type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    curl_close($ch);
    // For HTML, rewrite links to use token
    if (strpos($content_type, 'text/html') !== false) {
        // Rewrite href and src
        $content = preg_replace_callback('/href="(https?:\/\/[^"]+)"/i', function($matches) use ($key, $cipher, $ivlen) {
            $iv = openssl_random_pseudo_bytes($ivlen);
            $enc = openssl_encrypt($matches[1], $cipher, $key, 0, $iv);
            $enc_url = base64_encode($iv . $enc);
            $token = bin2hex(random_bytes(8));
            $_SESSION['tokens'][$token] = $enc_url;
            return 'href="proxy.php?t=' . $token . '"';
        }, $content);
        $content = preg_replace_callback('/src="(https?:\/\/[^"]+)"/i', function($matches) use ($key, $cipher, $ivlen) {
            $iv = openssl_random_pseudo_bytes($ivlen);
            $enc = openssl_encrypt($matches[1], $cipher, $key, 0, $iv);
            $enc_url = base64_encode($iv . $enc);
            $token = bin2hex(random_bytes(8));
            $_SESSION['tokens'][$token] = $enc_url;
            return 'src="proxy.php?t=' . $token . '"';
        }, $content);
        // Also rewrite forms? Skipping for simplicity.
    }
    // Set headers
    if ($content_type) header("Content-Type: $content_type");
    echo $content;
    exit();
}

// Default: return error
http_response_code(400);
echo 'Bad request';
?>
