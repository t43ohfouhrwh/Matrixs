<?php
session_start();
if (!isset($_SESSION['key'])) {
    $_SESSION['key'] = bin2hex(random_bytes(16));
}
$key = $_SESSION['key'];
// Generate short token mapping
if (!isset($_SESSION['tokens'])) {
    $_SESSION['tokens'] = [];
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Matrix</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
    <div id="app">
        
        <div id="toolbar">
            <div id="tabs-container">
                <div class="tab active" data-tab="0">Tab 1</div>
                <button id="new-tab">+</button>
            </div>
            <div id="nav-bar">
                <button id="back" disabled>←</button>
                <button id="forward" disabled>→</button>
                <button id="refresh">↻</button>
                <form id="url-form">
                    <input type="text" id="url-input" placeholder="Enter URL (e.g., google.com)">
                    <button type="submit">Go</button>
                </form>
                <button id="about-blank-btn" title="Open in new tab (disguised)">📚</button>
            </div>
        </div>
        
        <div id="content">
            <iframe id="main-frame" src="about:blank"></iframe>
        </div>
    </div>
    <script src="script.js"></script>
</body>
</html>
