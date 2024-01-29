<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Methods: POST');
header('Content-Type: application/json; charset=UTF-8');

require_once __DIR__ . '/../database/database.php';
require_once __DIR__ . '/../helper/sendJson.php';
require_once __DIR__ . '/../helper/authHelper.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $headers = apache_request_headers();
    $token = isset($headers['Authorization']) ? str_replace('Bearer ', '', $headers['Authorization']) : null;

    // Validate the token using the helper function
    $decodedToken = validateToken($token);

    if (!$decodedToken) {
        sendJson(401, 'Unauthorized');
    }


    if (!isset($_POST['user_id']) || !isset($_POST['password'])) {
        sendJson(422, 'Please provide both user_id and password.');
    }

    $user_id = $_POST['user_id'];
    $password = trim($_POST['password']);

    $sql = "SELECT * FROM `users` WHERE `id`='$user_id'";
    $query = mysqli_query($conn, $sql);

    if (!$query) {
        sendJson(500, 'Database query error.');
    }

    $user = mysqli_fetch_assoc($query);

    if (!$user || !password_verify($password, $user['password'])) {
        sendJson(401, 'Invalid user id or password.');
    }

    // Delete the user
    $deleteSql = "DELETE FROM `users` WHERE `id`='$user_id'";
    $deleteQuery = mysqli_query($conn, $deleteSql);

    if (!$deleteQuery) {
        sendJson(500, 'Failed to delete user');
    }
    sendJson(200, 'Your account has been deleted.');

} else {
    sendJson(405, 'Invalid Request Method. HTTP method should be POST');
}
