<?php
session_start();
if (!isset($_SESSION['key'])) {
    $_SESSION['key'] = bin2hex(random_bytes(16));
}
$key = $_SESSION['key'];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Matrix Browser</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
    <div class="browser">
        <div class="toolbar">
            <form method="POST" action="encrypt.php" id="navForm">
                <input type="text" name="url" id="urlBar" placeholder="Enter URL (e.g., https://example.com)" value="<?php echo isset($_GET['url']) ? htmlspecialchars($_GET['url']) : ''; ?>">
                <button type="submit">Go</button>
            </form>
        </div>
        <div class="content" id="contentArea">
            <h1>Welcome to Matrix Browser</h1>
            <p>Enter a URL above to start browsing privately.</p>
        </div>
    </div>
</body>
</html>
