<?php
include("../AultController/ActionController.php");
$data = json_decode(file_get_contents("php://input"), true);
$new_username = $data['username'] ?? null;
$actionController = new ActionController();
$getallheader = getallheaders();
$token = $getallheader['Authorization'] ?? "";
if($token)  $token = str_replace('Bearer ', '', $token);
if($new_username) $actionController->updateUserName($token, $new_username);

?>