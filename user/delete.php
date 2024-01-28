<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Methods: DELETE');
header('Content-Type: application/json; charset=UTF-8');

require_once __DIR__ . '/../database/database.php';
require_once __DIR__ . '/../helper/sendJson.php';
require_once __DIR__ . '/../helper/authHelper.php'; 

if ($_SERVER['REQUEST_METHOD'] == 'DELETE') {
    // Get the JWT token from the Authorization header
    $headers = apache_request_headers();
    $token = isset($headers['Authorization']) ? str_replace('Bearer ', '', $headers['Authorization']) : null;

    // Validate the token using the helper function
    $decodedToken = validateToken($token);

    if (!$decodedToken) {
        sendJson(401, 'Unauthorized');
    }

    $email = $decodedToken->email;

    // Check if the user exists
    $sql = "SELECT * FROM `users` WHERE `email`='$email'";
    $query = mysqli_query($conn, $sql);

    if (!$query) {
        sendJson(500, 'Database query error.');
    }

    $user = mysqli_fetch_assoc($query);

    if (!$user) {
        sendJson(404, 'User not found');
    }

    // Delete the user
    $deleteSql = "DELETE FROM `users` WHERE `email`='$email'";
    $deleteQuery = mysqli_query($conn, $deleteSql);

    if (!$deleteQuery) {
        sendJson(500, 'Failed to delete user');
    }

    sendJson(200, 'User deleted successfully');

} else {
    sendJson(405, 'Invalid Request Method. HTTP method should be DELETE');
}

