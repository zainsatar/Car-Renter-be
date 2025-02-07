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

    // Get the JWT token from the Authorization header
    $headers = apache_request_headers();
    $token = isset($headers['Authorization']) ? str_replace('Bearer ', '', $headers['Authorization']) : null;

    // Validate the token using the helper function
    $decodedToken = validateToken($token);

    if (!$decodedToken) {
        sendJson(401, 'Unauthorized');
    }
    if (
        !isset($_POST['car_id']) ||
        !isset($_POST['customer_id']) ||
        !isset($_POST['renter_id']) ||
        !isset($_POST['province']) ||
        !isset($_POST['customer_phone_number']) ||
        !isset($_POST['city']) ||
        !isset($_POST['address']) ||
        !isset($_POST['start_date']) ||
        !isset($_POST['end_date']) ||
        !isset($_POST['latitude']) ||
        !isset($_POST['longitude']) ||
        !isset($_POST['reason_to_buy']) ||
        empty(trim($_POST['end_date'])) ||
        empty(trim($_POST['start_date'])) ||
        empty(trim($_POST['renter_id'])) ||
        empty(trim($_POST['customer_id'])) ||
        empty(trim($_POST['car_id'])) ||
        empty(trim($_POST['customer_phone_number'])) ||
        empty(trim($_POST['province'])) ||
        empty(trim($_POST['city'])) ||
        empty(trim($_POST['address']))||
        empty(trim($_POST['latitude'])) ||
        empty(trim($_POST['longitude'])) ||
        empty(trim($_POST['reason_to_buy']))
    ):
        sendJson(
            404,
            'Please fill all the required fields'
        );
    endif;


    $columns = implode('`, `', array_keys($_POST));
    $values = implode("', '", array_map(function ($value) use ($conn) {
        return is_string($value) ? mysqli_real_escape_string($conn, $value) : $value;
    }, $_POST));

    $sql = "INSERT INTO `bookings` (`$columns`) VALUES ('$values')";

    $query = mysqli_query($conn, $sql);
    if ($query)
    {
        $car_id=$_POST['car_id'];
        // Update the isBooked column to true
        $sql = "UPDATE `cars` SET `isBooked` = 1 WHERE `car_id` = $car_id";
        $updateQuery = mysqli_query($conn, $sql);
        if ($updateQuery) {
            sendJson(200, 'Booking added successfully.', ['data' => $_POST]);
        }else{
            // Handle the case where the update query fails
            sendJson(500, 'Error updating car status.');
        }
    }
    sendJson(500, 'Something going wrong.');
endif;

sendJson(405, 'Invalid Request Method. HTTP method should be POST');
