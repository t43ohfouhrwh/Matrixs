<?php
session_start();
if (!isset($_SESSION['key'])) {
    $_SESSION['key'] = bin2hex(random_bytes(16)); // 256-bit key
}
$key = $_SESSION['key'];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Matrix Proxy</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Matrix Proxy</h1>
        <form method="POST" action="encrypt.php">
            <input type="text" name="url" placeholder="Enter URL (e.g., https://example.com)" required>
            <button type="submit">Encrypt & Go</button>
        </form>
        <p>Your encrypted URLs are safe... mostly.</p>
    </div>
</body>
</html>
