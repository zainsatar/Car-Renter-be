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
        !isset($_GET['renter_id'])
    ):
        sendJson(
            422,
            'renter id is required'
        );
    endif;

    $renter_id = $_GET['renter_id'];

    $sql = "SELECT b.*, c.image1, c.image2, c.image3, c.image4 
        FROM `bookings` b 
        JOIN `cars` c ON b.car_id = c.car_id 
        WHERE b.`renter_id`='$renter_id'";
        
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
