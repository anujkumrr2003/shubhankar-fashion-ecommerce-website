<?php
session_start();
include "config.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

if (empty($_POST['code']) && empty($_GET['code'])) {
    $_SESSION['coupon_error'] = "Please enter coupon code.";
    header("Location: checkout.php");
    exit;
}

$rawCode = $_POST['code'] ?? $_GET['code'];
$code = mysqli_real_escape_string($conn, strtoupper(trim($rawCode)));

$sql = "SELECT * FROM promo_codes
        WHERE code='$code'
        AND status='1'
        AND CURDATE() BETWEEN start_date AND end_date
        LIMIT 1";

$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) == 0) {
    $_SESSION['coupon_error'] = "Invalid Coupon Code.";
    header("Location: checkout.php");
    exit;
}

$coupon = mysqli_fetch_assoc($result);

$subtotal = 0;

// Pull product price + variant_id per cart row, same as cart.php / checkout.php
$cart = mysqli_query($conn, "
SELECT p.price, c.qty, c.variant_id, c.product_id
FROM cart c
INNER JOIN products p
ON p.id=c.product_id
WHERE c.user_id='$user_id'
");

// Look up the variant price separately for any row that has a variant_id,
// same pattern used in cart.php / checkout.php.
$variantStmt = $conn->prepare(
    "SELECT variant_price
     FROM product_variants
     WHERE id=? AND product_id=?
     LIMIT 1"
);

while ($row = mysqli_fetch_assoc($cart)) {

    $unitPrice = $row['price'];

    if ((int)$row['variant_id'] > 0) {

        $vId = (int)$row['variant_id'];
        $pId = (int)$row['product_id'];

        $variantStmt->bind_param("ii", $vId, $pId);
        $variantStmt->execute();
        $vResult = $variantStmt->get_result();

        if ($vResult && $vResult->num_rows > 0) {
            $vRow = $vResult->fetch_assoc();
            $unitPrice = $vRow['variant_price'];
        }
        // If the variant no longer exists, silently falls back to base product price.
    }

    $subtotal += $unitPrice * $row['qty'];
}

if ($subtotal < $coupon['minimum_amount']) {
    $_SESSION['coupon_error'] = "Minimum order amount ₹" . number_format($coupon['minimum_amount'], 2);
    header("Location: checkout.php");
    exit;
}

if (strtolower($coupon['discount_type']) == "flat") {
    $discount = $coupon['discount_value'];
} else {
    $discount = ($subtotal * $coupon['discount_value']) / 100;
}

if ($discount > $subtotal) {
    $discount = $subtotal;
}

$_SESSION['coupon'] = $coupon['code'];
$_SESSION['discount'] = $discount;
$_SESSION['coupon_success'] = "Coupon Applied Successfully.";

header("Location: checkout.php");
exit;