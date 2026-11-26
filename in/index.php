<?php include 'config.php'; ?>


<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Shubhankar India | Buy Premium Men's Fashion Online in India</title>


<meta name="title" content="Shubhankar India | Buy Premium Men's Fashion Online in India">

<meta name="description" content="Shop premium men's fashion online in India at Shubhankar. Explore jeans, shirts, t-shirts, blazers, trousers and more with secure payments and PAN India delivery.">

<meta name="keywords" content="Men's Fashion India, Buy Men's Clothing Online India, Men's Jeans India, Men's Shirts India, Men's T Shirts India, Men's Blazers India, Men's Trousers India, Online Clothing Store India, Shubhankar India">

<meta name="robots" content="index,follow,max-image-preview:large,max-snippet:-1,max-video-preview:-1">

<meta name="author" content="Shubhankar">
<meta name="publisher" content="Shubhankar">

<link rel="canonical" href="https://shubhankarfashion.com/in/">

<!-- Hreflang -->
<link rel="alternate" hreflang="en-IN" href="https://shubhankarfashion.com/in/">
<link rel="alternate" hreflang="x-default" href="https://shubhankarfashion.com/in/">

<!-- Open Graph -->
<meta property="og:type" content="website">
<meta property="og:site_name" content="Shubhankar India">
<meta property="og:title" content="Shubhankar India | Buy Premium Men's Fashion Online">
<meta property="og:description" content="Premium men's clothing for India. Shop jeans, shirts, t-shirts, blazers and more with PAN India delivery.">
<meta property="og:url" content="https://shubhankarfashion.com/in/">
<meta property="og:image" content="https://shubhankarfashion.com/img/logo-removebg-preview.png">
<meta property="og:image:alt" content="Shubhankar India">

<!-- Twitter -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="Shubhankar India">
<meta name="twitter:description" content="Buy premium men's fashion online in India.">
<meta name="twitter:image" content="https://shubhankarfashion.com/img/logo-removebg-preview.png">

<!-- Favicon -->
<link rel="icon" type="image/png" href="https://shubhankarfashion.com/img/logo-removebg-preview.png">
<link rel="apple-touch-icon" href="https://shubhankarfashion.com/img/logo-removebg-preview.png">

<meta name="theme-color" content="#000000">
<meta name="geo.region" content="IN">
<meta name="geo.country" content="India">
<meta name="ICBM" content="20.5937,78.9629">
<script type="application/ld+json">
{
  "@context":"https://schema.org",
  "@type":"OnlineStore",
  "name":"Shubhankar India",
  "url":"https://shubhankarfashion.com/in/",
  "logo":"https://shubhankarfashion.com/img/logo-removebg-preview.png",
  "image":"https://shubhankarfashion.com/img/logo-removebg-preview.png",
  "description":"Premium men's fashion online store in India.",
  "areaServed":{
    "@type":"Country",
    "name":"India"
  },
  "address":{
    "@type":"PostalAddress",
    "addressCountry":"IN"
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
    <link rel="stylesheet" href="../css/style.css">


    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flag-icons/css/flag-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

</head>

<body>





    <?php include 'header.php'; ?>




    <!-- ================= HERO SLIDER ================= -->

    <!-- ================= HERO SLIDER ================= -->
    <?php
    include 'config.php';

    // Desktop Images
    $desktop = [];
    $res = $conn->query("SELECT home_image FROM logo1 ORDER BY id ASC");
    while ($row = $res->fetch_assoc()) {
        $desktop[] = $row['home_image'];
    }

    // Mobile Images
    $mobile = [];
    $res = $conn->query("SELECT home_image FROM logo ORDER BY id ASC");
    while ($row = $res->fetch_assoc()) {
        $mobile[] = $row['home_image'];
    }

    $total = max(count($desktop), count($mobile));
    ?>

    <section class="hero-slider">
        <div id="heroSlider" class="carousel slide carousel-fade" data-bs-ride="carousel">

            <!-- Indicators -->
            <div class="carousel-indicators">
                <?php for ($i = 0; $i < $total; $i++) { ?>
                    <button type="button"
                        data-bs-target="#heroSlider"
                        data-bs-slide-to="<?php echo $i; ?>"
                        class="<?php echo ($i == 0) ? 'active' : ''; ?>"
                        <?php echo ($i == 0) ? 'aria-current="true"' : ''; ?>>
                    </button>
                <?php } ?>
            </div>

            <!-- Slides -->
            <div class="carousel-inner">

                <?php for ($i = 0; $i < $total; $i++) { ?>

                    <div class="carousel-item <?php echo ($i == 0) ? 'active' : ''; ?>">

                        <?php if (isset($desktop[$i])) { ?>
                            <img src="../admin/<?php echo $desktop[$i]; ?>"
                                class="d-none d-md-block w-100 desktop-banner"
                                alt="Desktop Banner">
                        <?php } ?>

                        <?php if (isset($mobile[$i])) { ?>
                            <img src="../admin/<?php echo $mobile[$i]; ?>"
                                class="d-block d-md-none w-100 mobile-banner"
                                alt="Mobile Banner">
                        <?php } ?>

                    </div>

                <?php } ?>

            </div>

            <!-- Controls -->
            <button class="carousel-control-prev" type="button" data-bs-target="#heroSlider" data-bs-slide="prev">
                <span class="carousel-control-prev-icon"></span>
            </button>

            <button class="carousel-control-next" type="button" data-bs-target="#heroSlider" data-bs-slide="next">
                <span class="carousel-control-next-icon"></span>
            </button>

        </div>
    </section>

    <?php $conn->close(); ?>



    <style>
        /* Desktop Banner */
        .desktop-banner {
            height: 600px;
            object-fit: cover;
        }

        /* Mobile Banner */
        .mobile-banner {
            height: 420px;
            object-fit: cover;
        }

        .hero-slider .carousel-item {
            position: relative;
        }

        .hero-slider .carousel-item::before {
            content: "";
            position: absolute;
            inset: 0;
            background: rgba(0, 0, 0, .1);
            z-index: 1;
        }

        .hero-slider .carousel-caption {
            z-index: 2;
        }

        /* Tablet */
        @media (max-width:991px) {
            .desktop-banner {
                height: 500px;
            }
        }

        /* Mobile */
        @media (max-width:767px) {
            .mobile-banner {
                height: auto;
                width: 100%;
            }
        }
    </style>
    <!-- ============ CATEGORY STRIP ============ -->

    <?php include 'cat.php'; ?>


    <?php
    include 'config.php';
    $promoCodes = [];

    $result = $conn->query("
    SELECT code, discount_type, discount_value, minimum_amount
    FROM promo_codes
    WHERE status = 1
    ORDER BY id DESC
");


    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $promoCodes[] = $row;
        }
    }
    ?>

    <?php if (count($promoCodes) > 0): ?>
        <!-- <h3 class="text-center mb-4" style="color:#bd4871; font-weight:700;">
            <i class="fas fa-gift me-2"></i> Use These Promo Codes
        </h3> -->
    <?php endif; ?>
    <!-- <div class="promo-strip">
        <div class="container">
            <div class="row g-4">

                <?php foreach ($promoCodes as $row): ?>
                    <?php
                    $symbol = ($row['discount_type'] === 'percent') ? '%' : '₹';
                    $couponCode = htmlspecialchars($row['code']);
                    ?>

                    <div class="col-lg-4 col-md-6 col-12">
                        <div class="promo-chip">

                            <span class="promo-chip-text">
                                🎉 Apply <strong><?= $couponCode ?></strong><br>

                                Get
                                <strong class="coupon-discount"
                                    data-price-inr="<?= $row['discount_value'] ?>">
                                    <?= $row['discount_type'] == 'percent'
                                        ? $row['discount_value'] . '%'
                                        : '₹' . number_format($row['discount_value']) ?>
                                </strong>

                                OFF on orders above

                                <strong class="coupon-min"
                                    data-price-inr="<?= $row['minimum_amount'] ?>">
                                    ₹<?= number_format($row['minimum_amount']) ?>
                                </strong>
                            </span>

                            <button class="copy-btn"
                                data-code="<?= $couponCode ?>"
                                title="Copy Coupon">
                                <i class="fa-solid fa-copy"></i>
                            </button>

                        </div>
                    </div>

                <?php endforeach; ?>

            </div>
        </div>
    </div> -->


    <style>
        /* Promo Strip */
        .promo-strip {
            background: #f8f9fb;
            padding: 25px 0;
        }

        .promo-chip {
            background: linear-gradient(75deg, #bd4871, #d95b879e);
            border-radius: 5px;
            padding: 10px 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: #fff;
            box-shadow: 0 10px 25px rgba(189, 72, 113, .25);
            transition: .3s ease;
            overflow: hidden;
            position: relative;
            border: 1.5px dotted #ffffff;
            height: 100%;
        }

        .promo-chip:hover {
            transform: translateY(-5px);
            box-shadow: 0 18px 35px rgba(189, 72, 113, .35);
        }

        .promo-chip::before {
            content: "";
            position: absolute;
            top: 0;
            left: -120%;
            width: 60%;
            height: 100%;
            background: rgba(255, 255, 255, .18);
            transform: skewX(-25deg);
            transition: .7s;
        }

        .promo-chip:hover::before {
            left: 130%;
        }

        .promo-chip-text {
            font-size: 15.5px;
            font-weight: 500;
            line-height: 1.5;
        }

        .promo-chip-text strong {
            color: #fff;
            font-weight: 700;
        }

        .copy-btn {
            width: 48px;
            height: 48px;
            border: none;
            border-radius: 50%;
            background: #fff;
            color: #bd4871;
            font-size: 19px;
            display: flex;
            justify-content: center;
            align-items: center;
            cursor: pointer;
            transition: .3s;
            flex-shrink: 0;
        }

        .copy-btn:hover {
            transform: scale(1.1) rotate(15deg);
        }

        @media(max-width:992px) {
            .promo-chip {
                padding: 16px;
            }
        }



        @media (max-width:576px) {

            .promo-chip {
                display: flex;
                flex-direction: row;
                justify-content: space-between;
                align-items: center;
                gap: 10px;
                padding: 12px;
            }

            .promo-chip-text {
                flex: 1;
                text-align: left;
                font-size: 14px;
                line-height: 1.4;
            }

            .copy-btn {
                width: 42px;
                height: 42px;
                min-width: 42px;
                font-size: 16px;
            }

        }
    </style>
    <script>
        document.querySelectorAll(".copy-btn").forEach(btn => {

            btn.addEventListener("click", function() {

                navigator.clipboard.writeText(this.dataset.code);

                const old = this.innerHTML;

                this.innerHTML = '<i class="fa-solid fa-check"></i>';

                setTimeout(() => {
                    this.innerHTML = old;
                }, 1500);

            });

        });
    </script>

    <!-- ============ FEATURED PRODUCTS ============ -->
    <section class="section bg-surface-2">
        <div class="container">
            <div class="d-flex justify-content-between align-items-end mb-4">
                <div>
                    <span class="eyebrow">Handpicked</span>
                    <h2 class="h3 mt-1 mb-0">Bestsellers</h2>
                </div>
                <a href="shop" class="small">View all products <i class="bi bi-arrow-right"></i></a>
            </div>
            <!-- <div class="row g-3" id="homeProductRow"></div> -->


            <?php
            include 'config.php';

            $result = $conn->query("
    SELECT p.*, s.subcategory_name
    FROM products p
    LEFT JOIN subcategories s
        ON p.subcategory_id = s.id
    ORDER BY p.id DESC
");

            if (!$result) {
                die("Query Failed : " . $conn->error);
            }
            ?>
            <div class="row g-3">

                <?php while ($row = $result->fetch_assoc()) { ?>

                    <div class="col-6 col-md-4 col-lg-3">
                          <a href="b2b-product/<?= urlencode($row['name']) ?>">
                               
                            <div class="product-card">



                                <div class="thumb">
                                    <img src="../admin/uploads/<?= $row['image'] ?>"
                                        class="img-fluid product-img"
                                        alt="<?= $row['name'] ?>">
                                </div>



                                <div class="body">

                                    <?php if ($row['trending_type'] != "") { ?>
                                        <span class="badge badge-soft mb-2 d-none">
                                            <?= $row['trending_type'] ?>
                                        </span>
                                    <?php } ?>

                                    <div class="cat text-uppercase d-none">
                                        <?= $row['subcategory_name'] ?>
                                    </div>



                                    <div class="name text-center">
                                        <?= $row['name'] ?>
                                    </div>


                                    <div class="rating small mb-2 d-none">
                                        ★★★★★
                                        <span class="text-muted">(5.0)</span>
                                    </div>

                                    <div class="d-flex align-items-center gap-2">

                                        <!--<span class="price" data-price-inr="<?= $row['price'] ?>" style="margin:auto;">-->
                                        <!--    ₹<?= $row['price'] ?>-->
                                        <!--</span>-->
                                        
                                        <?php
$display_price = $row['price'] * 1.20;
?>

<span class="price"
      data-price-inr="<?= $display_price ?>"
      style="margin:auto;">
    ₹<?= number_format($display_price, 2) ?>
</span>

                                        <?php
                                        if ($row['rough_price'] > $row['price']) {

                                            $discount = round((($row['rough_price'] - $row['price']) / $row['rough_price']) * 100);
                                        ?>

                                            <span class="price-strike d-none" data-price-inr="<?= $row['rough_price'] ?>">
                                                ₹<?= $row['rough_price'] ?>
                                            </span>

                                            <span class="small text-success d-none">
                                                <?= $discount ?>% OFF
                                            </span>

                                        <?php } ?>

                                    </div>


                                    <button
                                        class="btn btn-brand btn-sm w-100 js-add-to-cart d-none"
                                        data-id="<?= $row['id'] ?>">
                                        <i class="bi bi-cart-plus"></i>
                                        Add to Cart
                                    </button>

                                </div>
                        

                    </div>
                    </a>

            </div>

        <?php } ?>

        </div>
        </div>
    </section>

    <!-- ============ B2B / B2C STRIP ============ -->
    <section class="section">
        <div class="container">
            <div class="row g-4">
                <div class="col-md-6" >
                    <div class="card-panel h-100">
                        <i class="bi bi-briefcase fs-2" style="color:var(--primary);"></i>
                        <h3 class="h5 mt-3">Buying for your business?</h3>
                        <p class="text-muted-soft mb-0">Customers in India automatically see B2B pricing — bulk slabs, GST-inclusive invoicing and dedicated support for salons, clinics and retailers.</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card-panel h-100">
                        <i class="bi bi-bag-heart fs-2" style="color:var(--primary);"></i>
                        <h3 class="h5 mt-3">Shopping for yourself?</h3>
                        <p class="text-muted-soft mb-0">Switch your country in the header and we'll instantly convert every price to your local currency in standard retail (B2C) mode — no page reload needed.</p>
                    </div>
                </div>
                 <div class="col-md-6">
      <div class="card-panel h-100">
    <i class="bi bi-truck fs-2" style="color:var(--primary);"></i>

    <h3 class="h5 mt-3">Fast Shipping</h3>

    <p class="text-muted-soft mb-0">
        Get your favorite styles delivered quickly with reliable shipping, real-time order tracking, and secure packaging.
    </p>
</div>
</div>
<div class="col-md-6">
<div class="card-panel h-100">
    <i class="bi bi-tags fs-2" style="color:var(--primary);"></i>

    <h3 class="h5 mt-3">Fair Pricing</h3>

    <p class="text-muted-soft mb-0">
        Shop with confidence through transparent pricing, great value, and premium-quality products without hidden charges.
    </p>
</div>
</div>
<div class="col-md-6">
<div class="card-panel h-100">
    <i class="bi bi-patch-check fs-2" style="color:var(--primary);"></i>

    <h3 class="h5 mt-3">Why Choose Us</h3>

    <p class="text-muted-soft mb-0">
        We combine premium quality, trusted service, secure payments, and excellent customer support to deliver a seamless shopping experience.
    </p>
</div>
</div>
<div class="col-md-6">
<div class="card-panel h-100">
    <i class="bi bi-shield-check fs-2" style="color:var(--primary);"></i>

    <h3 class="h5 mt-3">Secure Shopping</h3>

    <p class="text-muted-soft mb-0">
        Shop safely with trusted payment methods, protected transactions, and complete privacy for every order you place.
    </p>
</div>
</div>
            </div>
        </div>
    </section>

    <!-- ============ TESTIMONIALS ============ -->
    
<section class="section-sm bg-surface-2">
  <div class="container">
    <div class="text-center mb-4">
      <span class="eyebrow">What people say</span>
      <h2 class="h3 mt-1">Loved by Fashion Lovers</h2>
    </div>

    <div class="row g-3">

      <div class="col-md-4" >
        <div class="testimonial-card" style="border-radius:0px !important;">
          <div class="rating mb-2">★★★★★</div>
          <p class="small mb-3">
            "The fabric quality is really good and the outfit looks exactly
            like the pictures. The fitting was comfortable and the overall
            experience was excellent."
          </p>
          <div class="small fw-semibold">— Priya S., Delhi</div>
        </div>
      </div>

      <div class="col-md-4">
        <div class="testimonial-card" style="border-radius:0px !important;">
          <div class="rating mb-2">★★★★★</div>
          <p class="small mb-3">
            "Loved the design and finishing. The outfit feels stylish,
            comfortable and perfect for both special occasions and regular wear.
            Will definitely shop again."
          </p>
          <div class="small fw-semibold">— Neha R., Mumbai</div>
        </div>
      </div>

      <div class="col-md-4">
        <div class="testimonial-card" style="border-radius:0px !important;">
          <div class="rating mb-2">★★★★★</div>
          <p class="small mb-3">
            "Really happy with my purchase. The quality, colour and stitching
            were exactly what I expected, and the delivery was smooth. Great
            value for the price."
          </p>
          <div class="small fw-semibold">— Anjali K., Lucknow</div>
        </div>
      </div>

    </div>
  </div>
</section>
 


    <!-- ============ NEWSLETTER ============ -->
    <section class="section">
        <div class="container">
            <div class="card-panel text-center py-5">
                <h3 class="h4">Get 10% off your first order</h3>
                <p class="text-muted-soft">Join our list for new launches and wellness tips.</p>
                <form class="d-flex justify-content-center gap-2 flex-wrap" onsubmit="event.preventDefault(); this.querySelector('button').textContent='Subscribed ✓';">
                    <input type="email" required class="form-control" style="max-width:320px;" placeholder="you@example.com">
                    <button class="btn btn-accent" type="submit">Subscribe</button>
                </form>
            </div>
        </div>
    </section>






    <!-- B2B Popup -->




    <!-- <div class="modal fade" id="b2bModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">

        <div class="modal-header">
          <h5 class="modal-title" style="margin:auto;">Welcome to Shubhankar</h5>
        </div>

        <div class="modal-body text-center">

          <h3 class="mb-3">This site Open in India</h3>

          <p class="text-muted">
            Are you purchasing for your business or personal use?
          </p>

          <div class="d-grid gap-3 mt-4">

            <button type="button" class="btn btn-dark btn-lg" id="b2bBtn">
              Business (B2B)
            </button>

          

          </div>

        </div>

      </div>
    </div>
  </div> -->


    <!-- <script>
    window.addEventListener("load", function() {

      fetch("https://ipwho.is/")
        .then(response => response.json())
        .then(data => {

          console.log(data);

          if (data.success && data.country_code === "IN") {

            const modal = new bootstrap.Modal(
              document.getElementById("b2bModal")
            );

            setTimeout(function() {
              modal.show();
            }, 300);

          }

        })
        .catch(function(error) {
          console.log(error);
        });

      document.getElementById("b2bBtn").onclick = function() {
        window.location.href = "in/b2b.php";
      };

      document.getElementById("b2cBtn").onclick = function() {

        const modal = bootstrap.Modal.getInstance(
          document.getElementById("b2bModal")
        );

        if (modal) {
          modal.hide();
        }

      };

    });
  </script> -->



    <!-- ============ FOOTER ============ -->
    <?php include 'footer.php'; ?>








    <script>
        document.addEventListener("DOMContentLoaded", function() {

            if (typeof NEH !== "undefined") {
                NEH.updatePriceEls();
            }

        });
    </script>

</body>

</html>