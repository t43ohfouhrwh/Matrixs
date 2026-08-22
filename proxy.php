<?php
session_start();
$key = $_SESSION['key'];

if (!isset($_GET['q'])) {
    header("Location: index.php");
    exit();
}

$encrypted_url = base64_decode($_GET['q']);
$cipher = "aes-256-cbc";
$ivlen = openssl_cipher_iv_length($cipher);
$iv = substr($encrypted_url, 0, $ivlen);
$encrypted = substr($encrypted_url, $ivlen);
$url = openssl_decrypt($encrypted, $cipher, $key, 0, $iv);

if (!$url) {
    header("Location: index.php?error=invalid");
    exit();
}

// Fetch the content
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36");
$content = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// If the page is HTML, wrap it in the browser frame
if (strpos($content, '<html') !== false || strpos($content, '<HTML') !== false) {
    // Rewrite links to use proxy
    $content = preg_replace('/href="(https?:\/\/[^"]+)"/i', 'href="proxy.php?q=' . urlencode(base64_encode($iv . openssl_encrypt('$1', $cipher, $key, 0, $iv))) . '"', $content);
    $content = preg_replace('/src="(https?:\/\/[^"]+)"/i', 'src="proxy.php?q=' . urlencode(base64_encode($iv . openssl_encrypt('$1', $cipher, $key, 0, $iv))) . '"', $content);
    
    // Inject the browser toolbar at the top
    $toolbar = '
    <div style="position:fixed;top:0;left:0;right:0;z-index:9999;background:#0a0a0a;border-bottom:2px solid #00ff00;padding:10px;">
        <form method="POST" action="encrypt.php" style="display:flex;gap:10px;max-width:1200px;margin:0 auto;">
            <input type="text" name="url" value="' . htmlspecialchars($url) . '" style="flex:1;padding:8px;background:#111;border:1px solid #00ff00;color:#00ff00;font-family:monospace;">
            <button type="submit" style="padding:8px 16px;background:#00ff00;color:#000;border:none;cursor:pointer;font-family:monospace;">Go</button>
            <a href="index.php" style="padding:8px 16px;background:#333;color:#00ff00;text-decoration:none;border:1px solid #00ff00;font-family:monospace;">New Tab</a>
        </form>
    </div>
    <div style="margin-top:60px;">
    ';
    
    $content = str_replace('<body', $toolbar . '<body', $content);
    $content = str_replace('<body>', $toolbar . '<body>', $content);
    
    echo $content;
} else {
    // Non-HTML content (images, CSS, etc.)
    header("Content-Type: " . curl_getinfo($ch, CURLINFO_CONTENT_TYPE));
    echo $content;
}
?>
