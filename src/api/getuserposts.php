<?php
include("../AultController/ActionController.php");
$headers = getallheaders();
$authHeader = $headers['Authorization'] ?? "";
if($authHeader){
    $token = str_replace('Bearer ', '', $authHeader);
    // echo json_encode(["status" => "success", "message" => $token]);
}
$getUserPost = new ActionController();
$getUserPost->getUserPosts($token);
?>