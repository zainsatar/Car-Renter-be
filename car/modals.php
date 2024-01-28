<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=UTF-8');

require_once __DIR__ . '/../database/database.php'; 
require_once __DIR__ . '/../helper/sendJson.php';
require_once __DIR__ . '/../helper/authHelper.php'; 

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    // Get the JWT token from the Authorization header
    $headers = apache_request_headers();
    $token = isset($headers['Authorization']) ? str_replace('Bearer ', '', $headers['Authorization']) : null;

    // Validate the token using the helper function
    $decodedToken = validateToken($token);

    if (!$decodedToken) {
        sendJson(401, 'Unauthorized');
    }

    // Assuming your car models table is named 'car_models'
    $sql = "SELECT * FROM `car_models`";
    $result = mysqli_query($conn, $sql);

    if (!$result) {
        sendJson(500, 'Database query error.');
    }

    $carModels = array();

    while ($row = mysqli_fetch_assoc($result)) {
        $carModels[] = $row;
    }

    if (empty($carModels)) {
        sendJson(401, 'No record found');
    }

    sendJson(200, 'Car models retrieved successfully', ['carModels' => $carModels]);

} else {
    sendJson(405, 'Invalid Request Method. HTTP method should be GET');
}

