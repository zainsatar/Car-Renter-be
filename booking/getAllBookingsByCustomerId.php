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
        !isset($_GET['customer_id'])
    ):
        sendJson(
            422,
            'customer_id is required'
        );
    endif;

    $customer_id = $_GET['customer_id'];

    $sql = "SELECT * FROM `bookings` WHERE `customer_id`='$customer_id'";
    $result = mysqli_query($conn, $sql);

    if (!$result) {
        sendJson(500, 'Database query error.');
    }

    $bookings = array();

    while ($row = mysqli_fetch_assoc($result)) {
        $bookings[] = $row;
    }

    if (empty($bookings)) {
        sendJson(401, 'No record found');
    }

    sendJson(200, 'All Bookings retrieved successfully', ['bookings' => $bookings]);
endif;

sendJson(405, 'Invalid Request Method. HTTP method should be GET');
