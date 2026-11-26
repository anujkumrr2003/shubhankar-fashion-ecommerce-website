<?php
session_start();
include "config.php";

if(!isset($_SESSION['user_id'])){
    header("Location:login.php");
    exit;
}

$user_id=(int)$_SESSION['user_id'];

$q=mysqli_query($conn,"
SELECT *
FROM user
WHERE id='$user_id'
LIMIT 1
");

if(mysqli_num_rows($q)==0){
    header("Location:my-address.php");
    exit;
}

$u=mysqli_fetch_assoc($q);

// Check duplicate
$check=mysqli_query($conn,"
SELECT id
FROM user_addresses
WHERE user_id='$user_id'
AND address='".mysqli_real_escape_string($conn,$u['address'])."'
LIMIT 1
");

if(mysqli_num_rows($check)==0){

$type="Home";
$default=0;

$stmt=mysqli_prepare($conn,"
INSERT INTO user_addresses
(
user_id,
full_name,
phone,
country,
state,
city,
address,
pincode,
address_type,
is_default,
created_at
)
VALUES
(
?,?,?,?,?,?,?,?,?,?,NOW()
)
");

mysqli_stmt_bind_param(
$stmt,
"issssssssi",
$user_id,
$u['full_name'],
$u['phone'],
$u['country'],
$u['state'],
$u['city'],
$u['address'],
$u['pincode'],
$type,
$default
);

mysqli_stmt_execute($stmt);

$_SESSION['success']="Profile address saved successfully.";

}else{

$_SESSION['success']="Profile address already exists.";

}

header("Location:my-address.php");
exit;