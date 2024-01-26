<?php
$url = 'srv1163.hstgr.io';
$username = 'u850693830_root';
$password = 'root@USER123';
$conn = mysqli_connect($url, $username, $password, "u850693830_carRenter");
if (mysqli_connect_errno()) {
    echo "Connection Failed - " . mysqli_connect_error();
    exit;
}
