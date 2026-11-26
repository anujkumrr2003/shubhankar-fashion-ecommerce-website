<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include "config.php";

require 'razorpay-php/Razorpay.php';

use Razorpay\Api\Api;

if (!isset($_SESSION['order_id'])) {
    echo json_encode([
        "status" => false,
        "message" => "Order not found."
    ]);
    exit;
}

$order_id = (int) $_SESSION['order_id'];

// Get payment method from frontend
$payment_method = isset($_POST['payment_method'])
    ? mysqli_real_escape_string($conn, $_POST['payment_method'])
    : 'card';

// First check if order exists
$sql = mysqli_query($conn, "
    SELECT * FROM orders
    WHERE id = '$order_id'
    LIMIT 1
");

if (!$sql || mysqli_num_rows($sql) == 0) {
    echo json_encode([
        "status" => false,
        "message" => "Invalid Order."
    ]);
    exit;
}

$order = mysqli_fetch_assoc($sql);

// Don't let someone re-pay for an order that's already paid
// if (isset($order['payment_status']) && $order['payment_status'] === 'Paid') {
if (strtolower($order['payment_status']) == 'paid') {
    echo json_encode([
        "status" => false,
        "message" => "This order has already been paid for."
    ]);
    exit;
}

// Update payment method in database
// mysqli_query($conn, "UPDATE orders
//     SET payment_method = '$payment_method'
//     WHERE id = '$order_id'");

$update = mysqli_query($conn, "
UPDATE orders
SET payment_method='$payment_method'
WHERE id='$order_id'
");

if (!$update) {
    echo json_encode([
        "status" => false,
        "message" => "Unable to update payment method."
    ]);
    exit;
}


/*
|--------------------------------------------------------------------------
| Razorpay Credentials
|--------------------------------------------------------------------------
| TODO: move these into config.php (ideally outside the web root, or as
| environment variables) instead of hardcoding them here.
*/

$key_id     = "rzp_live_TjPco4ozYdR4s0";
$key_secret = "pJEDU0iTdmfaPASXwXH7TnKt";

$api = new Api($key_id, $key_secret);

/*
|--------------------------------------------------------------------------
| Currency + amount
|--------------------------------------------------------------------------
| Razorpay test-mode accounts (without international payments enabled)
| only accept INR. An empty/invalid currency is a common silent cause
| of checkout failures, so we default safely here.
*/
$currency = strtoupper(trim($order['currency']));
if ($currency === '') {
    $currency = 'INR';
}

// Amount MUST be an integer number of the smallest currency unit (paise for
// INR), not a float. orders.total is now always computed server-side in
// place-order.php from real cart/coupon data, so this is trustworthy.
$amount = (int) round(((float) $order['total']) * 100);

if ($amount <= 0) {
    echo json_encode([
        "status" => false,
        "message" => "Invalid order amount."
    ]);
    exit;
}

try {
    $razorpayOrder = $api->order->create([
        "receipt"         => $order['order_no'],
        "amount"          => $amount,
        "currency"        => $currency,
        "payment_capture" => 1
    ]);

    $razorpay_order_id = $razorpayOrder['id'];

    // Save Razorpay order ID so verify-payment.php can confirm it matches later
    // mysqli_query($conn, "
    //     UPDATE orders
    //     SET razorpay_order_id = '$razorpay_order_id'
    //     WHERE id = '$order_id'
    // ");


    mysqli_query($conn, "
UPDATE orders
SET razorpay_order_id = '$razorpay_order_id'
WHERE id = '$order_id'
");

    echo json_encode([
        "status"            => true,
        "key"               => $key_id,
        "order_id"          => $order_id,
        "razorpay_order_id" => $razorpay_order_id,
        "amount"            => $amount,
        "currency"          => $currency,
        "name"              => $order['full_name'],
        "email"             => $order['email'],
        "contact"           => $order['phone'],
        "order_no"          => $order['order_no']
    ]);

} catch (Exception $e) {
    echo json_encode([
        "status"  => false,
        "message" => $e->getMessage()
    ]);
}
