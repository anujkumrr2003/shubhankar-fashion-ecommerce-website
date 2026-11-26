<?php
session_start();
include "config.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: b2b-cart.php");
    exit;
}

$cart_id = (int)$_GET['id'];

/* Check Cart Item */

$check = $conn->prepare("
    SELECT id
    FROM cart
    WHERE id=?
    AND user_id=?
    LIMIT 1
");

$check->bind_param("ii", $cart_id, $user_id);
$check->execute();

$result = $check->get_result();

if ($result->num_rows == 0) {
    header("Location: b2b-cart.php");
    exit;
}

/* Delete Item */

$delete = $conn->prepare("
    DELETE FROM cart
    WHERE id=?
    AND user_id=?
");

$delete->bind_param("ii", $cart_id, $user_id);
$delete->execute();

/* Redirect */

header("Location: b2b-cart.php");
exit;