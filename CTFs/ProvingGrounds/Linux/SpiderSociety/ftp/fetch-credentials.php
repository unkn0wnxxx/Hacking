

<?php
// fetch-credentials.php

// Path to the credentials file
$credentialsFile = __DIR__ . '/.fuhfjkzbdsfuybefzmdbbzdcbhjzdbcukbdvbsdvuibdvnbdvenv';

// Function to parse the credentials file
function getCredentials($filePath) {
    if (!file_exists($filePath)) {
        die('Credentials file not found.');
    }

    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $credentials = [];

    foreach ($lines as $line) {
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $credentials[trim($key)] = trim($value);
        }
    }

    return $credentials;
}

// Fetch credentials
$credentials = getCredentials($credentialsFile);
$user = $credentials['FTP_BACKUP_USER'] ?? null;
$pass = $credentials['FTP_BACKUP_PASS'] ?? null;

// Check if the request is coming from contro-panel.php
/* if ($_SERVER['HTTP_REFERER'] !== 'http://yourdomain.com/contro-panel.php') {
    http_response_code(403);
    die('Unauthorized access.');
} */

// Check if the user is authenticated
session_start();
if (!isset($_SESSION['user'])) {
    http_response_code(401);
    die('User not authenticated.');
}

// Respond with the credentials
header('Content-Type: application/json');
echo json_encode([
    'FTP_BACKUP_USER' => $user,
    'FTP_BACKUP_PASS' => $pass
]);