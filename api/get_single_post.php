<?php
include("../AultController/ActionController.php");
$post_id = $_GET['id'] ?? null;
$getSinglePost = new ActionController();
if($post_id) $getSinglePost->getSinglePost($post_id);
?>