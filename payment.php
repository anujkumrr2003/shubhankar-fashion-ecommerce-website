<?php
session_start();
include "config.php";

if (!isset($_SESSION['order_id'])) {
  header("Location: checkout");
  exit;
}

$order_id = (int)$_SESSION['order_id'];

$sql = mysqli_query($conn, "
SELECT *
FROM orders
WHERE id='$order_id'
LIMIT 1
");


if (mysqli_num_rows($sql) == 0) {
  die("Order not found.");
}



$order = mysqli_fetch_assoc($sql);

$subtotal = $order['subtotal'];
$discount = $order['discount'];
$total    = $order['total'];
$coupon   = $order['coupon_code'];
$currency = $order['currency'];
$order_no = $order['order_no'];



?>


<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
 <!-- Primary Meta Tags -->
<title>Payment | Royal Blue</title>
<meta name="title" content="Payment | Royal Blue">
<meta name="description" content="Complete your payment securely at Royal Blue. Enjoy a safe and easy online payment experience with trusted checkout options.">
<meta name="keywords" content="Royal Blue payment, secure online payment, online shopping payment, checkout payment, Royal Blue store">
<meta name="author" content="Royal Blue">

<!-- Canonical URL -->
<link rel="canonical" href="https://shubhankarfashion.com/payment">

<!-- Open Graph Meta Tags -->
<meta property="og:url" content="https://shubhankarfashion.com/payment">
<meta property="og:type" content="website">
<meta property="og:title" content="Payment | Royal Blue">
<meta property="og:description" content="Securely complete your payment and place your order with Royal Blue.">
<meta property="og:image" content="https://shubhankarfashion.com/img/logo-removebg-preview.png">
<meta property="og:site_name" content="Royal Blue">

<!-- Twitter Card -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="Payment | Royal Blue">
<meta name="twitter:description" content="Secure payment experience for Royal Blue customers.">
<meta name="twitter:image" content="https://shubhankarfashion.com/img/logo-removebg-preview.png">

<!-- Robots -->
<meta name="robots" content="index, follow">

<!-- Favicon -->
<link rel="icon" type="image/png" href="https://shubhankarfashion.com/img/logo-removebg-preview.png">

<!-- Schema Markup -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "WebPage",
  "name": "Payment | Royal Blue",
  "url": "https://shubhankarfashion.com/payment",
  "image": "https://shubhankarfashion.com/img/logo-removebg-preview.png",
  "publisher": {
    "@type": "Organization",
    "name": "Royal Blue",
    "logo": {
      "@type": "ImageObject",
      "url": "https://shubhankarfashion.com/img/logo-removebg-preview.png"
    }
  }
}
</script>
  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <!-- Site styles -->
  <link rel="stylesheet" href="css/style.css">

</head>

<body>



  <?php include 'header.php'; ?>

  <section class="section-sm bg-surface-2 border-bottom">
    <div class="container">
      <nav class="small text-muted-soft mb-3"><a href="index">Home</a> / <span>Payment</span></nav>
      <div class="step-track">
        <div class="step"><span class="num">1</span> Shipping</div>
        <div class="step-sep"></div>
        <div class="step active"><span class="num">2</span> Payment</div>
        <div class="step-sep"></div>
        <div class="step"><span class="num">3</span> Confirmation</div>
      </div>
    </div>
  </section>
  <section class="section">
    <div class="container">
      <div class="row g-4">
        <div class="col-lg-7">
          <div class="card-panel">
            <h2 class="h6 mb-3">Choose Payment Method</h2>

            <!-- Payment Methods -->
            <div class="d-flex flex-column gap-2 mb-4">
              <label class="border rounded p-3 d-flex align-items-center gap-2" style="border-color:var(--border) !important;">
                <input type="radio" name="payMethod" value="card" checked>
                <i class="bi bi-credit-card"></i> Credit / Debit Card
              </label>

             
              <label class="border rounded p-3 d-flex align-items-center gap-2" style="border-color:var(--border) !important;">
                <input type="radio" name="payMethod" value="upi">
                <i class="bi bi-phone"></i> UPI (Google Pay, PhonePe, Paytm, etc.)
              </label>

             
              <label class="border rounded p-3 d-flex align-items-center gap-2" style="border-color:var(--border) !important;">
                <input type="radio" name="payMethod" value="netbanking">
                <i class="bi bi-bank"></i> Net Banking / NEFT
              </label>

              <!-- COD -->
              <label class="border rounded p-3 d-flex align-items-center gap-2" style="border-color:var(--border) !important;">
                <input type="radio" name="payMethod" value="cod">
                <i class="bi bi-cash-coin"></i> Cash on Delivery (COD)
              </label>
            </div>

            <!-- Hidden fields -->
            <input type="hidden" id="order_id" value="<?= $order_id; ?>">
            <input type="hidden" id="order_no" value="<?= $order_no; ?>">
            <input type="hidden" id="amount" value="<?= $total; ?>">
            <input type="hidden" id="customer_name" value="<?= htmlspecialchars($order['full_name']); ?>">
            <input type="hidden" id="customer_email" value="<?= htmlspecialchars($order['email']); ?>">
            <input type="hidden" id="customer_phone" value="<?= htmlspecialchars($order['phone']); ?>">

            <button type="button" class="btn btn-brand w-100 mt-4" id="payBtn">
              Pay Now
            </button>
          </div>
        </div>
        <div class="col-lg-5">

          <div class="card-panel">

            <h2 class="h6 mb-3">
              Amount Payable
            </h2>

            <div class="d-flex justify-content-between mb-2">

              <span>Order No.</span>

              <strong><?= $order_no; ?></strong>

            </div>

            <div class="d-flex justify-content-between mb-2">

              <span>Subtotal</span>

              <!--<span-->
              <!--  id="paySubtotal"-->
              <!--  class="js-price"-->
              <!--  data-currency="<?= $currency ?>"-->
              <!--  data-price-inr="<?= $subtotal ?>">-->
              <!--  <?= number_format($subtotal, 2) ?>-->
              <!--</span>-->
              
              <span id="paySubtotal">
    <?= htmlspecialchars($currency) ?> <?= number_format((float)$subtotal, 2) ?>
</span>

            </div>

            <?php if ($discount > 0) { ?>

              <div class="d-flex justify-content-between mb-2 text-success">

                <span>

                  Discount
                  (<?= $coupon; ?>)

                </span>

                <!--<span-->
                <!--  id="payDiscount"-->
                <!--  class="js-price"-->
                <!--  data-currency="<?= $currency ?>"-->
                <!--  data-price-inr="<?= $discount ?>">-->
                <!--  <?= number_format($discount, 2) ?>-->
                <!--</span>-->
                
                <span id="payDiscount">
    <?= htmlspecialchars($currency) ?> <?= number_format((float)$discount, 2) ?>
</span>

              </div>

            <?php } ?>

            <hr>

            <div class="d-flex justify-content-between fw-bold fs-5">

              <span>Total</span>

              <!--<span-->
              <!--  id="payTotal"-->
              <!--  class="js-price"-->
              <!--  data-currency="<?= $currency ?>"-->
              <!--  data-price-inr="<?= $total ?>">-->
              <!--  <?= number_format($total, 2) ?>-->
              <!--</span>-->
              
              <span id="payTotal">
    <?= htmlspecialchars($currency) ?> <?= number_format((float)$total, 2) ?>
</span>

            </div>

          </div>
        </div>
      </div>
    </div>
  </section>






  <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

  <script>
    $("#payBtn").click(function() {
      const payMethod = $('input[name="payMethod"]:checked').val();
      const orderId = $("#order_id").val();

      $("#payBtn").prop("disabled", true).text("Please Wait...");

      if (payMethod === "cod") {
        // COD - Direct success
        $.post("process-cod.php", {
          order_id: orderId
        }, function(res) {
          if (res.status) {
            window.location.href = "order-success";
          } else {
            alert(res.message || "Failed to place COD order");
            $("#payBtn").prop("disabled", false).text("Pay Now");
          }
        }, "json").fail(function() {
          alert("Server error. Please try again.");
          $("#payBtn").prop("disabled", false).text("Pay Now");
        });
        return;
      }

      // For Card, UPI, Netbanking → Use Razorpay
      $.ajax({
        url: "create-order.php",
        type: "POST",
        data: {
          payment_method: payMethod
        },
        dataType: "json",
        success: function(res) {
          if (!res.status) {
            alert(res.message);
            $("#payBtn").prop("disabled", false).text("Pay Now");
            return;
          }



          var options = {
            key: res.key,
            amount: res.amount,
            currency: res.currency,
            name: "Shubhankar",
            description: "Order #" + res.order_no,
            image: "images/logo.png",
            order_id: res.razorpay_order_id,
            prefill: {
              name: res.name,
              email: res.email,
              contact: res.contact
            },
            notes: {
              order_id: res.order_id,
              payment_method: payMethod
            },
            theme: {
              color: "#000"
            },
            handler: function(response) {

              console.log("Razorpay Response");
              console.log(response);

              $.ajax({
                url: "verify-payment.php",
                type: "POST",
                dataType: "json",
                data: {
                  razorpay_payment_id: response.razorpay_payment_id,
                  razorpay_order_id: response.razorpay_order_id,
                  razorpay_signature: response.razorpay_signature
                },
                success: function(verifyRes) {

                  console.log("Verify Response");
                  console.log(verifyRes);

                  if (verifyRes.status) {
                    window.location.href = "order-success";
                  } else {
                    alert(verifyRes.message);
                  }

                },
                error: function(xhr) {

                  console.log(xhr.responseText);

                  alert("Verification Error");

                }

              });

            },
            modal: {
              ondismiss: function() {
                $("#payBtn").prop("disabled", false).text("Pay Now");
              }
            }
          };

          var rzp = new Razorpay(options);
          rzp.on('payment.failed', function(response) {
            alert(response.error.description);
            $("#payBtn").prop("disabled", false).text("Pay Now");
          });
          rzp.open();
        },
        error: function() {
          alert("Unable to create payment order.");
          $("#payBtn").prop("disabled", false).text("Pay Now");
        }
      });
    });
  </script>




  <!-- ============ FOOTER ============ -->
  <?php include 'footer.php'; ?>


  <!-- <script>
    document.addEventListener("DOMContentLoaded", function() {

      if (typeof NEH !== "undefined") {

        NEH.updatePriceEls();

      }

    });

    document.addEventListener("neh:localechange", function() {

      if (typeof NEH !== "undefined") {

        NEH.updatePriceEls();

      }

    });
  </script> -->

</body>

</html>