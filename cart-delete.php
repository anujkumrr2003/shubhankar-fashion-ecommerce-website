<?php

session_start();

include 'config.php';

$id=(int)$_GET['id'];

$user=$_SESSION['user_id'];

$conn->query("DELETE FROM cart
WHERE id='$id'
AND user_id='$user'");

header("Location:cart");