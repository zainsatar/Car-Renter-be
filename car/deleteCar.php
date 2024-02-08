<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Methods: POST');
header('Content-Type: application/json; charset=UTF-8');

require_once __DIR__ . '/../database/database.php';
require_once __DIR__ . '/../helper/sendJson.php';
require_once __DIR__ . '/../helper/authHelper.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $headers = apache_request_headers();
    $token = isset($headers['Authorization']) ? str_replace('Bearer ', '', $headers['Authorization']) : null;

    // Validate the token using the helper function
    $decodedToken = validateToken($token);

    if (!$decodedToken) {
        sendJson(401, 'Unauthorized');
    }


    if (!isset($_POST['renter_id']) || !isset($_POST['car_id'])) {
        sendJson(422, 'Please provide both renter_id and car_id.');
    }

    $renter_id = $_POST['renter_id'];  
    $car_id = $_POST['car_id'];   

    $sql = "SELECT * FROM `cars` WHERE `car_id`='$car_id' AND `renter_id`='$renter_id'";
    $query = mysqli_query($conn, $sql);

    if (!$query) {
        sendJson(500, 'Database query error.');
    }

    $car = mysqli_fetch_assoc($query);

    if (!$car) {
        sendJson(401, 'Invalid renter_id or car_id');
    }

    $basePath = dirname(__DIR__);
    $path=$basePath.'/';

    $image1=$car['image1'];
    $image2=$car['image2'];
    $image3=$car['image3'];
    $image4=$car['image4'];

    // Create an array of file paths
    $filePaths = [
        $path . $image1,
        $path . $image2,
        $path . $image3,
        $path . $image4
    ];

    // Loop through the file paths and remove files if they exist
    foreach ($filePaths as $filePath) {
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    // Delete the car
    $deleteSql = "DELETE FROM `cars` WHERE `car_id`='$car_id' AND `renter_id`='$renter_id'";
    $deleteQuery = mysqli_query($conn, $deleteSql);

    if (!$deleteQuery) {
        sendJson(500, 'Failed to delete car');
    }
    sendJson(200, 'Car deleted successfully.');

} else {
    sendJson(405, 'Invalid Request Method. HTTP method should be POST');
}
