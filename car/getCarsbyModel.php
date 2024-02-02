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

    // Get the JWT token from the Authorization header
    $headers = apache_request_headers();
    $token = isset($headers['Authorization']) ? str_replace('Bearer ', '', $headers['Authorization']) : null;

    // Validate the token using the helper function
    $decodedToken = validateToken($token);

    if (!$decodedToken) {
        sendJson(401, 'Unauthorized');
    }


    if (
        !isset($_GET['model_id'])
    ):
        sendJson(
            422,
            'model id is required'
        );
    endif;

    $model_id = $_GET['model_id'];

    $sql = "SELECT * FROM `cars` WHERE `company_id`='$model_id'";
    $result = mysqli_query($conn, $sql);

    if (!$result) {
        sendJson(500, 'Database query error.');
    }

    $cars = array();

    while ($row = mysqli_fetch_assoc($result)) {
        $cars[] = $row;
    }

    if (empty($cars)) {
        sendJson(401, 'No record found');
    }

    sendJson(200, 'Cars retrieved successfully', ['cars' => $cars]);
endif;

sendJson(405, 'Invalid Request Method. HTTP method should be GET');
