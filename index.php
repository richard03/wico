<?php
// Get the request URI
$request_uri = $_SERVER['REQUEST_URI'];

// Remove query string if present
if (($pos = strpos($request_uri, '?')) !== false) {
    $request_uri = substr($request_uri, 0, $pos);
}

// Remove trailing slash
$request_uri = rtrim($request_uri, '/');

// If the request starts with /api, handle it as an API request
if (strpos($request_uri, '/api') === 0) {
    require_once __DIR__ . '/api/index.php';
    exit();
}

// Handle frontend routes here
// For now, we'll just show a simple message
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wico</title>
</head>
<body>
    <h1>Vítejte ve Wico</h1>
    <p>API je dostupné na <code>/api</code> endpointu.</p>
</body>
</html> 