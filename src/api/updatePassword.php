<?php
include("../AultController/ActionController.php");
$actionController = new ActionController();
$getallheader = getallheaders();
$data = json_decode(file_get_contents("php://input"), true);
$new_password = $data['password'] ?? null;
$token = $getallheader['Authorization'] ?? "";
if($token)  $token = str_replace('Bearer ', '', $token);
if($new_password) $actionController->updatePassword($token, $new_password);

?>