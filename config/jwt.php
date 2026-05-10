<?php
require_once __DIR__ . '/../vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$secret_key = "r3aCt_PHP_jWt_9xK#2026_SECURE_KEY_!@99abcXYZ";
$issuer = "localhost";
// $audience = "myusers";
// $issued_at = time();
// $not_before = $issued_at + 10; // Token valid after 10 seconds
// $expire = $issued_at + 3600; // Token expires in 1 hour
function generate_jwt($user){
    global $secret_key, $issuer;
    $payload = array(
        "iss" => $issuer,
        "at" => time(),
        "role" => $user['role'],
        "user_id" => $user['id'],
        "email" => $user['email'],
        "username" => $user['username']
    );
    return JWT::encode($payload, $secret_key, 'HS256');
}

function validate_jwt($jwt){
    global $secret_key;
    try {
        $decoded = JWT::decode($jwt, new key($secret_key, 'HS256'));
        return  $decoded;
    }catch (Exception $e){
        return false;
    }
}
?>