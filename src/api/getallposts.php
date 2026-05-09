<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include("../AultController/ActionController.php");
$getAllPost = new ActionController();
$getAllPost->getAllPost();
?>