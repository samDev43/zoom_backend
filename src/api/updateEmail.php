<?php
include("../AultController/ActionController.php");
$actionController = new ActionController();
$data = json_decode(file_get_contents("php://input"), true);
$new_email = $data['email'] ?? null;
$getallheader = getallheaders();
$token = $getallheader['Authorization'] ?? "";
if($token)  $token = str_replace('Bearer ', '', $token);
if($new_email) $actionController->updateEmail($token, $new_email);

?>