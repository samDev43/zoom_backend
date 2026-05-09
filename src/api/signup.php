<?php
//  error_reporting(E_ALL);
// ini_set('display_errors', 1);
include('../AultController/AultController.php');

$data = file_get_contents("php://input");
$values = json_decode($data, true);
$email = $values['email'] ?? null;
$username = $values['username'] ?? null;
$password = $values['password'] ?? null;
$csrf_token = $values['csrf_token'] ?? null;
$sign_up = new Authentication();
// echo($_SESSION['csrf_token']);
// echo('<br>');
// echo($csrf_token);
if ($email && $username && $password) $sign_up->sign_up($email, $username, $password, $csrf_token);
?>