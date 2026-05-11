<?php
//  error_reporting(E_ALL);
// ini_set('display_errors', 1);
include("../AultController/ActionController.php");
$headers = getallheaders();
$authHeader = $headers['Authorization'] ?? "";
if($authHeader){
    $token = str_replace('Bearer ', '', $authHeader);
    // echo json_encode(["status" => "success", "message" => $token]);
}
$addPost = new ActionController();
$title = $_POST['title'] ?? null;
$excerpt = $_POST['excerpt'] ?? null;
$content = $_POST['content'] ?? null;
$image = $_FILES['cover_image']['name'] ?? null;
$temp_image = $_FILES['cover_image']['tmp_name'] ?? null;
$imageSize = $_FILES['cover_image']['size'] ?? null;
$path = __DIR__ . "/public/uploads/";
if($title && $excerpt && $content ){
    // if($_FILES['cover_image']['error'] === 0){
        // $cover_image = $_FILES['cover_image'];
        $addPost->addPost($title, $excerpt, $content, $image, $temp_image, $path, $imageSize, $token);
    // }
};



?>