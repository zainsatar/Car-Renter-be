<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Methods: POST');
header('Content-Type: application/json; charset=UTF-8');

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/sendJson.php';
require_once __DIR__ . '/vendor/autoload.php'; // Include JWT library

use Firebase\JWT\JWT;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if (!isset($_POST['email']) || !isset($_POST['password'])) {
        sendJson(422, 'Please provide both email and password.');
    }

    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $password = trim($_POST['password']);

    $sql = "SELECT * FROM `users` WHERE `email`='$email'";
    $query = mysqli_query($conn, $sql);

    if (!$query) {
        sendJson(500, 'Database query error.');
    }

    $user = mysqli_fetch_assoc($query);

    if (!$user || !password_verify($password, $user['password'])) {
        sendJson(401, 'Invalid email or password.');
    }

    // User authenticated successfully, generate JWT
    $secretKey = 'MY_JWT'; 
    $issuedAt = time();
    $expirationTime = $issuedAt + 60 * 60; // JWT token valid for 1 hour

    $payload = array(
        'email' => $user['email'],
        'iat' => $issuedAt,
        'exp' => $expirationTime
    );

    $token = JWT::encode($payload, $secretKey, 'HS256');

    sendJson(200, 'Login successful.', ['token' => $token]);

} else {
    sendJson(405, 'Invalid Request Method. HTTP method should be POST');
}

