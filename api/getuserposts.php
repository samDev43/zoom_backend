<?php
include("../AultController/ActionController.php");
$headers = getallheaders();
$authHeader = $headers['Authorization'] ?? "";
$token = null;
if($authHeader){
    $token = str_replace('Bearer ', '', $authHeader);
    // echo json_encode(["status" => "success", "message" => $token]);
}
$getUserPost = new ActionController();
if($token) $getUserPost->getUserPosts($token);
?>