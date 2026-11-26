<?php
 
session_start();
include "config.php";

// if (!isset($_SESSION['user_id'])) {
//     echo "login";
//     exit;
// }


if (!isset($_SESSION['user_id'])) {

    $_SESSION['redirect_url'] = $_SERVER['HTTP_REFERER'];

    echo "login";
    exit;
}

$user_id    = (int)$_SESSION['user_id'];
$product_id = (int)$_POST['product_id'];
$qty        = (int)$_POST['qty'];
$variant_id = isset($_POST['variant_id']) ? (int)$_POST['variant_id'] : 0;
$size       = isset($_POST['size']) ? trim($_POST['size']) : "";
$price = isset($_POST['price']) ? (float)$_POST['price'] : 0;
$color = mysqli_real_escape_string($conn, trim($_POST['color'] ?? ''));

if ($qty <= 0) {
    $qty = 1;
}

// Validate Variant
if ($variant_id > 0) {

    $variantCheck = $conn->prepare("
        SELECT id
        FROM product_variants
        WHERE id=? AND product_id=?
    ");

    $variantCheck->bind_param("ii", $variant_id, $product_id);
    $variantCheck->execute();

    if ($variantCheck->get_result()->num_rows == 0) {
        $variant_id = 0;
    }
}

/*
Same Product
+
Same Variant
+
Same Size
= Quantity Increase

Different Size
= New Cart Row
*/

$check = $conn->prepare("
    SELECT id, qty
    FROM cart
    WHERE
        user_id=?
        AND product_id=?
        AND variant_id=?
        AND size=?
        AND color=?
");

$check->bind_param(
    "iiiss",
    $user_id,
    $product_id,
    $variant_id,
    $size,
    $color
);

$check->execute();

$res = $check->get_result();

if ($res->num_rows > 0) {

    $row = $res->fetch_assoc();

    $newQty = $row['qty'] + $qty;

    $update = $conn->prepare("
      UPDATE cart
SET qty=?, price=?
WHERE id=?
    ");

   $update->bind_param("idi", $newQty, $price, $row['id']);
    $update->execute();

} else {

    $insert = $conn->prepare("
       INSERT INTO cart
(
    user_id,
    product_id,
    variant_id,
    size,
    color,
    price,
    qty
)
VALUES
(
    ?,?,?,?,?,?,?
)
    ");

    $insert->bind_param(
    "iiissdi",
    $user_id,
    $product_id,
    $variant_id,
    $size,
    $color,
    $price,
    $qty

);

    $insert->execute();
}

echo "success";
