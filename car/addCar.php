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
        !isset($_POST['renter_id']) ||
        !isset($_POST['company_id']) ||
        !isset($_POST['car_name']) ||
        !isset($_POST['province']) ||
        !isset($_POST['city']) ||
        !isset($_POST['address']) ||
        empty(trim($_POST['renter_id'])) ||
        empty(trim($_POST['company_id'])) ||
        empty(trim($_POST['car_name'])) ||
        empty(trim($_POST['province'])) ||
        empty(trim($_POST['city'])) ||
        empty(trim($_POST['address']))
    ):
        sendJson(
            404,
            'Please fill all the required fields'
        );
    endif;

    $uploadDir = __DIR__ . '/../uploads/';
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

    $_POST['ratings'] = -1;

    $columns = implode('`, `', array_keys($_POST));
    $values = implode("', '", array_map(function ($value) use ($conn) {
        return is_string($value) ? mysqli_real_escape_string($conn, $value) : $value;
    }, $_POST));

    $sql = "INSERT INTO `cars` (`$columns`) VALUES ('$values')";

    $query = mysqli_query($conn, $sql);
    if ($query)
        sendJson(200, 'Car added successfully.', ['data' => $_POST]);
    sendJson(500, 'Something going wrong.');
endif;

sendJson(405, 'Invalid Request Method. HTTP method should be POST');
