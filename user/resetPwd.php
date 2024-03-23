<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: access');
header('Access-Control-Allow-Methods: POST');
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With');

require_once __DIR__ . '/../database/database.php';
require_once __DIR__ . '/../helper/sendJson.php';
require_once __DIR__ . '/../helper/authHelper.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST'):

    if (!isset($_POST['user_id'])) {
        sendJson(404, 'Invalid user id.');
    }

    if (!isset($_POST['new_password'])) {
        sendJson(404, 'Please provide new password');
    }


    $user_id = mysqli_real_escape_string($conn, trim($_POST['user_id']));
    $new_password = $_POST['new_password'];


    $sql = "SELECT * FROM `users` WHERE `id`='$user_id'";
    $query = mysqli_query($conn, $sql);

    if (!$query) {
        sendJson(500, 'Database query error.');
    }

    $user = mysqli_fetch_assoc($query);

    if (!$user) {
        sendJson(401, 'Invalid user_id.');
    } else {
        $hash_password = password_hash($new_password, PASSWORD_DEFAULT);

        $sql = "UPDATE `users` SET `password`='$hash_password' WHERE `id`='$user_id'";

        $query = mysqli_query($conn, $sql);
        if ($query)
            sendJson(200, 'Password updated successfully.');
        sendJson(500, 'Something going wrong.');
    }

endif;

sendJson(405, 'Invalid Request Method. HTTP method should be POST');
