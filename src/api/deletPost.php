<?php
include("../AultController/ActionController.php");
$headers = getallheaders();
$authHeader = $headers['Authorization'] ?? "";
$data = file_get_contents("php://input");
$data = json_decode($data, true);
$post_id = $data['post_id']; 
if($authHeader){
    $token = str_replace('Bearer ', '', $authHeader);
}
$deletePost = new  ActionController();
if($post_id) $deletePost->deletePost($post_id, $token)

?>