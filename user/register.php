<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: access');
header('Access-Control-Allow-Methods: POST');
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With');

require_once __DIR__ . '/../database/database.php';
require_once __DIR__ . '/../helper/sendJson.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST'):
    $data = json_decode(file_get_contents('php://input'));
    if (
        !isset($_POST['name']) ||
        !isset($_POST['email']) ||
        !isset($_POST['password']) ||
        empty(trim($_POST['name'])) ||
        empty(trim($_POST['email'])) ||
        empty(trim($_POST['password']))
    ):
        sendJson(
            422,
            'Please fill all the required fields & None of the fields should be empty.',
            ['required_fields' => ['name', 'email', 'password']]
        );
    endif;

    if (isset($_POST['subscriptionPlan'])) {
        $_POST['role'] = 'renter';
    } else {
        $_POST['role'] = 'customer';
    }

    if (
        isset($_FILES['profileImage']) &&
        isset($_FILES['idBackImage']) &&
        isset($_FILES['idFrontImage'])
    ) {
        $profileImage = $_FILES['profileImage'];
        $idBackImage = $_FILES['idBackImage'];
        $idFrontImage = $_FILES['idFrontImage'];

        $profileImageBasePath = $_POST['email'] . time() . $profileImage['name'];
        $idBackImageBasePath = $_POST['email'] . time() . $idBackImage['name'];
        $idFrontImageBasePath = $_POST['email'] . time() . $idFrontImage['name'];

        // You can customize the file handling logic as per your requirements
        $uploadDir = __DIR__ . '/../uploads/';
        $uploadprofileImage = $uploadDir . basename($profileImageBasePath);
        $uploadidBackImage = $uploadDir . basename($idBackImageBasePath);
        $uploadidFrontImage = $uploadDir . basename($idFrontImageBasePath);

        if (
            move_uploaded_file($profileImage['tmp_name'], $uploadprofileImage) &&
            move_uploaded_file($idBackImage['tmp_name'], $uploadidBackImage) &&
            move_uploaded_file($idFrontImage['tmp_name'], $uploadidFrontImage)
        ) {
            $_POST['profileImage'] = $profileImageBasePath;
            $_POST['idBackImage'] = $idBackImageBasePath;
            $_POST['idFrontImage'] = $idFrontImageBasePath;
        } else {
            sendJson(500, 'Failed to upload file.');
        }
    }

    $name = mysqli_real_escape_string($conn, htmlspecialchars(trim($_POST['name'])));
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $password = trim($_POST['password']);
    $role = $_POST['role'];
    $subscriptionPlan = $_POST['subscriptionPlan'];
    $profileImage = $_POST['profileImage'];
    $idBackImage = $_POST['idBackImage'];
    $idFrontImage = $_POST['idBackImage'];

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)):
        sendJson(500, 'Invalid Email Address!');

    elseif (strlen($password) < 8):
        sendJson(500, 'Your password must be at least 8 characters long!');
    endif;

    $hash_password = password_hash($password, PASSWORD_DEFAULT);
    $sql = "SELECT `email` FROM `users` WHERE `email`='$email'";
    $query = mysqli_query($conn, $sql);
    $row_num = mysqli_num_rows($query);

    if ($row_num > 0)
        sendJson(400, 'This E-mail already in use!');

    $sql = "INSERT INTO `users`(`name`,`email`,`password`,`profileImage`,`idFrontImage`,`idBackImage`,`subscriptionPlan`,`role`) VALUES('$name','$email','$hash_password','$profileImage','$idFrontImage','$idBackImage','$subscriptionPlan','$role')";

    $query = mysqli_query($conn, $sql);
    if ($query)
        sendJson(200, 'You have successfully registered.');
    sendJson(500, 'Something going wrong.');
endif;

sendJson(405, 'Invalid Request Method. HTTP method should be POST');
