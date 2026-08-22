<?php
session_start();
if (!isset($_SESSION['key'])) {
    $_SESSION['key'] = bin2hex(random_bytes(16));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Matrix</title>
    <link rel="stylesheet" type="text/css" href="style.css">
    <style>
        /* Force English */
        * { font-family: 'Segoe UI', Arial, sans-serif !important; }
    </style>
</head>
<body>
    <div id="app">
        
        <div id="toolbar">
            <div id="tabs-container">
                <div class="tab active" data-tab="0">Tab 1</div>
                <button id="new-tab">+</button>
            </div>
            <div id="nav-bar">
                <button id="back" disabled>&#8592;</button>
                <button id="forward" disabled>&#8594;</button>
                <button id="refresh">&#8635;</button>
                <input type="text" id="url-input" placeholder="Enter URL (e.g., google.com)">
                <button id="go-btn">Go</button>
                <button id="about-blank-btn" title="Open in new tab">&#128218;</button>
            </div>
        </div>
        
        <div id="content">
            <iframe id="main-frame" src="about:blank"></iframe>
        </div>
    </div>
    <script src="script.js"></script>
</body>
</html>
