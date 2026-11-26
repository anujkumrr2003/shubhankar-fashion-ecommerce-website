<?php
session_start();
include "config.php";

header("Content-Type: application/json");

// Login Check
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        "status" => "login"
    ]);
    exit;
}

$user_id = (int)$_SESSION['user_id'];

/* POST Data */
$product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
$qty        = isset($_POST['qty']) ? (int)$_POST['qty'] : 1;
$variant_id = isset($_POST['variant_id']) ? (int)$_POST['variant_id'] : 0;

$size  = isset($_POST['size']) ? trim($_POST['size']) : '';
$color = isset($_POST['color']) ? trim($_POST['color']) : '';

/* Validation */
if ($product_id <= 0) {
    echo json_encode([
        "status" => "error",
        "message" => "Invalid Product"
    ]);
    exit;
}

if ($qty <= 0) {
    $qty = 1;
}

/* Check Variant */
if ($variant_id > 0) {

    $checkVariant = $conn->prepare("
        SELECT id
        FROM product_variants
        WHERE id=? AND product_id=?
        LIMIT 1
    ");

    $checkVariant->bind_param("ii", $variant_id, $product_id);
    $checkVariant->execute();

    if ($checkVariant->get_result()->num_rows == 0) {
        $variant_id = 0;
    }
}

/* Check Existing Cart */
$check = $conn->prepare("
    SELECT id, qty
    FROM cart
    WHERE
        user_id=?
        AND product_id=?
        AND variant_id=?
        AND size=?
        AND color=?
    LIMIT 1
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

$result = $check->get_result();

if ($result->num_rows > 0) {

    $row = $result->fetch_assoc();

    $newQty = $row['qty'] + $qty;

    $update = $conn->prepare("
        UPDATE cart
        SET
            qty=?,
            size=?,
            color=?
        WHERE id=?
    ");

    $update->bind_param(
        "issi",
        $newQty,
        $size,
        $color,
        $row['id']
    );

    $update->execute();

} else {

    $insert = $conn->prepare("
        INSERT INTO cart
        (
            user_id,
            product_id,
            variant_id,
            qty,
            size,
            color
        )
        VALUES
        (?, ?, ?, ?, ?, ?)
    ");

    $insert->bind_param(
        "iiiiss",
        $user_id,
        $product_id,
        $variant_id,
        $qty,
        $size,
        $color
    );

    $insert->execute();
}

/* Latest Cart Count */
$countStmt = $conn->prepare("
    SELECT COALESCE(SUM(qty),0) AS total
    FROM cart
    WHERE user_id=?
");

$countStmt->bind_param("i", $user_id);
$countStmt->execute();

$count = $countStmt->get_result()->fetch_assoc();

echo json_encode([
    "status" => "success",
    "count"  => (int)$count['total']
]);

exit;