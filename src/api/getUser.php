<?php
include("../AultController/ActionController.php");
$actionController = new ActionController();
$getallheader = getallheaders();
$token = $getallheader['Authorization'] ?? "";
if($token)  $token = str_replace('Bearer ', '', $token);
$actionController->getUser($token);
?>