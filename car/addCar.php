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

    if (
        isset($_FILES['image1']) &&
        isset($_FILES['image2']) &&
        isset($_FILES['image3']) &&
        isset($_FILES['image4'])
    ) {
        $image1 = $_FILES['image1'];
        $image2 = $_FILES['image2'];
        $image3 = $_FILES['image3'];
        $image4 = $_FILES['image4'];

        $image1BasePath = $_POST['renter_id'] . time() . $image1['name'];
        $image2BasePath = $_POST['renter_id'] . time() . $image2['name'];
        $image3BasePath = $_POST['renter_id'] . time() . $image3['name'];
        $image4BasePath = $_POST['renter_id'] . time() . $image4['name'];

        // You can customize the file handling logic as per your requirements
        $uploadDir = __DIR__ . '/../uploads/';
        $uploadimage1 = $uploadDir . basename($image1BasePath);
        $uploadimage2 = $uploadDir . basename($image2BasePath);
        $uploadimage3 = $uploadDir . basename($image3BasePath);
        $uploadimage4 = $uploadDir . basename($image4BasePath);

        if (
            move_uploaded_file($image1['tmp_name'], $uploadimage1) &&
            move_uploaded_file($image2['tmp_name'], $uploadimage2) &&
            move_uploaded_file($image2['tmp_name'], $uploadimage4) &&
            move_uploaded_file($image3['tmp_name'], $uploadimage3)
        ) {
            $_POST['image1'] = $image1BasePath;
            $_POST['image2'] = $image2BasePath;
            $_POST['image3'] = $image3BasePath;
            $_POST['image4'] = $image4BasePath;
        } else {
            sendJson(500, 'Failed to upload file.');
        }
    }

    $renter_id = $_POST['renter_id'];
    $company_id = $_POST['company_id'];
    $car_name = $_POST['car_name'];
    $color = $_POST['color'];
    $fuel_type = $_POST['fuel_type'];
    $kms_driven = $_POST['kms_driven'];
    $mileage = $_POST['mileage'];
    $engine = $_POST['engine'];
    $latitude = $_POST['latitude'];
    $longitude = $_POST['longitude'];
    $province = $_POST['province'];
    $city = $_POST['city'];
    $address = $_POST['address'];
    $famous_place_nearby = $_POST['famous_place_nearby']; 
    $image1 = $_POST['image1'];
    $image2 = $_POST['image2'];
    $image3 = $_POST['image3'];
    $image4 = $_POST['image4'];
    $ratings = $_POST['ratings'];


    $sql = "INSERT INTO `cars` (`renter_id`, `company_id`, `car_name`,`kms_driven`,`engine`, `fuel_type`, `color`, `mileage`, `province`, `city`, `address`, `latitude`, `longitude`,`famous_place_nearby`, `image1`, `image2`, `image3`, `image4`, `ratings`)
     VALUES ('$renter_id', '$company_id', '$car_name','$kms_driven','$engine','$fuel_type','$color','$mileage' '$province', '$city', '$address','$latitude','$longitude','$famous_place_nearby', '$image1', '$image2', '$image3', '$image4', '$ratings')";

    $query = mysqli_query($conn, $sql);
    if ($query)
        sendJson(200, 'Car added successfully.');
    sendJson(500, 'Something going wrong.');
endif;

sendJson(405, 'Invalid Request Method. HTTP method should be POST');
