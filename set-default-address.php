<?php
session_start();
include "config.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = (int)$_SESSION['user_id'];

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: my-address.php");
    exit;
}

$address_id = (int)$_GET['id'];

// Check address belongs to logged-in user
$stmt = mysqli_prepare($conn,"
    SELECT id
    FROM user_addresses
    WHERE id=? AND user_id=?
    LIMIT 1
");

mysqli_stmt_bind_param($stmt,"ii",$address_id,$user_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

if(mysqli_num_rows($result)==0){

    $_SESSION['error']="Invalid Address.";

    header("Location: my-address.php");
    exit;
}

// Remove previous default
mysqli_query($conn,"
UPDATE user_addresses
SET is_default='0'
WHERE user_id='$user_id'
");

// Set selected address as default
$stmt = mysqli_prepare($conn,"
UPDATE user_addresses
SET is_default='1'
WHERE id=? AND user_id=?
");

mysqli_stmt_bind_param($stmt,"ii",$address_id,$user_id);
mysqli_stmt_execute($stmt);

$_SESSION['success']="Default address updated successfully.";

header("Location: my-address.php");
exit;
?>