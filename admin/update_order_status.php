<?php
session_start();
include "config.php";
error_reporting(0);

require 'phpMailer/PHPMailer.php';
require 'phpMailer/SMTP.php';
require 'phpMailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$order_id   = intval($_POST['order_id']);
$new_status = $_POST['status'] ?? '';

if (!$order_id || !$new_status) {
    echo "Invalid request";
    exit;
}

/* Fetch order details from the orders table.
   (This table does not have total_amount / final_amount /
   payment_method / razorpay_payment_id columns — those were
   from a different table and were the reason nothing matched.
   Using the actual orders columns: order_no, full_name, email,
   phone, address/city/state/country/pincode, company_name,
   gstin, subtotal, gst, discount, total, coupon_code,
   payment_status, order_status, currency.) */

$stmt = $conn->prepare("
    SELECT *
    FROM orders
    WHERE id = ?
    LIMIT 1
");

$stmt->bind_param("i", $order_id);
$stmt->execute();

$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    echo "Order not found";
    exit;
}

$order_no       = $order['order_no'];
$customer_name  = $order['full_name'];
$customer_email = $order['email'];
$phone          = $order['phone'];

$company_name   = $order['company_name'];
$gstin          = $order['gstin'];

$address        = $order['address'];
$city           = $order['city'];
$state          = $order['state'];
$country        = $order['country'];
$pincode        = $order['pincode'];

$subtotal       = $order['subtotal'];
$gst            = $order['gst'];
$discount       = $order['discount'];
$total          = $order['total'];
$coupon_code    = $order['coupon_code'];
$payment_status = $order['payment_status'];
$currency       = $order['currency'];

// Same currency symbol lookup used in the order-placement email.
$currency_symbols = [
    'INR' => '&#8377;',
    'USD' => '$',
    'EUR' => '&euro;',
    'GBP' => '&pound;',
];
$currency_symbol = $currency_symbols[strtoupper($currency)] ?? ($currency . ' ');

/* Update order status */

$stmt2 = $conn->prepare("UPDATE orders SET order_status=? WHERE id=?");
$stmt2->bind_param("si", $new_status, $order_id);
$stmt2->execute();

/* =========================================================
   INSTANT AJAX RESPONSE
   Send the success response back to the browser right away.
   Email sending keeps running on the server after the
   connection is closed. Reliable background execution
   requires PHP-FPM (fastcgi_finish_request).
   ========================================================= */

if (session_status() === PHP_SESSION_ACTIVE) {
    session_write_close();
}
ignore_user_abort(true);
set_time_limit(0);

echo "Order status updated & email sent";

if (function_exists('fastcgi_finish_request')) {
    // Best case: PHP-FPM. The AJAX caller gets this response now,
    // PHP keeps executing below in the background.
    fastcgi_finish_request();
} else {
    // Fallback for mod_php / CLI servers: best effort flush.
    while (ob_get_level() > 0) {
        ob_end_flush();
    }
    flush();
}

/* =========================================================
   EMAIL SENDING (runs after the response has been sent)
   ========================================================= */

// Guard against a missing customer email so PHPMailer doesn't throw
// on an empty addAddress() call.
if (!empty($customer_email)) {

    // Color-code the status badge, same convention as the other emails.
    $status_lower = strtolower($new_status);
    if (strpos($status_lower, 'deliver') !== false || strpos($status_lower, 'complete') !== false || strpos($status_lower, 'confirm') !== false || strpos($status_lower, 'ship') !== false) {
        $status_bg     = '#e6f7ec';
        $status_border = '#b7e6c9';
        $status_color  = '#1e7e42';
    } elseif (strpos($status_lower, 'cancel') !== false || strpos($status_lower, 'reject') !== false || strpos($status_lower, 'fail') !== false) {
        $status_bg     = '#fdecec';
        $status_border = '#f5c2c2';
        $status_color  = '#b02a2a';
    } else {
        $status_bg     = '#fdeef5';
        $status_border = '#f5c9dd';
        $status_color  = '#b0175f';
    }

    $mail = new PHPMailer(true);

    try {

        // $mail->isSMTP();
        // $mail->Host = "smtp.gmail.com";
        // $mail->SMTPAuth = true;

        // $mail->Username = "vipanujkumarlmp@gmail.com";
        // $mail->Password = "xwju bgei lyfm gghg";

        // $mail->SMTPSecure = "tls";
        // $mail->Port = 587;

        // $mail->setFrom("vipanujkumarlmp@gmail.com", "Your Store");
        
        $mail->isSMTP();
$mail->Host = 'smtp.hostinger.com';
$mail->SMTPAuth = true;
$mail->Username = 'info@shubhankarfashion.com';
$mail->Password = 'Sunchit@11';
$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
$mail->Port = 587;
$mail->setFrom('info@shubhankarfashion.com', 'Shubhankar Fashion');

        $mail->addAddress($customer_email, $customer_name);

        $mail->Subject = "Order Update - Order #{$order_no}";

        $mail->isHTML(true);

        $mail->Body = "
        <div style='background-color:#f0f2f5;padding:30px 0;font-family:Segoe UI, Roboto, Helvetica, Arial, sans-serif;'>
          <table role='presentation' width='100%' cellpadding='0' cellspacing='0'>
            <tr>
              <td align='center'>
                <table role='presentation' width='600' cellpadding='0' cellspacing='0' style='background-color:#ffffff;border-radius:10px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.06);'>

                  <!-- Header -->
                  <tr>
                    <td style='background-color:#b0175f;background:linear-gradient(135deg,#b0175f,#d1467f);padding:28px 30px;text-align:center;'>
                      <h1 style='margin:0;color:#ffffff;font-size:22px;letter-spacing:0.5px;'>Order Status Updated</h1>
                      <p style='margin:6px 0 0;color:#f6d6e6;font-size:13px;'>Order #{$order_no}</p>
                    </td>
                  </tr>

                  <!-- Greeting -->
                  <tr>
                    <td style='padding:28px 30px 10px;'>
                      <h2 style='margin:0 0 8px;color:#b0175f;font-size:18px;'>Hello, {$customer_name}</h2>
                      <p style='margin:0;color:#555;font-size:14px;line-height:1.6;'>
                        Your order status has just been updated. Here are the latest details:
                      </p>
                    </td>
                  </tr>

                  <!-- Status Badge -->
                  <tr>
                    <td style='padding:16px 30px 0;'>
                      <div style='background-color:{$status_bg};border:1px solid {$status_border};border-radius:8px;padding:16px 18px;text-align:center;'>
                        <p style='margin:0 0 4px;color:#888;font-size:12px;text-transform:uppercase;letter-spacing:0.5px;'>Order Status</p>
                        <p style='margin:0;color:{$status_color};font-size:18px;font-weight:700;'>{$new_status}</p>
                      </div>
                    </td>
                  </tr>

                  <!-- Order / Customer Details -->
                  <tr>
                    <td style='padding:22px 30px 0;'>
                      <table width='100%' cellpadding='0' cellspacing='0' style='background-color:#f8f9fc;border-radius:8px;border:1px solid #eef0f5;'>
                        <tr>
                          <td style='padding:10px 18px;color:#888;font-size:13px;width:45%;border-radius:8px 0 0 0;'>Order No.</td>
                          <td style='padding:10px 18px;color:#222;font-size:13px;font-weight:600;border-radius:0 8px 0 0;'>{$order_no}</td>
                        </tr>
                        <tr>
                          <td style='padding:10px 18px;color:#888;font-size:13px;background-color:#ffffff;'>Phone</td>
                          <td style='padding:10px 18px;color:#222;font-size:13px;font-weight:600;background-color:#ffffff;'>{$phone}</td>
                        </tr>
                        <tr>
                          <td style='padding:10px 18px;color:#888;font-size:13px;'>Shipping Address</td>
                          <td style='padding:10px 18px;color:#222;font-size:13px;font-weight:600;'>{$address}, {$city}, {$state}, {$country} - {$pincode}</td>
                        </tr>
                         
                        <tr>
                          <td style='padding:10px 18px;color:#888;font-size:13px;border-radius:0 0 0 8px;'>Payment Status</td>
                          <td style='padding:10px 18px;color:#222;font-size:13px;font-weight:600;border-radius:0 0 8px 0;'>{$payment_status}</td>
                        </tr>
                      </table>
                    </td>
                  </tr>

                  <!-- Price Summary -->
                  <tr>
                    <td style='padding:26px 30px 0;'>
                      <h3 style='margin:0 0 12px;color:#b0175f;font-size:16px;'>Order Summary</h3>
                      <table width='100%' cellpadding='0' cellspacing='0'>
                        <tr>
                          <td style='padding:6px 0;color:#666;font-size:13px;text-align:right;' colspan='3'>Subtotal</td>
                          <td style='padding:6px 0;color:#333;font-size:13px;text-align:right;width:120px;'>{$currency_symbol}" . number_format((float)$subtotal, 2) . "</td>
                        </tr>
                        <tr>
                          <td style='padding:6px 0;color:#666;font-size:13px;text-align:right;' colspan='3'>GST</td>
                          <td style='padding:6px 0;color:#333;font-size:13px;text-align:right;width:120px;'>{$currency_symbol}" . number_format((float)$gst, 2) . "</td>
                        </tr>
                        <tr>
                          <td style='padding:6px 0;color:#666;font-size:13px;text-align:right;' colspan='3'>Discount" . ($coupon_code ? " ({$coupon_code})" : "") . "</td>
                          <td style='padding:6px 0;color:#1e7e42;font-size:13px;text-align:right;width:120px;'>- {$currency_symbol}" . number_format((float)$discount, 2) . "</td>
                        </tr>
                        <tr>
                          <td style='padding:10px 0;color:#b0175f;font-size:16px;font-weight:700;text-align:right;border-top:2px solid #eef0f5;' colspan='3'>Total</td>
                          <td style='padding:10px 0;color:#b0175f;font-size:16px;font-weight:700;text-align:right;border-top:2px solid #eef0f5;width:120px;'>{$currency_symbol}" . number_format((float)$total, 2) . "</td>
                        </tr>
                      </table>
                    </td>
                  </tr>

                  <!-- Footer -->
                  <tr>
                    <td style='padding:30px;text-align:center;'>
                      <p style='margin:0 0 6px;color:#333;font-size:14px;'>Thank you for shopping with us!</p>
                      <p style='margin:0;color:#999;font-size:12px;'>This is an automated notification &mdash; please do not reply directly to this message.</p>
                    </td>
                  </tr>

                  <tr>
                    <td style='background-color:#b0175f;padding:16px;text-align:center;'>
                      <p style='margin:0;color:#f6d6e6;font-size:12px;'>&copy; " . date('Y') . " Your Store. All rights reserved.</p>
                    </td>
                  </tr>

                </table>
              </td>
            </tr>
          </table>
        </div>
        ";

        $mail->send();

    } catch (Exception $e) {
        // Mail failed silently — order status is already updated either way.
        error_log("Order status mail failed for order #{$order_no}: " . $mail->ErrorInfo);
    }
}

exit;