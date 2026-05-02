<?php
session_start();
include 'users.php'; // Load user credentials

function login($username, $password) {
    global $users;

    // Validate user credentials
    if (isset($users[$username]) && hash_equals($users[$username], $password)) {
        $_SESSION["user"] = $username;
        return true;
    }
    return false;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"] ?? '');
    $password = trim($_POST["password"] ?? '');

    if (login($username, $password)) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "message" => "Invalid username or password."]);
    }
    exit();
}
?>