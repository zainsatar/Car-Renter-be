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

    if (!isset($_POST['booking_id']) && !isset($_POST['status'])) {
        sendJson(404, 'Booking ID and status is required for update.');
    }

    // Fetch the existing booking record
    $booking_id = $_POST['booking_id'];
    $booking_status = $_POST['status'];
    $existingBookingQuery = "SELECT * FROM `bookings` WHERE `booking_id`='$booking_id'";
    $existingBooking = mysqli_query($conn, $existingBookingQuery);

    if (!$existingBooking) {
        sendJson(500, 'Database query error.');
    }

    $bookingRecord = mysqli_fetch_assoc($existingBooking);

    if (!$bookingRecord) {
        sendJson(404, 'Booking not found for the provided ID.');
    }

    $car_id = $bookingRecord['car_id'];

    $sql = "UPDATE `bookings` SET `status`= '$booking_status' WHERE `booking_id`=$booking_id";

    $query = mysqli_query($conn, $sql);
    if ($query)
    {
        // Update the isBooked column in the cars table
        if ($booking_status == 'canceled' || $booking_status == 'completed') {
            // Set isBooked to false if booking status is canceled or completed
            $isBooked = 0;
        } else {
            // Set isBooked to true for any other status
            $isBooked = 1;
        } 
        $updateCarSql = "UPDATE `cars` SET `isBooked` = $isBooked WHERE `car_id` = $car_id";
        $updateCarQuery = mysqli_query($conn, $updateCarSql);
        if (!$updateCarQuery) {
            sendJson(500, 'Error updating car isBooked status.');
        }   
        sendJson(200, 'Booking updated successfully.');
    }
    sendJson(500, 'Something going wrong.');
endif;

sendJson(405, 'Invalid Request Method. HTTP method should be POST');
