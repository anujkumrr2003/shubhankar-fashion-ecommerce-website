<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Order Confirmed | Shubhankar</title>
<meta name="description" content="Your Shubhankar order has been placed successfully.">
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

<section class="section">
  <div class="container text-center" style="max-width:560px;">
    <i class="bi bi-check-circle" style="font-size:3.5rem;color:var(--primary);"></i>
    <h1 class="h3 mt-3">Order placed successfully!</h1>
    <p class="text-muted-soft">Thank you for shopping with Shubhankar. A confirmation has been sent to your email along with tracking details once your order ships.</p>
    <div class="card-panel text-start my-4">
      <div class="d-flex justify-content-between small mb-2"><span>Order Number</span><span class="fw-semibold">NEH-2026-00482</span></div>
      <div class="d-flex justify-content-between small mb-2"><span>Estimated Delivery</span><span class="fw-semibold">3 - 5 business days</span></div>
      <div class="d-flex justify-content-between small"><span>Mode</span><span class="fw-semibold js-mode-badge">-</span></div>
    </div>
    <div class="d-flex gap-2 justify-content-center flex-wrap">
      <a href="shop.php" class="btn btn-brand">Continue Shopping</a>
      <a href="index.php" class="btn btn-outline-brand">Back to Home</a>
    </div>
  </div>
</section>

<!-- ============ FOOTER ============ -->
<?php include 'footer.php'; ?>

</body>
</html>
