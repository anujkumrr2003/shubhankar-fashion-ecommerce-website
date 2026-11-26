<?php

session_start();

include 'config.php';

$id=(int)$_GET['id'];

$action=$_GET['action'];

$user=$_SESSION['user_id'];

if($action=="plus"){

$conn->query("UPDATE cart
SET qty=qty+1
WHERE id='$id'
AND user_id='$user'");

}

if($action=="minus"){

$conn->query("UPDATE cart
SET qty=IF(qty>1,qty-1,1)
WHERE id='$id'
AND user_id='$user'");

}

header("Location:cart");