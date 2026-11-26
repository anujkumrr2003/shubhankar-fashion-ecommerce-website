<?php
session_start();
include "config.php";

$count = 0;

if(isset($_SESSION['user_id'])){

    $user_id = (int)$_SESSION['user_id'];

    $sql = mysqli_query($conn,"
        SELECT IFNULL(SUM(qty),0) AS total
        FROM cart
        WHERE user_id='$user_id'
    ");

    if($row = mysqli_fetch_assoc($sql)){
        $count = (int)$row['total'];
    }
}

echo $count;