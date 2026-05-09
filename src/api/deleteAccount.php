<?php
include("../AultController/ActionController.php");
$getAllHeader = getallheaders();
$data = file_get_contents("php://input");
$data = json_decode($data, true);
$deleteUser = new ActionController();
$user_id = $data['id'];
$token = $getAllHeader["Authorization"] ?? "";
if($token){
    $tokenn = str_replace("Bearer ", '', $token);
}

 if($user_id) $deleteUser->deleteAccount($tokenn, $user_id);


?>