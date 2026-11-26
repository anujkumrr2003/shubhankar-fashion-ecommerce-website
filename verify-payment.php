<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include "config.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require "phpMailer/Exception.php";
require "phpMailer/PHPMailer.php";
require "phpMailer/SMTP.php";

require 'razorpay-php/Razorpay.php';

use Razorpay\Api\Api;

 

$key_id     = "rzp_live_TjPco4ozYdR4s0";
$key_secret = "pJEDU0iTdmfaPASXwXH7TnKt";

$api = new Api($key_id, $key_secret);

/* ---------------- Admin notification recipient ---------------- */
/* TODO: replace with your real admin/sales inbox */
$admin_email = 'admin@yourcompany.com';
$admin_name  = 'Admin';

if (!isset($_SESSION['order_id'])) {
  echo json_encode([
    "status"  => false,
    "message" => "Session expired. Please log in and try again."
  ]);
  exit;
}

$order_id            = (int) $_SESSION['order_id'];
$razorpay_payment_id = $_POST['razorpay_payment_id'] ?? '';
$razorpay_order_id   = $_POST['razorpay_order_id'] ?? '';
$razorpay_signature  = $_POST['razorpay_signature'] ?? '';

if (empty($razorpay_payment_id) || empty($razorpay_order_id) || empty($razorpay_signature)) {
  echo json_encode([
    "status"  => false,
    "message" => "Missing payment details."
  ]);
  exit;
}

// ---- Load the order with a prepared statement (no SQL injection) ----
$stmt = mysqli_prepare($conn, "SELECT * FROM orders WHERE id = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, "i", $order_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (!$result || mysqli_num_rows($result) == 0) {
  echo json_encode([
    "status"  => false,
    "message" => "Order not found."
  ]);
  exit;
}

$order = mysqli_fetch_assoc($result);

if (strtolower($order['payment_status']) == 'paid') {
  echo json_encode([
    "status"  => true,
    "message" => "Order already paid."
  ]);
  exit;
}

$user_id   = $order['user_id'];
$full_name = $order['full_name'];
$email     = $order['email'];
$phone     = $order['phone'];
$country   = $order['country'];
$state     = $order['state'];
$city      = $order['city'];
$address   = $order['address'];
$pincode   = $order['pincode'];

$subtotal  = $order['subtotal'];
$discount  = $order['discount'];
$total     = $order['total'];
$currency  = $order['currency'];
$order_no  = $order['order_no'];

$billing_full_name = trim($order['billing_full_name'] ?? '');
$billing_phone     = trim($order['billing_phone'] ?? '');
$billing_address   = trim($order['billing_address'] ?? '');
$billing_city      = trim($order['billing_city'] ?? '');
$billing_state     = trim($order['billing_state'] ?? '');
$billing_region   = trim($order['billing_region'] ?? '');
$billing_pincode   = trim($order['billing_pincode'] ?? '');

$currency_symbols = [
  'INR' => '&#8377;',
  'USD' => '$',
  'EUR' => '&euro;',
  'GBP' => '&pound;',
];
$currency_symbol = $currency_symbols[strtoupper($currency)] ?? ($currency . " ");

if (empty($order['razorpay_order_id']) || $order['razorpay_order_id'] !== $razorpay_order_id) {
  echo json_encode([
    "status"  => false,
    "message" => "Order mismatch. Please contact support."
  ]);
  exit;
}

try {

  $attributes = [
    "razorpay_order_id"   => $razorpay_order_id,
    "razorpay_payment_id" => $razorpay_payment_id,
    "razorpay_signature"  => $razorpay_signature
  ];

  // Throws Razorpay\Api\Errors\SignatureVerificationError on failure
  $api->utility->verifyPaymentSignature($attributes);

  // ---- Update the order with a prepared statement ----
  $update_stmt = mysqli_prepare($conn, "
        UPDATE orders SET
            payment_status = 'Paid',
            order_status = 'Confirmed',
            razorpay_payment_id = ?,
            razorpay_order_id = ?,
            razorpay_signature = ?
        WHERE id = ?
    ");
  mysqli_stmt_bind_param(
    $update_stmt,
    "sssi",
    $razorpay_payment_id,
    $razorpay_order_id,
    $razorpay_signature,
    $order_id
  );
  $update = mysqli_stmt_execute($update_stmt);

  if (!$update) {
    throw new Exception("Unable to update order.");
  }

  // ---- Build the order-items rows for the email ----
  // NOTE: change the table/column names below if yours differ
  // (order_items: order_id, product_name, size, qty, price)
  $items_list = "";
  $total_qty  = 0;

  $items_stmt = mysqli_prepare($conn, "
SELECT
oi.*,
p.name,
p.image
FROM order_items oi
LEFT JOIN products p
ON p.id = oi.product_id
WHERE oi.order_id = ?
    ");
  mysqli_stmt_bind_param($items_stmt, "i", $order_id);
  mysqli_stmt_execute($items_stmt);
  $items_result = mysqli_stmt_get_result($items_stmt);

  while ($item = mysqli_fetch_assoc($items_result)) {
    $line_total   = $item['qty'] * $item['price'];
    $productImage = "https://shubhankarfashion.com/admin/uploads/" . $item['image'];
    $productLink  = "https://shubhankarfashion.com/product.php?id=" . $item['product_id'];

    $total_qty += (int) $item['qty'];
    
    $colorMap = [
    "black" => "#000000",
    "white" => "#ffffff",
    "red" => "#ff0000",
    "blue" => "#0066ff",
    "green" => "#28a745",
    "yellow" => "#ffd700",
    "orange" => "#ff7f00",
    "pink" => "#ff69b4",
    "purple" => "#800080",
    "brown" => "#8b4513",
    "grey" => "#808080",
    "gray" => "#808080",
    "navy" => "#001f54",
    "maroon" => "#800000",
    "olive" => "#808000",
    "sky blue" => "#87ceeb",
    "beige" => "#f5f5dc",
    "cream" => "#fffdd0",
    "gold" => "#d4af37",
    "silver" => "#c0c0c0"
];

$colorCode = strtolower(trim($item['color']));
$emailColor = $colorMap[$colorCode] ?? $item['color'];


    $items_list .= "
<tr>

<td style='padding:12px;border-top:1px solid #eee;'>

<img src='{$productImage}'
width='70'
style='display:block;border-radius:8px;margin-bottom:8px;'>

<strong>" . htmlspecialchars($item['name']) . "</strong>

</td>

<td style='padding:12px;border-top:1px solid #eee;'>
" . htmlspecialchars($item['size']) . "
</td>

<td style='padding:12px;border-top:1px solid #eee;'>

<span style='
display:inline-block;
width:16px;
height:16px;
border-radius:50%;
border:1px solid #bbb;
background:{$emailColor};
vertical-align:middle;
margin-right:8px;'>
</span>

" . htmlspecialchars($item['color']) . "

</td>

<td style='padding:12px;text-align:center;border-top:1px solid #eee;'>
{$item['qty']}
</td>

<td style='padding:12px;text-align:right;border-top:1px solid #eee;'>
{$currency_symbol}" . number_format($item['price'],2) . "
</td>

<td style='padding:12px;text-align:right;border-top:1px solid #eee;'>
{$currency_symbol}" . number_format($line_total,2) . "
</td>

</tr>";
  }

  // =================================================================
  // Respond to the browser IMMEDIATELY (instant success popup).
  // Everything after this point still runs on the server, but the
  // client has already received its answer and moved on.
  // =================================================================
  $response = json_encode(["status" => true]);

  if (function_exists('fastcgi_finish_request')) {
    // Works when PHP runs under PHP-FPM (most common setup with
    // Nginx, and Apache+mod_fcgid/mod_proxy_fcgi)
    header('Content-Type: application/json');
    echo $response;
    session_write_close();     // release the session lock
    fastcgi_finish_request();  // flush the response to the client now
  } else {
    // Fallback for plain mod_php / CLI servers without php-fpm
    ignore_user_abort(true);
    set_time_limit(0);
    header('Content-Type: application/json');
    header('Content-Length: ' . strlen($response));
    header('Connection: close');
    echo $response;
    session_write_close();
    if (ob_get_level() > 0) {
      ob_end_flush();
    }
    flush();
  }



  $billing_html = "";

  $has_billing = !empty($billing_full_name) && !empty($billing_address);

  if ($has_billing) {

    $billing_html = "
    <tr>
        <td style='padding:10px 18px;color:#888;font-size:13px;background:#ffffff;'>
            Billing Name
        </td>

        <td style='padding:10px 18px;color:#222;font-size:13px;font-weight:600;background:#ffffff;'>
            {$billing_full_name}<br>
            {$billing_phone}
        </td>
    </tr>

    <tr>
        <td style='padding:10px 18px;color:#888;font-size:13px;'>
            Billing Address
        </td>

        <td style='padding:10px 18px;color:#222;font-size:13px;font-weight:600;'>
            {$billing_address},
            {$billing_city},
            {$billing_state},
            {$billing_region}
            - {$billing_pincode}
        </td>
    </tr>
    ";
  }


  // ---- From here on, the client is gone: send the customer email in the background ----
  try {
    $mail = new PHPMailer(true);

    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'vipanujkumarlmp@gmail.com';
    $mail->Password   = 'xwju bgei lyfm gghg'; // Gmail App Password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    $mail->setFrom('vipanujkumarlmp@gmail.com', 'By Shubhankar');
    $mail->addAddress($email, $full_name);

    $mail->isHTML(true);
    $mail->Subject = "Order Confirmation - {$order_no}";

    $mail->Body = "
            <div style='background-color:#f0f2f5;padding:30px 0;font-family:Segoe UI, Roboto, Helvetica, Arial, sans-serif;'>
              <table role='presentation' width='100%' cellpadding='0' cellspacing='0'>
                <tr>
                  <td align='center'>
                    <table role='presentation' width='600' cellpadding='0' cellspacing='0' style='background-color:#ffffff;border-radius:10px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.06);'>
                      <!-- Header -->
                      <tr>
                        <td style='background-color:#1a3d7c;background:linear-gradient(135deg,#1a3d7c,#2e5aa8);padding:28px 30px;text-align:center;'>
                          <h1 style='margin:0;color:#ffffff;font-size:22px;letter-spacing:0.5px;'>Order Placed</h1>
                          <p style='margin:6px 0 0;color:#d6e0f5;font-size:13px;'>Order #{$order_no}</p>
                        </td>
                      </tr>

                      <!-- Greeting -->
                      <tr>
                        <td style='padding:28px 30px 10px;'>
                          <h2 style='margin:0 0 8px;color:#1a3d7c;font-size:18px;'>Thank you, {$full_name}!</h2>
                          <p style='margin:0;color:#555;font-size:14px;line-height:1.6;'>
                            We've received your order and your payment has been confirmed. Here's a summary of what you ordered.
                          </p>
                        </td>
                      </tr>

                      <!-- Payment Status Badge -->
                      <tr>
                        <td style='padding:16px 30px 0;'>
                          <div style='background-color:#e9f7ef;border:1px solid #b6e5c8;border-radius:8px;padding:16px 18px;text-align:center;'>
                            <p style='margin:0 0 4px;color:#888;font-size:12px;text-transform:uppercase;letter-spacing:0.5px;'>Payment Status</p>
                            <p style='margin:0;color:#1e7e42;font-size:18px;font-weight:700;'>Paid</p>
                          </div>
                        </td>
                      </tr>

                      <!-- Order / Customer Details -->
                      <tr>
                        <td style='padding:22px 30px 0;'>
                          <table width='100%' cellpadding='0' cellspacing='0' style='background-color:#f8f9fc;border-radius:8px;border:1px solid #eef0f5;'>
                            <tr>
                              <td style='padding:10px 18px;color:#888;font-size:13px;width:40%;border-radius:8px 0 0 0;'>Order No.</td>
                              <td style='padding:10px 18px;color:#222;font-size:13px;font-weight:600;border-radius:0 8px 0 0;'>{$order_no}</td>
                            </tr>
                            <tr>
                              <td style='padding:10px 18px;color:#888;font-size:13px;background-color:#ffffff;'>Email</td>
                              <td style='padding:10px 18px;color:#222;font-size:13px;font-weight:600;background-color:#ffffff;'>{$email}</td>
                            </tr>
                            <tr>
                              <td style='padding:10px 18px;color:#888;font-size:13px;'>Phone</td>
                              <td style='padding:10px 18px;color:#222;font-size:13px;font-weight:600;'>{$phone}</td>
                            </tr>
                            <tr>
                              <td style='padding:10px 18px;color:#888;font-size:13px;background-color:#ffffff;'>Shipping Address</td>
                              <td style='padding:10px 18px;color:#222;font-size:13px;font-weight:600;background-color:#ffffff;'>{$address}, {$city}, {$state}, {$country} - {$pincode}</td>
                            </tr>
                            {$billing_html}
                            <tr>
                              <td style='padding:10px 18px;color:#888;font-size:13px;background-color:#ffffff;'>Total Quantity</td>
                              <td style='padding:10px 18px;color:#222;font-size:13px;font-weight:600;background-color:#ffffff;'>{$total_qty}</td>
                            </tr>
                            <tr>
                              <td style='padding:10px 18px;color:#888;font-size:13px;border-radius:0 0 0 8px;'>Total Amount</td>
                              <td style='padding:10px 18px;color:#1f8a4c;font-size:14px;font-weight:700;border-radius:0 0 8px 0;'>{$currency_symbol}" . number_format($total, 2) . "</td>
                            </tr>
                          </table>
                        </td>
                      </tr>

                      <!-- Items Table -->
                      <tr>
                        <td style='padding:26px 30px 0;'>
                          <h3 style='margin:0 0 12px;color:#1a3d7c;font-size:16px;'>Order Items</h3>
                          <table width='100%' cellpadding='0' cellspacing='0' style='border:1px solid #eef0f5;border-radius:8px;overflow:hidden;'>
                            <tr style='background-color:#1a3d7c;'>
                              <th style='padding:12px 15px;color:#ffffff;font-size:13px;text-align:left;'>Product</th>
                              <th style='padding:12px 15px;color:#ffffff;font-size:13px;text-align:left;'>Size</th>
                              <th style='padding:12px 15px;color:#ffffff;font-size:13px;text-align:left;'>Color</th>
                              <th style='padding:12px 15px;color:#ffffff;font-size:13px;text-align:center;'>Qty</th>
                              <th style='padding:12px 15px;color:#ffffff;font-size:13px;text-align:right;'>Price</th>
                              <th style='padding:12px 15px;color:#ffffff;font-size:13px;text-align:right;'>Total</th>
                            </tr>
                            {$items_list}
                          </table>
                        </td>
                      </tr>

                      <!-- Price Summary -->
                      <tr>
                        <td style='padding:20px 30px 0;'>
                          <table width='100%' cellpadding='0' cellspacing='0'>
                            <tr>
                              <td style='padding:6px 0;color:#666;font-size:13px;text-align:right;' colspan='3'>Subtotal</td>
                              <td style='padding:6px 0;color:#333;font-size:13px;text-align:right;width:120px;'>{$currency_symbol}" . number_format($subtotal, 2) . "</td>
                            </tr>
                            <tr>
                              <td style='padding:6px 0;color:#666;font-size:13px;text-align:right;' colspan='3'>Discount</td>
                              <td style='padding:6px 0;color:#1e7e42;font-size:13px;text-align:right;width:120px;'>- {$currency_symbol}" . number_format($discount, 2) . "</td>
                            </tr>
                            <tr>
                              <td style='padding:10px 0;color:#1a3d7c;font-size:16px;font-weight:700;text-align:right;border-top:2px solid #eef0f5;' colspan='3'>Total</td>
                              <td style='padding:10px 0;color:#1a3d7c;font-size:16px;font-weight:700;text-align:right;border-top:2px solid #eef0f5;width:120px;'>{$currency_symbol}" . number_format($total, 2) . "</td>
                            </tr>
                          </table>
                        </td>
                      </tr>

                      <!-- Footer -->
                      <tr>
                        <td style='padding:30px;text-align:center;'>
                          <p style='margin:0 0 6px;color:#333;font-size:14px;'>Thank you for shopping with us!</p>
                          <p style='margin:0;color:#999;font-size:12px;'>This is an automated confirmation email &mdash; please do not reply directly to this message.</p>
                        </td>
                      </tr>

                      <tr>
                        <td style='background-color:#1a3d7c;padding:16px;text-align:center;'>
                          <p style='margin:0;color:#c9d7f0;font-size:12px;'>&copy; " . date('Y') . " Your Company. All rights reserved.</p>
                        </td>
                      </tr>

                    </table>
                  </td>
                </tr>
              </table>
            </div>
            ";

    $mail->send();
  } catch (\Exception $e) {
    // The client already got their success response, so just log this
    // instead of trying to echo/return anything.
    error_log("Order confirmation email failed for order {$order_no}: " . $e->getMessage());
  }

  // ---- Send admin notification email (also runs after the client is gone) ----
  try {
    $mailAdmin = new PHPMailer(true);

    $mailAdmin->isSMTP();
    $mailAdmin->Host       = 'smtp.gmail.com';
    $mailAdmin->SMTPAuth   = true;
    $mailAdmin->Username   = 'vipanujkumarlmp@gmail.com';
    $mailAdmin->Password   = 'xwju bgei lyfm gghg';
    $mailAdmin->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mailAdmin->Port       = 587;

    $mailAdmin->setFrom('vipanujkumarlmp@gmail.com', 'By Shubhankar');
    $mailAdmin->addAddress($admin_email, $admin_name);
    // So the admin can hit "Reply" and land straight in the customer's inbox
    $mailAdmin->addReplyTo($email, $full_name);

    $mailAdmin->isHTML(true);
    $mailAdmin->Subject = "Payment Received Order #{$order_no} — {$full_name}";

    $mailAdmin->Body = "
    <div style='background-color:#f0f2f5;padding:30px 0;font-family:Segoe UI, Roboto, Helvetica, Arial, sans-serif;'>
      <table role='presentation' width='100%' cellpadding='0' cellspacing='0'>
        <tr>
          <td align='center'>
            <table role='presentation' width='560' cellpadding='0' cellspacing='0' style='background-color:#ffffff;border-radius:10px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.06);'>

              <tr>
                <td style='background:linear-gradient(135deg,#1a3d7c,#2e5aa8);padding:24px 28px;text-align:center;'>
                  <h1 style='margin:0;color:#ffffff;font-size:20px;'>Payment Received</h1>
                  <p style='margin:6px 0 0;color:#d6e0f5;font-size:13px;'>Order #{$order_no}</p>
                </td>
              </tr>

              <tr>
                <td style='padding:24px 28px 8px;'>
                  <table width='100%' cellpadding='0' cellspacing='0' style='background-color:#f8f9fc;border-radius:8px;border:1px solid #eef0f5;'>
                    <tr>
                      <td style='padding:10px 18px;color:#888;font-size:13px;width:45%;border-radius:8px 0 0 0;'>Order ID</td>
                      <td style='padding:10px 18px;color:#222;font-size:13px;font-weight:600;border-radius:0 8px 0 0;'>{$order_no}</td>
                    </tr>
                    <tr>
                      <td style='padding:10px 18px;color:#888;font-size:13px;background-color:#ffffff;'>Customer Name</td>
                      <td style='padding:10px 18px;color:#222;font-size:13px;font-weight:600;background-color:#ffffff;'>{$full_name}</td>
                    </tr>
                    <tr>
                      <td style='padding:10px 18px;color:#888;font-size:13px;'>Email</td>
                      <td style='padding:10px 18px;color:#222;font-size:13px;font-weight:600;'>{$email}</td>
                    </tr>
                    <tr>
                      <td style='padding:10px 18px;color:#888;font-size:13px;background-color:#ffffff;'>Contact No.</td>
                      <td style='padding:10px 18px;color:#222;font-size:13px;font-weight:600;background-color:#ffffff;'>{$phone}</td>
                    </tr>
                    <tr>
                      <td style='padding:10px 18px;color:#888;font-size:13px;'>Total Quantity</td>
                      <td style='padding:10px 18px;color:#222;font-size:13px;font-weight:600;'>{$total_qty}</td>
                    </tr>
                    <tr>
                      <td style='padding:10px 18px;color:#888;font-size:13px;background-color:#ffffff;border-radius:0 0 0 8px;'>Total Amount</td>
                      <td style='padding:10px 18px;color:#1f8a4c;font-size:14px;font-weight:700;background-color:#ffffff;border-radius:0 0 8px 0;'>{$currency_symbol}" . number_format($total, 2) . "</td>
                    </tr>
                  </table>
                </td>
              </tr>

              <tr>
                <td style='padding:20px 28px 28px;text-align:center;'>
                  <p style='margin:0;color:#999;font-size:12px;'>Payment method: Razorpay (Paid). Log in to the admin panel to view full order details and shipping/billing address.</p>
                </td>
              </tr>

            </table>
          </td>
        </tr>
      </table>
    </div>
    ";

    $mailAdmin->send();
  } catch (\Exception $e) {
    error_log("Admin notification mail failed for order {$order_no}: " . $mailAdmin->ErrorInfo);
  }

  exit;
} catch (\Exception $e) {
  echo json_encode([
    "status"  => false,
    "message" => $e->getMessage()
  ]);
  exit;
}
