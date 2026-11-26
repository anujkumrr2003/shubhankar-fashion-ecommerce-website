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

/* ===========================
   CHECK ADDRESS BELONGS TO USER
=========================== */

$stmt = mysqli_prepare($conn, "
    SELECT id
    FROM b2b_user_addresses
    WHERE id=? AND user_id=?
    LIMIT 1
");

mysqli_stmt_bind_param($stmt, "ii", $address_id, $user_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {

    $_SESSION['error'] = "Invalid address.";

    header("Location: my-address.php");
    exit;
}

mysqli_stmt_close($stmt);

/* ===========================
   REMOVE OLD DEFAULT
=========================== */

$stmt = mysqli_prepare($conn, "
    UPDATE b2b_user_addresses
    SET is_default=0
    WHERE user_id=?
");

mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

/* ===========================
   SET NEW DEFAULT
=========================== */

$stmt = mysqli_prepare($conn, "
    UPDATE b2b_user_addresses
    SET is_default=1
    WHERE id=? AND user_id=?
");

mysqli_stmt_bind_param($stmt, "ii", $address_id, $user_id);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

$_SESSION['success'] = "Default address updated successfully.";

header("Location: my-address.php");
exit;
?>