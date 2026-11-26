<?php
session_start();
include "config.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = (int)$_SESSION['user_id'];

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {

    $_SESSION['error'] = "Invalid address.";
    header("Location: b2b-checkout.php");
    exit;
}

$address_id = (int)$_GET['id'];

/*----------------------------------
CHECK ADDRESS
----------------------------------*/

$stmt = mysqli_prepare($conn, "
    SELECT id, is_default
    FROM b2b_user_addresses
    WHERE id=? AND user_id=?
    LIMIT 1
");

mysqli_stmt_bind_param($stmt, "ii", $address_id, $user_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {

    $_SESSION['error'] = "Address not found.";
    header("Location: b2b-checkout.php");
    exit;
}

$address = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

/*----------------------------------
DELETE ADDRESS
----------------------------------*/

$delete = mysqli_prepare($conn, "
    DELETE FROM b2b_user_addresses
    WHERE id=? AND user_id=?
");

mysqli_stmt_bind_param($delete, "ii", $address_id, $user_id);

if (mysqli_stmt_execute($delete)) {

    mysqli_stmt_close($delete);

    /*
    If deleted address was default,
    make another address default
    */

    if ($address['is_default'] == 1) {

        $next = mysqli_query($conn, "
            SELECT id
            FROM b2b_user_addresses
            WHERE user_id='$user_id'
            ORDER BY id ASC
            LIMIT 1
        ");

        if (mysqli_num_rows($next) > 0) {

            $row = mysqli_fetch_assoc($next);

            mysqli_query($conn, "
                UPDATE b2b_user_addresses
                SET is_default = 1
                WHERE id='" . (int)$row['id'] . "'
            ");
        }
    }

    $_SESSION['success'] = "Address deleted successfully.";

} else {

    $_SESSION['error'] = "Unable to delete address.";
    mysqli_stmt_close($delete);
}

header("Location: b2b-checkout.php");
exit;
?>