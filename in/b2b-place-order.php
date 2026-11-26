<?php
session_start();
include "config.php";
if (!isset($_SESSION['user_id'])) {
  header("Location:login.php");
  exit;
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'phpMailer/Exception.php';
require 'phpMailer/PHPMailer.php';
require 'phpMailer/SMTP.php';

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] != "POST") {
  header("Location:b2b-checkout.php");
  exit;
}

/* ---------------- Admin notification recipient ---------------- */
/* TODO: replace with your real admin/sales inbox */
$admin_email = 'admin@shubhankarfashion.com';
$admin_name  = 'Admin';

/* ---------------- Form Data ---------------- */

$company_name   = mysqli_real_escape_string($conn, $_POST['company_name']);
$gst_number     = mysqli_real_escape_string($conn, $_POST['gst_number']);
$contact_person = mysqli_real_escape_string($conn, $_POST['contact_person']);
$email          = mysqli_real_escape_string($conn, $_POST['email']);
$phone          = mysqli_real_escape_string($conn, $_POST['phone']);
$country        = mysqli_real_escape_string($conn, $_POST['country']);
$state          = mysqli_real_escape_string($conn, $_POST['state']);
$city           = mysqli_real_escape_string($conn, $_POST['city']);
$address        = mysqli_real_escape_string($conn, $_POST['address']);
$business_type  = mysqli_real_escape_string($conn, $_POST['business_type']);
$delivery_date  = mysqli_real_escape_string($conn, $_POST['delivery_date']);
$remarks        = mysqli_real_escape_string($conn, $_POST['remarks']);

$product_ids = $_POST['product_id'];
$variant_ids = $_POST['variant_id'];
$qtys        = $_POST['qty'];

$size_requirements = [];
$product_remarks  = [];
$product_sizes  = [];
$product_colors = [];

$cartData = mysqli_query($conn, "
SELECT
    product_id,
    variant_id,
    size,
    color,
    size_requirement,
    remarks
FROM cart
WHERE user_id='$user_id'
");

while ($c = mysqli_fetch_assoc($cartData)) {
  $key = $c['product_id'] . '_' . $c['variant_id'];

  $product_sizes[$key]  = $c['size'];
$product_colors[$key] = $c['color'];

$size_requirements[$key] = $c['size_requirement'];
$product_remarks[$key]   = $c['remarks'];
}
$total_products = count($product_ids);
$product_list = "";

/* Running totals across all line items */
$total_qty    = 0;
$grand_total  = 0;

$status = "Pending";



$address_id = (int)$_POST['saved_address'];

$addr = mysqli_fetch_assoc(mysqli_query($conn, "
SELECT *
FROM b2b_user_addresses
WHERE id='$address_id'
AND user_id='$user_id'
LIMIT 1
"));

/* Shipping */
$company_name   = $addr['company_name'];
$gst_number     = $addr['gst_number'];
$contact_person = $addr['full_name'];
$phone          = $addr['phone'];
$state          = $addr['state'];
$city           = $addr['city'];
$address        = $addr['address'];
$business_type  = $addr['business_type'];
$delivery_date  = $addr['delivery_date'];
$remarks        = $addr['remarks'];

/* Billing */
$billing_company_name  = $addr['billing_company_name'];
$billing_gst_number    = $addr['billing_gst_number'];
$billing_business_type = $addr['billing_business_type'];
$billing_delivery_date = $addr['billing_delivery_date'];
$billing_remarks       = $addr['billing_remarks'];

$billing_full_name     = $addr['billing_full_name'];
$billing_phone         = $addr['billing_phone'];
$billing_state         = $addr['billing_state'];
$billing_city          = $addr['billing_city'];
$billing_region        = $addr['billing_region'];
$billing_address       = $addr['billing_address'];
$billing_pincode       = $addr['billing_pincode'];

/* Billing counts as "filled" only if the key identifying fields are present */
$has_billing = !empty($billing_full_name) && !empty($billing_address);

/* =========================================================
   EMPTY DATABASE VALUES => NA
========================================================= */

function showNA($value) {
    $value = trim((string)$value);
    return $value === '' ? 'NA' : $value;
}

/* Shipping */
$company_name   = showNA($company_name);
$gst_number     = showNA($gst_number);
$contact_person = showNA($contact_person);
$email          = showNA($email);
$phone          = showNA($phone);
$country        = showNA($country);
$state          = showNA($state);
$city           = showNA($city);
$address        = showNA($address);
$business_type  = showNA($business_type);
$delivery_date  = showNA($delivery_date);
$remarks        = showNA($remarks);

/* Billing */
$billing_company_name  = showNA($billing_company_name);
$billing_gst_number    = showNA($billing_gst_number);
$billing_business_type = showNA($billing_business_type);
$billing_delivery_date = showNA($billing_delivery_date);
$billing_remarks       = showNA($billing_remarks);
$billing_full_name     = showNA($billing_full_name);
$billing_phone         = showNA($billing_phone);
$billing_state          = showNA($billing_state);
$billing_city           = showNA($billing_city);
$billing_region         = showNA($billing_region);
$billing_address        = showNA($billing_address);
$billing_pincode        = showNA($billing_pincode);



for ($i = 0; $i < count($product_ids); $i++) {

  $product_id   = (int)$product_ids[$i];
  $variant_id   = (int)$variant_ids[$i];
  $required_qty = (int)$qtys[$i];

  $key = $product_id . '_' . $variant_id;
  $product_size  = mysqli_real_escape_string(
    $conn,
    $product_sizes[$key] ?? ''
);

$product_color = mysqli_real_escape_string(
    $conn,
    $product_colors[$key] ?? ''
);

  $size_requirement = mysqli_real_escape_string(
    $conn,
    $size_requirements[$key] ?? ''
  );

  $product_remark = mysqli_real_escape_string(
    $conn,
    $product_remarks[$key] ?? ''
  );

 mysqli_query($conn, "
INSERT INTO b2b_inquiries
(
    user_id,
    product_id,
    variant_id,
    company_name,
    gst_number,
    contact_person,
    email,
    phone,
    country,
    state,
    city,
    address,
    business_type,
    size,
color,
    required_qty,
    size_requirement,
    product_remarks,
    delivery_date,
    remarks,
    billing_company_name,
    billing_gst_number,
    billing_business_type,
    billing_delivery_date,
    billing_remarks,
    billing_full_name,
    billing_phone,
    billing_state,
    billing_city,
    billing_region,
    billing_address,
    billing_pincode,
    billing_address_type,
    status
)
VALUES
(
    '$user_id',
    '$product_id',
    '$variant_id',
    '$company_name',
    '$gst_number',
    '$contact_person',
    '$email',
    '$phone',
    '$country',
    '$state',
    '$city',
    '$address',
    '$business_type',

    '$product_size',
    '$product_color',
    '$required_qty',

    '$size_requirement',
    '$product_remark',
    '$delivery_date',
    '$remarks',

    '$billing_company_name',
    '$billing_gst_number',
    '$billing_business_type',
    '$billing_delivery_date',
    '$billing_remarks',

    '$billing_full_name',
    '$billing_phone',
    '$billing_state',
    '$billing_city',
    '$billing_region',
    '$billing_address',
    '$billing_pincode',

    '$billing_address_type',
    '$status'
)
");
  if (mysqli_error($conn)) {
    die(mysqli_error($conn));
  }

//   if ($variant_id > 0) {

//     $product = mysqli_fetch_assoc(mysqli_query($conn, "
//             SELECT
//                 p.name AS product_name,
//                 s.subcategory_name,
//                 pv.variant_price AS price
//             FROM products p
//             LEFT JOIN subcategories s
//                 ON p.subcategory_id = s.id
//             LEFT JOIN product_variants pv
//                 ON pv.id = '$variant_id'
//             WHERE p.id = '$product_id'
//             LIMIT 1
//         "));

//     $price = $product['price'];
//   } else {

//     $product = mysqli_fetch_assoc(mysqli_query($conn, "
//             SELECT
//                 p.name AS product_name,
//                 s.subcategory_name,
//                 p.price
//             FROM products p
//             LEFT JOIN subcategories s
//                 ON p.subcategory_id = s.id
//             WHERE p.id = '$product_id'
//             LIMIT 1
//         "));

//     $price = $product['price'];
//   }

// if ($variant_id > 0) {

//     $product = mysqli_fetch_assoc(mysqli_query($conn, " 
//         SELECT 
//             p.name AS product_name, 
//             s.subcategory_name, 
//             pv.variant_price AS price 
//         FROM products p 
//         LEFT JOIN subcategories s 
//             ON p.subcategory_id = s.id 
//         LEFT JOIN product_variants pv 
//             ON pv.id = '$variant_id' 
//         WHERE p.id = '$product_id' 
//         LIMIT 1 
//     "));

//     $price = (float)$product['price'];

// } else {

//     $product = mysqli_fetch_assoc(mysqli_query($conn, " 
//         SELECT 
//             p.name AS product_name, 
//             s.subcategory_name, 
//             p.price 
//         FROM products p 
//         LEFT JOIN subcategories s 
//             ON p.subcategory_id = s.id 
//         WHERE p.id = '$product_id' 
//         LIMIT 1 
//     "));

//     $price = (float)$product['price'];
// }

// /*
// |--------------------------------------------------------------------------
// | 20% B2B Markup
// |--------------------------------------------------------------------------
// | Database me original price hi rahegi.
// | Order/email calculation me 20% extra add hoga.
// |
// | ₹100  -> ₹120
// | ₹200  -> ₹240
// | ₹500  -> ₹600
// |--------------------------------------------------------------------------
// */
// $price = $price * 1.20;

//   $product_category = $product['subcategory_name'];
//   $product_name     = $product['product_name'];

//   /* Accumulate totals */
//   $line_total    = $price * $required_qty;
//   $total_qty    += $required_qty;
//   $grand_total  += $line_total;


/* =========================================================
   GET ORIGINAL PRODUCT PRICE
========================================================= */

if ($variant_id > 0) {

    $product = mysqli_fetch_assoc(mysqli_query($conn, "
        SELECT
            p.name AS product_name,
            s.subcategory_name,
            pv.variant_price AS price
        FROM products p
        LEFT JOIN subcategories s
            ON p.subcategory_id = s.id
        INNER JOIN product_variants pv
            ON pv.id = '$variant_id'
        WHERE p.id = '$product_id'
        LIMIT 1
    "));

} else {

    $product = mysqli_fetch_assoc(mysqli_query($conn, "
        SELECT
            p.name AS product_name,
            s.subcategory_name,
            p.price AS price
        FROM products p
        LEFT JOIN subcategories s
            ON p.subcategory_id = s.id
        WHERE p.id = '$product_id'
        LIMIT 1
    "));
}


/* =========================================================
   CHECK PRODUCT
========================================================= */

if (!$product) {
    die("Product not found. Product ID: " . $product_id);
}


/* =========================================================
   ORIGINAL DATABASE PRICE
========================================================= */

$original_price = (float)$product['price'];


/* =========================================================
   FINAL B2B PRICE = ORIGINAL + 20%
   
   ₹100  -> ₹120
   ₹200  -> ₹240
   ₹500  -> ₹600
========================================================= */

$price = round($original_price * 1.20, 2);


/* =========================================================
   PRODUCT DETAILS
========================================================= */

$product_category = $product['subcategory_name'] ?? '';
$product_name     = $product['product_name'] ?? '';


/* =========================================================
   LINE TOTAL
========================================================= */

$line_total = round($price * $required_qty, 2);

$total_qty += $required_qty;

$grand_total = round($grand_total + $line_total, 2);



/* =========================================================
   EMAIL PRODUCT ROW - ONLY ONCE
========================================================= */

$product_category = showNA($product_category);
$product_name     = showNA($product_name);
$product_size     = showNA($product_size);
$product_color    = showNA($product_color);

$row_bg = ($i % 2 === 0) ? '#ffffff' : '#f8f9fc';

$product_list .= "
<tr style='background-color:{$row_bg};'>

<td style='padding:12px 15px;border-bottom:1px solid #eef0f5;'>
    {$product_category}
</td>

<td style='padding:12px 15px;border-bottom:1px solid #eef0f5;'>
    <strong>{$product_name}</strong><br>

    <small>
        <b>Size:</b> {$product_size}<br>
        <b>Color:</b> {$product_color}
    </small>
</td>

<td style='padding:12px 15px;border-bottom:1px solid #eef0f5;text-align:center;'>
    {$required_qty}
</td>

<td style='padding:12px 15px;border-bottom:1px solid #eef0f5;text-align:right;'>
    &#8377;" . number_format($price, 2) . "
</td>

<td style='padding:12px 15px;border-bottom:1px solid #eef0f5;text-align:right;'>
    &#8377;" . number_format($line_total, 2) . "
</td>

</tr>";


  // Zebra-striped rows for readability
//   $row_bg = ($i % 2 === 0) ? '#ffffff' : '#f8f9fc';

//  $product_list .= "
// <tr style='background-color:{$row_bg};'>

// <td style='padding:12px 15px;border-bottom:1px solid #eef0f5;'>
// {$product_category}
// </td>

// <td style='padding:12px 15px;border-bottom:1px solid #eef0f5;'>

// <strong>{$product_name}</strong><br>

// <small>
// <b>Size:</b> {$product_size}<br>
// <b>Color:</b> {$product_color}
// </small>

// </td>

// <td style='padding:12px 15px;border-bottom:1px solid #eef0f5;text-align:center;'>
// {$required_qty}
// </td>

// <td style='padding:12px 15px;border-bottom:1px solid #eef0f5;text-align:right;'>
// &#8377;{$price}
// </td>

// <td style='padding:12px 15px;border-bottom:1px solid #eef0f5;text-align:right;'>
// &#8377;" . number_format($line_total,2) . "
// </td>

// </tr>";
}

$grand_total_formatted = number_format($grand_total, 2);

/* ---------------- Billing block (only rendered if billing address exists) ---------------- */

$billing_html = "";

if ($has_billing) {

  $billing_html = "

<tr>
<td colspan='2' style='padding:25px;'>

<h3 style='color:#1a3d7c;'>Billing Address</h3>

<table width='100%' cellpadding='8' cellspacing='0' style='border:1px solid #ddd;border-collapse:collapse;'>

<tr>
<td><strong>Company</strong></td>
<td>{$billing_company_name}</td>
</tr>

<tr>
<td><strong>GST</strong></td>
<td>{$billing_gst_number}</td>
</tr>

<tr>
<td><strong>Business Type</strong></td>
<td>{$billing_business_type}</td>
</tr>

<tr>
<td><strong>Contact Person</strong></td>
<td>{$billing_full_name}</td>
</tr>

<tr>
<td><strong>Phone</strong></td>
<td>{$billing_phone}</td>
</tr>

<tr>
<td><strong>Address</strong></td>
<td>{$billing_address}</td>
</tr>

<tr>
<td><strong>City</strong></td>
<td>{$billing_city}</td>
</tr>

<tr>
<td><strong>State</strong></td>
<td>{$billing_state}</td>
</tr>

<tr>
<td><strong>Region</strong></td>
<td>{$billing_region}</td>
</tr>

<tr>
<td><strong>Pincode</strong></td>
<td>{$billing_pincode}</td>
</tr>

<tr>
<td><strong>Delivery Date</strong></td>
<td>{$billing_delivery_date}</td>
</tr>

<tr>
<td><strong>Remarks</strong></td>
<td>{$billing_remarks}</td>
</tr>

<tr>
<td><strong>Amount Billed</strong></td>
<td>&#8377;{$grand_total_formatted}</td>
</tr>

</table>

</td>
</tr>

";
}

/* ---------------- Clear Cart ---------------- */

mysqli_query($conn, "DELETE FROM cart WHERE user_id='$user_id'");

/* =========================================================
   INSTANT REDIRECT
   Redirect the user immediately. Everything after this block
   (sending the email) keeps running on the server even though
   the browser has already moved to b2b-success.php — but this
   only works reliably under PHP-FPM (fastcgi_finish_request).
   ========================================================= */

session_write_close();
ignore_user_abort(true);
set_time_limit(0);

header("Location:b2b-success");

// Flush everything to the browser now, before doing the slow email work
if (function_exists('fastcgi_finish_request')) {
  // Best case: PHP-FPM. Browser gets the redirect right now,
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
   CUSTOMER EMAIL (runs after the redirect has been sent)
   ========================================================= */

$mail = new PHPMailer(true);
try {
//   $mail->isSMTP();
//   $mail->Host = 'smtp.gmail.com';
//   $mail->SMTPAuth = true;
//   $mail->Username = 'vipanujkumarlmp@gmail.com';
//   $mail->Password = 'xwju bgei lyfm gghg';
//   $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
//   $mail->Port = 587;
//   $mail->setFrom('vipanujkumarlmp@gmail.com', 'Your Company');

$mail->isSMTP();
$mail->Host = 'smtp.hostinger.com';
$mail->SMTPAuth = true;
$mail->Username = 'info@shubhankarfashion.com';
$mail->Password = 'Sunchit@11';
$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
$mail->Port = 587;

$mail->setFrom('info@shubhankarfashion.com', 'Shubhankar Fashion');

  $mail->addAddress($email, $contact_person);

  $mail->isHTML(true);
  $mail->Subject = "Your B2B Order has been Placed";

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
                  <p style='margin:6px 0 0;color:#d6e0f5;font-size:13px;'>B2B Inquiry Confirmation</p>
                </td>
              </tr>

              <!-- Greeting -->
              <tr>
                <td style='padding:28px 30px 10px;'>
                  <h2 style='margin:0 0 8px;color:#1a3d7c;font-size:18px;'>Thank you, {$contact_person}!</h2>
                  <p style='margin:0;color:#555;font-size:14px;line-height:1.6;'>
                    Your B2B inquiry has been submitted successfully. Our team will review your request and get back to you shortly.
                  </p>
                </td>
              </tr>

              <!-- Order / Company Details -->
              <tr>
                <td style='padding:20px 30px 0;'>
                  <table width='100%' cellpadding='0' cellspacing='0' style='background-color:#f8f9fc;border-radius:8px;border:1px solid #eef0f5;'>
                    <tr>
                      <td style='padding:10px 18px;color:#888;font-size:13px;width:40%;'>Company</td>
                      <td style='padding:10px 18px;color:#222;font-size:13px;font-weight:600;'>{$company_name}</td>
                    </tr>
                    <tr>
                      <td style='padding:10px 18px;color:#888;font-size:13px;background-color:#ffffff;'>GST Number</td>
                      <td style='padding:10px 18px;color:#222;font-size:13px;font-weight:600;background-color:#ffffff;'>{$gst_number}</td>
                    </tr>
                    <tr>
                      <td style='padding:10px 18px;color:#888;font-size:13px;'>Contact Person</td>
                      <td style='padding:10px 18px;color:#222;font-size:13px;font-weight:600;'>{$contact_person}</td>
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
                      <td style='padding:10px 18px;color:#888;font-size:13px;background-color:#ffffff;'>Business Type</td>
                      <td style='padding:10px 18px;color:#222;font-size:13px;font-weight:600;background-color:#ffffff;'>{$business_type}</td>
                    </tr>
                    <tr>
                      <td style='padding:10px 18px;color:#888;font-size:13px;'>Delivery Date</td>
                      <td style='padding:10px 18px;color:#222;font-size:13px;font-weight:600;'>{$delivery_date}</td>
                    </tr>
                    <tr>
                      <td style='padding:10px 18px;color:#888;font-size:13px;background-color:#ffffff;'>Total Products</td>
                      <td style='padding:10px 18px;color:#222;font-size:13px;font-weight:600;background-color:#ffffff;'>{$total_products}</td>
                    </tr>
                    <tr>
                      <td style='padding:10px 18px;color:#888;font-size:13px;'>Total Quantity</td>
                      <td style='padding:10px 18px;color:#222;font-size:13px;font-weight:600;'>{$total_qty}</td>
                    </tr>
                    <tr>
                      <td style='padding:10px 18px;color:#888;font-size:13px;background-color:#ffffff;border-radius:0 0 0 8px;'>Total Amount</td>
                      <td style='padding:10px 18px;color:#1f8a4c;font-size:14px;font-weight:700;background-color:#ffffff;border-radius:0 0 8px 0;'>&#8377;{$grand_total_formatted}</td>
                    </tr>
                    {$billing_html}
                  </table>
                </td>
              </tr>

              <!-- Product Table -->
              <tr>
                <td style='padding:26px 30px 0;'>
                  <h3 style='margin:0 0 12px;color:#1a3d7c;font-size:16px;'>Product Details</h3>
                  <table width='100%' cellpadding='0' cellspacing='0' style='border:1px solid #eef0f5;border-radius:8px;overflow:hidden;'>
                    <tr style='background-color:#1a3d7c;'>
                      <th style='padding:12px 15px;color:#ffffff;font-size:13px;text-align:left;'>Category</th>
                      <th style='padding:12px 15px;color:#ffffff;font-size:13px;text-align:left;'>Product</th>
                      <th style='padding:12px 15px;color:#ffffff;font-size:13px;text-align:center;'>Qty</th>
                      <th style='padding:12px 15px;color:#ffffff;font-size:13px;text-align:right;'>Price</th>
                      <th style='padding:12px 15px;color:#ffffff;font-size:13px;text-align:right;'>Line Total</th>
                    </tr>
                    {$product_list}
                    <tr style='background-color:#f0f4ff;'>
                      <td colspan='2' style='padding:12px 15px;color:#1a3d7c;font-size:13px;font-weight:700;'>Total</td>
                      <td style='padding:12px 15px;color:#1a3d7c;font-size:13px;font-weight:700;text-align:center;'>{$total_qty}</td>
                      <td style='padding:12px 15px;'></td>
                      <td style='padding:12px 15px;color:#1a3d7c;font-size:14px;font-weight:700;text-align:right;'>&#8377;{$grand_total_formatted}</td>
                    </tr>
                  </table>
                </td>
              </tr>

              <!-- Remarks -->
              <tr>
                <td style='padding:26px 30px 0;'>
                  <div style='background-color:#fff8e6;border:1px solid #ffe8b3;border-radius:8px;padding:14px 18px;'>
                    <p style='margin:0 0 4px;color:#8a6d1f;font-size:13px;font-weight:600;'>Remarks</p>
                    <p style='margin:0;color:#6b5a2e;font-size:13px;line-height:1.5;'>{$remarks}</p>
                  </div>
                </td>
              </tr>

              <!-- Footer -->
              <tr>
                <td style='padding:30px;text-align:center;'>
                  <p style='margin:0 0 6px;color:#333;font-size:14px;'>Thank you for choosing us!</p>
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
  // Mail failed silently — inquiry is already saved in DB either way.
  // Consider logging $mail->ErrorInfo to a file for debugging.
  error_log("B2B inquiry mail failed: " . $mail->ErrorInfo);
}

/* =========================================================
   ADMIN NOTIFICATION EMAIL
   Short internal alert so the sales team doesn't have to open
   the full customer email to see the key facts of the order.
   ========================================================= */

$mailAdmin = new PHPMailer(true);
try {
  $mailAdmin->isSMTP();
  $mailAdmin->Host = 'smtp.gmail.com';
  $mailAdmin->SMTPAuth = true;
  $mailAdmin->Username = 'vipanujkumarlmp@gmail.com';
  $mailAdmin->Password = 'xwju bgei lyfm gghg';
  $mailAdmin->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
  $mailAdmin->Port = 587;

  $mailAdmin->setFrom('vipanujkumarlmp@gmail.com', 'Your Company');
  $mailAdmin->addAddress($admin_email, $admin_name);
  // So the admin can hit "Reply" and land straight in the customer's inbox
  $mailAdmin->addReplyTo($email, $contact_person);

  $mailAdmin->isHTML(true);
  $mailAdmin->Subject = "New B2B Inquiry {$contact_person} ({$total_products} products)";

  $mailAdmin->Body = "
    <div style='background-color:#f0f2f5;padding:30px 0;font-family:Segoe UI, Roboto, Helvetica, Arial, sans-serif;'>
      <table role='presentation' width='100%' cellpadding='0' cellspacing='0'>
        <tr>
          <td align='center'>
            <table role='presentation' width='560' cellpadding='0' cellspacing='0' style='background-color:#ffffff;border-radius:10px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.06);'>

              <tr>
                <td style='background:linear-gradient(135deg,#1a3d7c,#2e5aa8);padding:24px 28px;text-align:center;'>
                  <h1 style='margin:0;color:#ffffff;font-size:20px;'>New B2B Inquiry Received</h1>
                </td>
              </tr>

              <tr>
                <td style='padding:24px 28px 8px;'>
                  <table width='100%' cellpadding='0' cellspacing='0' style='background-color:#f8f9fc;border-radius:8px;border:1px solid #eef0f5;'>
                    <tr>
                      <td style='padding:10px 18px;color:#888;font-size:13px;width:45%;'>Customer Name</td>
                      <td style='padding:10px 18px;color:#222;font-size:13px;font-weight:600;'>{$contact_person}</td>
                    </tr>
                    <tr>
                      <td style='padding:10px 18px;color:#888;font-size:13px;background-color:#ffffff;'>Email</td>
                      <td style='padding:10px 18px;color:#222;font-size:13px;font-weight:600;background-color:#ffffff;'>{$email}</td>
                    </tr>
                    <tr>
                      <td style='padding:10px 18px;color:#888;font-size:13px;'>Contact No.</td>
                      <td style='padding:10px 18px;color:#222;font-size:13px;font-weight:600;'>{$phone}</td>
                    </tr>
                    <tr>
                      <td style='padding:10px 18px;color:#888;font-size:13px;background-color:#ffffff;'>Total Quantity</td>
                      <td style='padding:10px 18px;color:#222;font-size:13px;font-weight:600;background-color:#ffffff;'>{$total_qty}</td>
                    </tr>
                    <tr>
                      <td style='padding:10px 18px;color:#888;font-size:13px;border-radius:0 0 0 8px;'>Total Amount</td>
                      <td style='padding:10px 18px;color:#1f8a4c;font-size:14px;font-weight:700;border-radius:0 0 8px 0;'>&#8377;{$grand_total_formatted}</td>
                    </tr>
                  </table>
                </td>
              </tr>

              <tr>
                <td style='padding:20px 28px 28px;text-align:center;'>
                 <table width='100%' cellpadding='0' cellspacing='0' style='border:1px solid #ddd;border-collapse:collapse;'>

<tr style='background:#1a3d7c;color:#fff;'>

<th style='padding:10px;'>Product</th>
<th style='padding:10px;'>Size</th>
<th style='padding:10px;'>Color</th>
<th style='padding:10px;'>Qty</th>

</tr>

{$product_list}

</table>

<p style='margin-top:20px;color:#666;font-size:13px;'>
Please login to the admin panel for complete inquiry details.
</p>
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
  error_log("B2B admin notification mail failed: " . $mailAdmin->ErrorInfo);
}

exit;
