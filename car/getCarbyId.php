<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: access');
header('Access-Control-Allow-Methods: POST');
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With');

require_once __DIR__ . '/../database/database.php';
require_once __DIR__ . '/../helper/sendJson.php';
require_once __DIR__ . '/../helper/authHelper.php';

if ($_SERVER['REQUEST_METHOD'] == 'GET'):
    $data = json_decode(file_get_contents('php://input'));

    // Get the JWT token from the Authorization header
    $headers = apache_request_headers();
    $token = isset($headers['Authorization']) ? str_replace('Bearer ', '', $headers['Authorization']) : null;

    // Validate the token using the helper function
    $decodedToken = validateToken($token);

    if (!$decodedToken) {
        sendJson(401, 'Unauthorized');
    }


    if (
        !isset($data->car_id)
    ):
        sendJson(
            422,
            'car id is required'
        );
    endif;

    $car_id = $data->car_id;

    $sql = "SELECT * FROM `cars` WHERE `car_id`='$car_id'";
    $result = mysqli_query($conn, $sql);

    if (!$result) {
        sendJson(500, 'Database query error.');
    }

    $car = mysqli_fetch_assoc($result);

    if (!$car) {
        sendJson(401, 'No record found');
    }

    sendJson(200, 'Car retrieved successfully', ['data' => $car]);
endif;

sendJson(405, 'Invalid Request Method. HTTP method should be GET');
