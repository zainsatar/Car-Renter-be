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

    if (!isset($_POST['car_id'])) {
        sendJson(404, 'Car ID is required for update.');
    }

    // Fetch the existing car record
    $car_id = $_POST['car_id'];
    $existingCarQuery = "SELECT * FROM `cars` WHERE `car_id`='$car_id'";
    $existingCarResult = mysqli_query($conn, $existingCarQuery);

    if (!$existingCarResult) {
        sendJson(500, 'Database query error.');
    }

    $existingCar = mysqli_fetch_assoc($existingCarResult);

    if (!$existingCar) {
        sendJson(404, 'Car not found for the provided ID.');
    }

    // Remove old uploaded files
    $uploadDir = __DIR__ . '/../uploads/';
    if (!empty($existingCar['image1'])) {
        unlink($uploadDir . $existingCar['image1']);
    }
    if (!empty($existingCar['image2'])) {
        unlink($uploadDir . $existingCar['image2']);
    }
    if (!empty($existingCar['image3'])) {
        unlink($uploadDir . $existingCar['image3']);
    }
    if (!empty($existingCar['image4'])) {
        unlink($uploadDir . $existingCar['image4']);
    }

    if (isset($_FILES['image1'])) {
        $file = $_FILES['image1'];
        $fileBasePath = $_POST['renter_id'] . time() . $file['name'];
        $uploadfile = $uploadDir . basename($fileBasePath);
        if (move_uploaded_file($file['tmp_name'], $uploadfile)) {
            $_POST['image1'] = $fileBasePath;
        } else {
            sendJson(500, 'Failed to upload file.');
        }
    }
    if (isset($_FILES['image2'])) {
        $file = $_FILES['image2'];
        $fileBasePath = $_POST['renter_id'] . time() . $file['name'];
        $uploadfile = $uploadDir . basename($fileBasePath);
        if (move_uploaded_file($file['tmp_name'], $uploadfile)) {
            $_POST['image2'] = $fileBasePath;
        } else {
            sendJson(500, 'Failed to upload file.');
        }
    }
    if (isset($_FILES['image3'])) {
        $file = $_FILES['image3'];
        $fileBasePath = $_POST['renter_id'] . time() . $file['name'];
        $uploadfile = $uploadDir . basename($fileBasePath);
        if (move_uploaded_file($file['tmp_name'], $uploadfile)) {
            $_POST['image3'] = $fileBasePath;
        } else {
            sendJson(500, 'Failed to upload file.');
        }
    }
    if (isset($_FILES['image4'])) {
        $file = $_FILES['image4'];
        $fileBasePath = $_POST['renter_id'] . time() . $file['name'];
        $uploadfile = $uploadDir . basename($fileBasePath);
        if (move_uploaded_file($file['tmp_name'], $uploadfile)) {
            $_POST['image4'] = $fileBasePath;
        } else {
            sendJson(500, 'Failed to upload file.');
        }
    }

    unset($_POST['car_id']);

    $setClause = "";
    foreach ($_POST as $column => $value) {
        // Escape and quote the value if it's a string
        $escapedValue = is_string($value) ? "'" . mysqli_real_escape_string($conn, $value) . "'" : $value;

        // Concatenate each column-value pair
        $setClause .= "`$column`=$escapedValue, ";
    }

    // Remove the trailing comma and space
    $setClause = rtrim($setClause, ', ');


    $sql = "UPDATE `cars` SET $setClause WHERE `car_id`=$car_id";

    $query = mysqli_query($conn, $sql);
    if ($query)
        sendJson(200, 'Car updated successfully.');
    sendJson(500, 'Something going wrong.');
endif;

sendJson(405, 'Invalid Request Method. HTTP method should be POST');
