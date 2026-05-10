<?php
include("../AultController/ActionController.php");
$getAllHeader = getallheaders();
$token = $getAllHeader["Authorization"] ?? "";
if($token){
    $token = str_replace("Bearer ", '', $token);
}
$actionController = new ActionController();
$actionController->getAllUsers($token);
?>