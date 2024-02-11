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
        !isset($_POST['rating']) ||
        empty(trim($_POST['customer_id'])) ||
        empty(trim($_POST['car_id'])) ||
        empty(trim($_POST['rating']))
    ):
        sendJson(
            404,
            'Please fill all the required fields'
        );
    endif;

    if($_POST['rating'] <= 0 ||$_POST['rating'] > 5){
        sendJson(
            500,
            'Rate between 1 to 5'
        );
    }

    $car_id=$_POST['car_id'];
    $customer_id=$_POST['customer_id'];
    $rating=$_POST['rating'];

    $columns = implode('`, `', array_keys($_POST));
    $values = implode("', '", array_map(function ($value) use ($conn) {
        return is_string($value) ? mysqli_real_escape_string($conn, $value) : $value;
    }, $_POST));

    $sql = "INSERT INTO `ratings` (`$columns`) VALUES ('$values')";

    $query = mysqli_query($conn, $sql);
    if ($query) {
        // Update ratings column in cars table
        $sql = "SELECT AVG(rating) AS average_rating FROM ratings WHERE car_id = '$car_id'";
        $result = mysqli_query($conn, $sql);
        $row = mysqli_fetch_assoc($result);
        $average_rating = $row['average_rating'];
        if ($average_rating == NULL || $average_rating == null || $average_rating == -1) {
            // If no previous rating or default rating present, set the new rating
            $update_sql = "UPDATE `cars` SET `ratings` = $rating WHERE `car_id` = $car_id";
        } else {
            // If previous ratings exist, update with the average
            $update_sql = "UPDATE `cars` SET `ratings` = $average_rating WHERE `car_id` = $car_id";
        }

        mysqli_query($conn, $update_sql);

        // Success response
        sendJson(
            200,
            'Car rated successfully'
        );
    } else {
        // Error response
        sendJson(
            500,
            'Error adding rating'
        );
    }
endif;

sendJson(405, 'Invalid Request Method. HTTP method should be POST');
