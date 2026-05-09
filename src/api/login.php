<?php
 include("../AultController/AultController.php");
$data = file_get_contents("php://input");
$values = json_decode($data, true);
$username_email = $values['username_email'] ?? null;
$password = $values['password'] ?? null;
$login = new Authentication();
if($username_email && $password) $login->log_in($username_email, $password)
?>