<?php


session_start();
include "config.php";

if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit;
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require "phpMailer/Exception.php";
require "phpMailer/PHPMailer.php";
require "phpMailer/SMTP.php";

$user_id = $_SESSION['user_id'];

$full_name    = mysqli_real_escape_string($conn, $_POST['full_name']);
$email        = mysqli_real_escape_string($conn, $_POST['email']);
$phone        = mysqli_real_escape_string($conn, $_POST['phone']);
$country      = mysqli_real_escape_string($conn, $_POST['country']);
$state        = mysqli_real_escape_string($conn, $_POST['state']);
$city         = mysqli_real_escape_string($conn, $_POST['city']);
$currency     = mysqli_real_escape_string($conn, $_POST['currency']);
$currency_rate = (float)$_POST['currency_rate'];

if ($currency_rate <= 0) {
  $currency_rate = 1;
}

$address = mysqli_real_escape_string(
  $conn,
  trim($_POST['address'] . " " . $_POST['address2'])
);

$pincode      = mysqli_real_escape_string($conn, $_POST['pincode']);
$company_name = mysqli_real_escape_string($conn, $_POST['company_name']);
$gstin        = mysqli_real_escape_string($conn, $_POST['gstin']);


$subtotal = (float)$_POST['subtotal'];
$discount = (float)$_POST['discount'];
$total    = (float)$_POST['total'];

$gst = 0;

$coupon_code = mysqli_real_escape_string(
  $conn,
  $_POST['coupon_code']
);

$order_no = "ORD" . date("YmdHis") . rand(100, 999);

// Simple currency symbol lookup for the email; falls back to the raw code.
$currency_symbols = [
  'INR' => '&#8377;',
  'USD' => '$',
  'EUR' => '&euro;',
  'GBP' => '&pound;',
];
$currency_symbol = $currency_symbols[strtoupper($currency)] ?? ($currency . ' ');

mysqli_begin_transaction($conn);

try {

  $sql = "INSERT INTO orders
(
order_no,
user_id,
full_name,
email,
phone,
country,
state,
city,
address,
pincode,
company_name,
gstin,
subtotal,
gst,
discount,
total,
coupon_code,
payment_status,
order_status,
currency
)
VALUES
(
'$order_no',
'$user_id',
'$full_name',
'$email',
'$phone',
'$country',
'$state',
'$city',
'$address',
'$pincode',
'$company_name',
'$gstin',
'$subtotal',
'$gst',
'$discount',
'$total',
'$coupon_code',
'Pending',
'Pending',
'$currency'
)";

  mysqli_query($conn, $sql);

  $order_id = mysqli_insert_id($conn);

  // Now also select variant_id from the cart -- this was missing before,
  // which is why every order_items row was always tied to the base product.
  $cart = mysqli_query($conn, "
SELECT
product_id,
variant_id,
size,
qty,
price
FROM cart
WHERE user_id='$user_id'
");
  // Prepared statement to look up the variant's price when a cart row
  // has one. We validate it belongs to the same product, same as
  // add-to-cart.php / cart.php / checkout.php do.
  $variantStmt = $conn->prepare("
SELECT variant_price
FROM product_variants
WHERE id=? AND product_id=?
LIMIT 1
");

  $itemStmt = $conn->prepare("
INSERT INTO order_items
(order_id, product_id, variant_id, size, price, qty)
VALUES
(?, ?, ?, ?, ?, ?)
");

  $items_list = "";
  $row_index  = 0;

  while ($row = mysqli_fetch_assoc($cart)) {

    $product_id = (int) $row['product_id'];
    $variant_id = (int) $row['variant_id'];
    $qty        = (int) $row['qty'];
    $size = mysqli_real_escape_string($conn, $row['size']);

    // Default to the base product's current price.
    $p = mysqli_query($conn, "
SELECT
name,
price,
image
FROM products
WHERE id='$product_id'
");

    $product_row = mysqli_fetch_assoc($p);

    $product_name = $product_row['name'];
    $product_image = $product_row['image'];

    $product_link = "http://localhost/young/e-commerce/product.php?id=" . $product_id;

    $price = (float)$product_row['price'];


    // Agar variant selected hai to variant ka price lo
    if ($variant_id > 0) {

      $variantStmt->bind_param("ii", $variant_id, $product_id);
      $variantStmt->execute();

      $vResult = $variantStmt->get_result();

      if ($vResult->num_rows > 0) {

        $price = (float)$vResult->fetch_assoc()['variant_price'];
      } else {

        $variant_id = 0;
      }
    }


    // Selected currency me convert karo
    // $price = round($price * $currency_rate, 2);
    if ($currency !== 'INR') {
    $price = $price * 1.70;
}

$price = round($price * $currency_rate, 2);

    $itemStmt->bind_param(
      "iiisdi",
      $order_id,
      $product_id,
      $variant_id,
      $size,
      $price,
      $qty
    );
    $itemStmt->execute();

    // Build the email row while we still have the details in hand.
    $row_bg = ($row_index % 2 === 0) ? '#ffffff' : '#f8f9fc';
    $line_total = number_format($price * $qty, 2);

    $items_list .= "
        <tr style='background-color:{$row_bg};'>
           <td style='padding:12px 15px;border-bottom:1px solid #eef0f5;'>

<img
src='http://localhost/young/e-commerce/admin/uploads/{$product_image}'
width='70'
style='display:block;border-radius:8px;margin-bottom:8px;'>

<a
href='{$product_link}'
style='text-decoration:none;color:#1a3d7c;font-weight:bold;'>

{$product_name}

</a>

</td>
             <td style='padding:12px 15px;border-bottom:1px solid #eef0f5;color:#333;font-size:14px;'>{{$size}}</td>
            <td style='padding:12px 15px;border-bottom:1px solid #eef0f5;color:#333;font-size:14px;text-align:center;'>{$qty}</td>
            <td style='padding:12px 15px;border-bottom:1px solid #eef0f5;color:#333;font-size:14px;text-align:right;'>{$currency_symbol}" . number_format($price, 2) . "</td>
            <td style='padding:12px 15px;border-bottom:1px solid #eef0f5;color:#333;font-size:14px;text-align:right;'>{$currency_symbol}{$line_total}</td>
        </tr>";

    $row_index++;
  }

  mysqli_query($conn, "
DELETE FROM cart
WHERE user_id='$user_id'
");

  unset($_SESSION['coupon']);
  unset($_SESSION['discount']);

  $_SESSION['order_id'] = $order_id;
  $_SESSION['order_no'] = $order_no;

  mysqli_commit($conn);

  /* =========================================================
       INSTANT REDIRECT TO PAYMENT
       Redirect the user immediately. The order-confirmation
       email keeps sending on the server after the connection
       is closed. Reliable background execution requires PHP-FPM.
       ========================================================= */

  session_write_close();
  ignore_user_abort(true);
  set_time_limit(0);

  header("Location: payment.php");

  if (function_exists('fastcgi_finish_request')) {
    // Best case: PHP-FPM. Browser redirects to payment.php now,
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
       ORDER CONFIRMATION EMAIL (runs after redirect has been sent)
       ========================================================= */

  $mail = new PHPMailer(true);

  try {

    // $mail->isSMTP();
    // $mail->Host = 'smtp.gmail.com';
    // $mail->SMTPAuth = true;
    // $mail->Username = 'vipanujkumarlmp@gmail.com';
    // $mail->Password = 'xwju bgei lyfm gghg'; // Gmail App Password
    // $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    // $mail->Port = 587;
    // $mail->setFrom('vipanujkumarlmp@gmail.com', 'Your Company');
    
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
                        We've received your order and it's now awaiting payment confirmation. Here's a summary of what you ordered.
                      </p>
                    </td>
                  </tr>

                  <!-- Payment Status Badge -->
                  <tr>
                    <td style='padding:16px 30px 0;'>
                      <div style='background-color:#fff8e6;border:1px solid #ffe8b3;border-radius:8px;padding:16px 18px;text-align:center;'>
                        <p style='margin:0 0 4px;color:#888;font-size:12px;text-transform:uppercase;letter-spacing:0.5px;'>Payment Status</p>
                        <p style='margin:0;color:#8a6d1f;font-size:18px;font-weight:700;'>Order Placed</p>
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
                      <p style='margin:0 0 6px;color:#333;font-size:14px;'>Complete your payment to confirm this order.</p>
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
    // Mail failed silently — order is already saved in DB either way.
    error_log("Order confirmation mail failed for {$order_no}: " . $mail->ErrorInfo);
  }

  exit;
} catch (Exception $e) {

  mysqli_rollback($conn);

  echo $e->getMessage();
}






