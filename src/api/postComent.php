<?php
include("../AultController/ActionController.php");
$getToken = getallheaders();
$data = json_decode(file_get_contents("php://input"), true);
$postComment = new ActionController();
$comment = $data['comment'] ?? null;
$post_id = $data['postId'] ?? null;
$token = $getToken['Authorization'] ?? null;
if($token) $token = str_replace("Bearer ", "", $token);
if($comment && $post_id){

   $postComment->postComment($comment, $post_id, $token);
}
?>