<?php
include("../AultController/ActionController.php");
$actionController = new ActionController();
$getallheader = getallheaders();
$token = $getallheader['Authorization'] ?? "";
if($token)  $token = str_replace('Bearer ', '', $token);
$profile = $_FILES['image']['name'] ?? null;
$temp_profile = $_FILES['image']['tmp_name'] ?? null;
$directory = __DIR__ . "/public/uploads/";

if($profile)  $actionController->uploadProfile($token, $profile, $temp_profile, $directory);
?>