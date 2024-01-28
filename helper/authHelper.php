<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../helper/sendJson.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$secretKey = 'MY_JWT';

function validateToken($token)
{
    global $secretKey;

    try {
        if (empty($token))
            sendJson(401, 'Unauthorized');

        $decoded = JWT::decode($token, new Key($secretKey, 'HS256'));
        return $decoded;
    } catch (Exception $e) {
        return false;
    }
}

