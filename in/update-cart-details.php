<?php
session_start();
include "config.php";

if(!isset($_SESSION['user_id'])){
    exit;
}

$user_id = $_SESSION['user_id'];

$id = (int)$_POST['id'];
$type = $_POST['type'];
$value = trim($_POST['value']);

$allowed = ['size_requirement','remarks'];

if(!in_array($type,$allowed)){
    exit;
}

$stmt = $conn->prepare("
UPDATE cart
SET $type=?
WHERE id=?
AND user_id=?
");

$stmt->bind_param("sii",$value,$id,$user_id);
$stmt->execute();

echo "success";