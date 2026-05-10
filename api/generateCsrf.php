<?php
include('../AultController/AultController.php');

if(empty($_SESSION['csrf_token'])){
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

echo json_encode([
    "csrf_token" => $_SESSION['csrf_token']
]);
?>