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
    $basePath = dirname(__DIR__).'/';
    
    if (isset($_FILES['image1']) && !empty($existingCar['image1'])) {
        unlink($basePath . $existingCar['image1']);
    }
    if (isset($_FILES['image2']) && !empty($existingCar['image2'])) {
        unlink($basePath . $existingCar['image2']);
    }
    if (isset($_FILES['image3']) && !empty($existingCar['image3'])) {
        unlink($basePath . $existingCar['image3']);
    }
    if (isset($_FILES['image4']) && !empty($existingCar['image4'])) {
        unlink($basePath . $existingCar['image4']);
    }

    $uploadDir = 'uploads/cars/renter_id_'.$_POST['renter_id']. '/';
    $path=$basePath.$uploadDir;
    
    // Create the folder if it doesn't exist
    if (!file_exists($path)) {
        mkdir($path, 0777, true);
    }
    
    if (isset($_FILES['image1'])) {
        $file = $_FILES['image1'];
        $fileName = time() . $file['name'];
        $uploadfile = $path . basename($fileName);
        if (move_uploaded_file($file['tmp_name'], $uploadfile)) {
            $_POST['image1'] =$uploadDir. $fileName;
        } else {
            sendJson(500, 'Failed to upload file.');
        }
    }
    if (isset($_FILES['image2'])) {
        $file = $_FILES['image2'];
        $fileName = time() . $file['name'];
        $uploadfile = $path . basename($fileName);
        if (move_uploaded_file($file['tmp_name'], $uploadfile)) {
            $_POST['image2'] =$uploadDir. $fileName;
        } else {
            sendJson(500, 'Failed to upload file.');
        }
    }
    if (isset($_FILES['image3'])) {
        $file = $_FILES['image3'];
        $fileName = time() . $file['name'];
        $uploadfile = $path . basename($fileName);
        if (move_uploaded_file($file['tmp_name'], $uploadfile)) {
            $_POST['image3'] =$uploadDir. $fileName;
        } else {
            sendJson(500, 'Failed to upload file.');
        }
    }
    if (isset($_FILES['image4'])) {
        $file = $_FILES['image4'];
        $fileName = time() . $file['name'];
        $uploadfile = $path . basename($fileName);
        if (move_uploaded_file($file['tmp_name'], $uploadfile)) {
            $_POST['image4'] =$uploadDir. $fileName;
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
