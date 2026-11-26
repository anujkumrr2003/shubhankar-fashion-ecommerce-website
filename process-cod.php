<?php

session_start();
include "config.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require "phpMailer/Exception.php";
require "phpMailer/PHPMailer.php";
require "phpMailer/SMTP.php";

header("Content-Type: application/json");

/* TODO: replace with your real admin/sales inbox */
$admin_email = 'admin@yourcompany.com';
$admin_name  = 'Admin';

if (!isset($_SESSION['order_id'])) {

  echo json_encode([
    "status" => false,
    "message" => "Order not found."
  ]);
  exit;
}

$order_id = (int) $_SESSION['order_id'];

/*
|--------------------------------------------------------------------------
| Check Order
|--------------------------------------------------------------------------
*/



$stmt = mysqli_prepare($conn, "SELECT * FROM orders WHERE id = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, "i", $order_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (!$result || mysqli_num_rows($result) == 0) {

  echo json_encode([
    "status" => false,
    "message" => "Invalid order."
  ]);
  exit;
}

$order = mysqli_fetch_assoc($result);

$full_name = $order['full_name'];
$email     = $order['email'];
$phone     = $order['phone'];
$country   = $order['country'];
$state     = $order['state'];
$city      = $order['city'];
$address   = $order['address'];
$pincode   = $order['pincode'];

$billing_full_name    = trim($order['billing_full_name']);
$billing_phone        = trim($order['billing_phone']);
$billing_address      = trim($order['billing_address']);
$billing_city         = trim($order['billing_city']);
$billing_state        = trim($order['billing_state']);
$billing_region        = trim($order['billing_region']);
$billing_pincode      = trim($order['billing_pincode']);

$subtotal  = $order['subtotal'];
$discount  = $order['discount'];
$total     = $order['total'];
$currency  = $order['currency'];
$order_no  = $order['order_no'];

$currency_symbols = [
  'INR' => '&#8377;',
  'USD' => '$',
  'EUR' => '&euro;',
  'GBP' => '&pound;',
];

$currency_symbol = $currency_symbols[strtoupper($currency)] ?? ($currency . " ");

/*
|--------------------------------------------------------------------------
| Already Paid?
|--------------------------------------------------------------------------
*/

if (strtolower($order['payment_status']) == "paid") {

  echo json_encode([
    "status" => false,
    "message" => "This order has already been paid."
  ]);
  exit;
}

/*
|--------------------------------------------------------------------------
| Update COD Order
|--------------------------------------------------------------------------
*/

$update_stmt = mysqli_prepare($conn, "
    UPDATE orders
    SET
        payment_method = 'cod',
        payment_status = 'Pending',
        order_status = 'Confirmed'
    WHERE id = ?
");
mysqli_stmt_bind_param($update_stmt, "i", $order_id);
$update = mysqli_stmt_execute($update_stmt);

if (!$update) {

  echo json_encode([
    "status" => false,
    "message" => "Unable to update order."
  ]);
  exit;
}

/*
|--------------------------------------------------------------------------
| Build Order Items List
|--------------------------------------------------------------------------
*/

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

  $product_link  = "https://shubhankarfashion.com/product.php?id=" . urlencode($item['product_id']);
  $product_image = "https://shubhankarfashion.com/admin/uploads/" . rawurlencode($item['image']);
  $line_total    = $item['qty'] * $item['price'];
  $total_qty    += (int) $item['qty'];

  $items_list .= "
        <tr>
            <td style='padding:10px;'>
               <img src='{$product_image}' width='70' style='display:block;border-radius:8px;margin-bottom:8px;'>

<span style='color:#1a3d7c;font-weight:bold;'>
    " . htmlspecialchars($item['name']) . "
</span>
            </td>
<td>" . htmlspecialchars($item['size']) . "</td>

<td style='padding:10px;'>
    <span style='
        display:inline-block;
        width:16px;
        height:16px;
        border-radius:50%;
        border:1px solid #999;
        background:{$emailColor};
        vertical-align:middle;
        margin-right:6px;'>
    </span>
    " . htmlspecialchars($colorName) . "
</td>

<td>" . (int)$item['qty'] . "</td>
            <td>{$currency_symbol}" . number_format($item['price'], 2) . "</td>
            <td>{$currency_symbol}" . number_format($line_total, 2) . "</td>
        </tr>
    ";
    
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

$colorName = trim($item['color']);
$emailColor = $colorMap[strtolower($colorName)] ?? "#cccccc";
    
    
}

 

/*
|--------------------------------------------------------------------------
| Respond immediately, send email in the background
|--------------------------------------------------------------------------
*/

$response = json_encode([
  "status"  => true,
  "message" => "COD order placed successfully."
]);

if (function_exists('fastcgi_finish_request')) {

  echo $response;
  session_write_close();
  fastcgi_finish_request();
} else {

  ignore_user_abort(true);
  set_time_limit(0);
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

if (
    !empty($billing_full_name) ||
    !empty($billing_phone) ||
    !empty($billing_address)
) {

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
            {$billing_region},
            {$country} - {$billing_pincode}
        </td>
    </tr>";
}
/*
|--------------------------------------------------------------------------
| Send Customer Email (runs after the client already has its response)
|--------------------------------------------------------------------------
*/

try {
  $mail = new PHPMailer(true);

//   $mail->isSMTP();
//   $mail->Host       = 'smtp.gmail.com';
//   $mail->SMTPAuth   = true;
//   $mail->Username   = 'vipanujkumarlmp@gmail.com';
//   $mail->Password   = 'xwju bgei lyfm gghg'; // Gmail App Password
//   $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
//   $mail->Port       = 587;

//   $mail->setFrom('vipanujkumarlmp@gmail.com', 'By Shubhankar');

$mail->isSMTP();
$mail->Host = 'smtp.hostinger.com';
$mail->SMTPAuth = true;
$mail->Username = 'info@shubhankarfashion.com';
$mail->Password = 'Sunchit@11';
$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
$mail->Port = 587;
$mail->setFrom('info@shubhankarfashion.com', 'Shubhankar Fashion');

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
                        We've received your order and it has been confirmed for Cash on Delivery. Here's a summary of what you ordered.
                      </p>
                    </td>
                  </tr>

                  <!-- Payment Status Badge -->
                  <tr>
                    <td style='padding:16px 30px 0;'>
                      <div style='background-color:#fff8e6;border:1px solid #ffe8b3;border-radius:8px;padding:16px 18px;text-align:center;'>
                        <p style='margin:0 0 4px;color:#888;font-size:12px;text-transform:uppercase;letter-spacing:0.5px;'>Payment Method</p>
                        <p style='margin:0;color:#8a6d1f;font-size:18px;font-weight:700;'>Cash on Delivery &mdash; Payment Pending</p>
                        <p style='margin:6px 0 0;color:#a1873a;font-size:12px;'>Please keep {$currency_symbol}" . number_format($total, 2) . " ready at the time of delivery.</p>
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
                          <td style='padding:10px 0;color:#1a3d7c;font-size:16px;font-weight:700;text-align:right;border-top:2px solid #eef0f5;' colspan='3'>Total (Pay on Delivery)</td>
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
} catch (Exception $e) {
  // The client already has its response — just log the failure.
  error_log("COD confirmation email failed for order {$order_no}: " . $e->getMessage());
}

/*
|--------------------------------------------------------------------------
| Send Admin Notification Email
| Short internal alert with the order no, customer contact info, and totals
| so the team doesn't need to open the full customer email.
|--------------------------------------------------------------------------
*/

try {
  $mailAdmin = new PHPMailer(true);

  $mailAdmin->isSMTP();
  $mailAdmin->Host       = 'smtp.gmail.com';
  $mailAdmin->SMTPAuth   = true;
  $mailAdmin->Username   = 'vipanujkumarlmp@gmail.com';
  $mailAdmin->Password   = 'xwju bgei lyfm gghg'; // Gmail App Password
  $mailAdmin->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
  $mailAdmin->Port       = 587;

  $mailAdmin->setFrom('vipanujkumarlmp@gmail.com', 'By Shubhankar');
  $mailAdmin->addAddress($admin_email, $admin_name);
  // So the admin can hit "Reply" and land straight in the customer's inbox
  $mailAdmin->addReplyTo($email, $full_name);

  $mailAdmin->isHTML(true);
  $mailAdmin->Subject = "New COD Order #{$order_no} ({$full_name})";

  $mailAdmin->Body = "
    <div style='background-color:#f0f2f5;padding:30px 0;font-family:Segoe UI, Roboto, Helvetica, Arial, sans-serif;'>
      <table role='presentation' width='100%' cellpadding='0' cellspacing='0'>
        <tr>
          <td align='center'>
            <table role='presentation' width='560' cellpadding='0' cellspacing='0' style='background-color:#ffffff;border-radius:10px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.06);'>

              <tr>
                <td style='background:linear-gradient(135deg,#1a3d7c,#2e5aa8);padding:24px 28px;text-align:center;'>
                  <h1 style='margin:0;color:#ffffff;font-size:20px;'>New COD Order Received</h1>
                  <p style='margin:6px 0 0;color:#d6e0f5;font-size:13px;'>Order #{$order_no}</p>
                </td>
              </tr>

              <tr>
                <td style='padding:24px 28px 8px;'>
                  <table width='100%' cellpadding='0' cellspacing='0' style='background-color:#f8f9fc;border-radius:8px;border:1px solid #eef0f5;'>
                    <tr>
                      <td style='padding:10px 18px;color:#888;font-size:13px;width:45%;border-radius:8px 0 0 0;'>Order No.</td>
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
                  <p style='margin:0;color:#999;font-size:12px;'>Payment method: Cash on Delivery &mdash; log in to the admin panel for full order details.</p>
                </td>
              </tr>

            </table>
          </td>
        </tr>
      </table>
    </div>
    ";

  $mailAdmin->send();
} catch (Exception $e) {
  error_log("COD admin notification email failed for order {$order_no}: " . $e->getMessage());
}

exit;