<?php
if (session_status() == PHP_SESSION_NONE) {
  session_start();
}
error_reporting(0);
?>


 <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

<!-- ============ TOP UTILITY BAR ============ -->
<div class="topbar py-2 d-none d-md-block">
  <div class="container d-flex justify-content-between align-items-center">
    <div><i class="bi bi-truck"></i>&nbsp; Free shipping on prepaid orders above ₹999 / equivalent</div>
    <div>
      <a href="contact" class="me-3 text-white"><i class="bi bi-headset"></i> Support</a>
      <a href="faq" class="text-white"><i class="bi bi-question-circle"></i> Help &amp; FAQ</a>
    </div>
  </div>
</div>

<!-- ============ MAIN HEADER ============ -->
<header class="site-header">
  <div class="container py-3">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">

     <a href="https://shubhankarfashion.com/" class="brand-mark d-flex align-items-center text-decoration-none">
    <img src="img/logo-removebg-preview.png" alt="Shubhankar" style="height:50px;width:auto;margin-right:10px;">
    
</a>

      <nav class="main-nav d-none d-lg-flex">
        <ul class="nav">
          <li class="nav-item"><a class="nav-link" href="https://shubhankarfashion.com/">Home</a></li>
          <li class="nav-item"><a class="nav-link" href="shop">Shop</a></li>
          <li class="nav-item"><a class="nav-link" href="categories">Categories</a></li>
          <li class="nav-item"><a class="nav-link" href="about">About</a></li>
          <li class="nav-item"><a class="nav-link" href="contact">Contact</a></li>
        </ul>
      </nav>

      <div class="d-flex align-items-center gap-2">

        <!-- Country / Currency switcher -->
        <!--<div class="dropdown">-->
        <!--  <button class="locale-pill" data-bs-toggle="dropdown" aria-expanded="false">-->
        <!--    <span class="locale-dot"></span>-->
        <!--    <span class="js-locale-name">United States</span>-->
        <!--    <span class="text-muted-soft">·</span>-->
        <!--    <span class="js-locale-currency">US</span>-->
        <!--    <i class="bi bi-chevron-down small ms-1"></i>-->
        <!--  </button>-->
        <!--  <div class="dropdown-menu dropdown-menu-end shadow-sm p-1" style="min-width:240px;">-->
        <!--    <div class="px-2 pt-1 pb-2 small text-muted-soft">Ship to / Sell as</div>-->
        <!--    <div id="countryPickerList"></div>-->
        <!--  </div>-->
        <!--</div>-->
        
      <div class="dropdown">
          <button class="locale-pill" data-bs-toggle="dropdown" aria-expanded="false">
            <span class="locale-dot"></span>
            <!-- <span class="js-locale-name">India</span>
            <span class="text-muted-soft">·</span>
            <span class="js-locale-currency">INR</span> -->
            <span class="js-locale-name"></span>
            <span class="text-muted-soft">·</span>
            <span class="js-locale-currency"></span>
            <i class="bi bi-chevron-down small ms-1"></i>
          </button>
          <div class="dropdown-menu dropdown-menu-end shadow-sm p-1" style="min-width:240px;">
            <div class="px-2 pt-1 pb-2 small text-muted-soft">Ship to / Sell as</div>
            <div id="countryPickerList"></div>
          </div>
        </div>



        <span class="mode-badge js-mode-badge d-none d-sm-inline-block">B2C Pricing</span>

        <!-- <a href="login" class="icon-btn d-none d-sm-inline-flex" title="Account"><i class="bi bi-person"></i></a> -->
        <?php if (isset($_SESSION['user_id'])) { ?>

          <div class="dropdown">

            <button class="icon-btn d-none d-sm-inline-flex"
              data-bs-toggle="dropdown"
              aria-expanded="false">

              <i class="bi bi-person-circle"></i>

            </button>

            <div class="dropdown-menu dropdown-menu-end shadow p-3" style="width:280px;">

              <div class="text-center mb-3">

                <div class="rounded-circle bg-light d-inline-flex justify-content-center align-items-center"
                  style="width:65px;height:65px;">

                  <i class="bi bi-person fs-2"></i>

                </div>

                <h6 class="mt-2 mb-0">
                  <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                </h6>

                <small class="text-muted">
                  <?php echo htmlspecialchars($_SESSION['user_email']); ?>
                </small>

              </div>

              <hr>

              <a class="dropdown-item" href="my-profile">
                <i class="bi bi-person me-2"></i>
                My Profile
              </a>
  
              <a class="dropdown-item" href="wishlist">
                <i class="bi bi-heart me-2"></i>  
                My Wishlist
              </a>
              <a class="dropdown-item" href="my-orders">
                <i class="bi bi-bag-check me-2"></i>
                My Orders
              </a>
              <a class="dropdown-item" href="my-address">
                <i class="bi bi-geo-alt me-2"></i>
                My Address
              </a>

              <a class="dropdown-item" href="change-password">
                <i class="bi bi-lock me-2"></i>
                Change Password
              </a>

              <hr>

              <a class="dropdown-item text-danger" href="logout">
                <i class="bi bi-box-arrow-right me-2"></i>
                Logout
              </a>

            </div>

          </div>

        <?php } else { ?>

          <a href="login"
            class="icon-btn d-none d-sm-inline-flex"
            title="Login">

            <i class="bi bi-person"></i>

          </a>

        <?php } ?>

        <?php
        if (!isset($conn)) {
          include 'config.php';
        }

        $cartCount = 0;

        if (isset($_SESSION['user_id'])) {

          $user_id = (int)$_SESSION['user_id'];

          $cartResult = mysqli_query($conn, "
        SELECT IFNULL(SUM(qty),0) AS total
        FROM cart
        WHERE user_id='$user_id'
    ");

          if ($cartResult) {
            $cartRow = mysqli_fetch_assoc($cartResult);
            $cartCount = (int)$cartRow['total'];
          }
        }
        ?>

        <a href="cart" class="icon-btn position-relative" title="Cart">
          <i class="bi bi-bag"></i>

          <span
            class="cart-count"
            id="cartCount"
            <?= ($cartCount == 0) ? 'style="display:none;"' : ''; ?>>
            <?= $cartCount; ?>
          </span>
        </a>

        <script>
          function loadCartCount() {

            fetch("cart-count?" + new Date().getTime())

              .then(res => res.text())

              .then(function(count) {

                count = parseInt(count) || 0;

                document.querySelectorAll("#cartCount").forEach(function(badge) {

                  badge.innerHTML = count;

                  if (count > 0) {
                    badge.style.display = "flex";
                  } else {
                    badge.style.display = "none";
                  }

                });

              })

              .catch(function(error) {
                console.log(error);
              });

          }

          document.addEventListener("DOMContentLoaded", function() {
            loadCartCount();
          });
        </script>

        <button class="icon-btn d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileNav">
          <i class="bi bi-list"></i>
        </button>
      </div>
    </div>
  </div>
</header>

<!-- Mobile nav offcanvas -->
<!-- <div class="offcanvas offcanvas-end" tabindex="-1" id="mobileNav">
  <div class="offcanvas-header">
    <span class="brand-mark">Neh<span>well</span></span>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
  </div>
  <div class="offcanvas-body">
    <ul class="nav flex-column">
      <li class="nav-item"><a class="nav-link" href="index">Home</a></li>
      <li class="nav-item"><a class="nav-link" href="shop">Shop</a></li>
      <li class="nav-item"><a class="nav-link" href="categories">Categories</a></li>
      <li class="nav-item"><a class="nav-link" href="about">About</a></li>
      <li class="nav-item"><a class="nav-link" href="contact">Contact</a></li>
      <li class="nav-item"><a class="nav-link" href="login">Login / Register</a></li>
    </ul>
  </div>
</div> -->

<!-- Mobile nav offcanvas -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="mobileNav">
  <div class="offcanvas-header">
       <a href="index" class="brand-mark d-flex align-items-center text-decoration-none">
    <img src="img/logo-removebg-preview.png" alt="Shubhankar" style="height:40px;width:auto;margin-right:10px;">
    
</a>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
  </div>
  <ul class="nav flex-column">

    <li class="nav-item">
      <a class="nav-link" href="index">Home</a>
    </li>

    <li class="nav-item">
      <a class="nav-link" href="shop">Shop</a>
    </li>

    <li class="nav-item">
      <a class="nav-link" href="categories">Categories</a>
    </li>

    <li class="nav-item">
      <a class="nav-link" href="about">About</a>
    </li>

    <li class="nav-item">
      <a class="nav-link" href="contact">Contact</a>
    </li>

    <?php if (isset($_SESSION['user_id'])) { ?>

      <li class="nav-item mt-3 mb-2">
        <div class="text-center">

          <div class="rounded-circle bg-light d-inline-flex justify-content-center align-items-center"
            style="width:70px;height:70px;">

            <i class="bi bi-person-circle fs-1"></i>

          </div>

          <h6 class="mt-2 mb-0">
            <?= htmlspecialchars($_SESSION['user_name']); ?>
          </h6>

          <small class="text-muted">
            <?= htmlspecialchars($_SESSION['user_email']); ?>
          </small>

        </div>
      </li>

      <hr>

      <li class="nav-item">
        <a class="nav-link" href="my-profile">
          <i class="bi bi-person me-2"></i>
          My Profile
        </a>
      </li>
      
      <li class="nav-item">
        <a class="nav-link" href="wishlist">
         <i class="bi bi-heart me-2"></i>
         My Wishlist
        </a>
      </li>
      
      <li class="nav-item">
        <a class="nav-link" href="my-orders">
          <i class="bi bi-bag-check me-2"></i>
          My Orders
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link" href="my-address">
          <i class="bi bi-geo-alt me-2"></i>
          My Address
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link" href="change-password">
          <i class="bi bi-lock me-2"></i>
          Change Password
        </a>
      </li>

      <hr>

      <li class="nav-item">
        <a class="nav-link text-danger" href="logout">
          <i class="bi bi-box-arrow-right me-2"></i>
          Logout
        </a>
      </li>

    <?php } else { ?>

      <li class="nav-item">
        <a class="nav-link" href="login">
          <i class="bi bi-person me-2"></i>
          Login / Register
        </a>
      </li>

    <?php } ?>

  </ul>
</div>



<style>
  /* Mobile Menu */
  #mobileNav .offcanvas-header {
    border-bottom: 1px solid #eee;
  }

  #mobileNav .offcanvas-body {
    padding: 20px;
  }

  .mobile-user-box {
    background: #f8f9fa;
    border-radius: 16px;
    padding: 18px;
    text-align: center;
    margin-bottom: 20px;
  }

  .mobile-user-box .avatar {
    width: 70px;
    height: 70px;
    border-radius: 50%;
    background: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: auto;
    box-shadow: 0 5px 15px rgba(0, 0, 0, .08);
  }

  .mobile-user-box .avatar i {
    font-size: 42px;
    color: #c50000;
  }

  .mobile-user-box h6 {
    margin-top: 12px;
    margin-bottom: 2px;
    font-weight: 600;
  }

  .mobile-user-box small {
    color: #777;
  }

  #mobileNav .nav-link {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 15px;
    border-radius: 10px;
    color: #333;
    font-weight: 500;
    transition: .3s;
  }

  #mobileNav .nav-link i {
    width: 22px;
    font-size: 18px;
  }

  #mobileNav .nav-link:hover {
    background: #c50000;
    color: #fff;
    transform: translateX(5px);
  }

  #mobileNav .logout {
    color: #dc3545;
  }

  #mobileNav .logout:hover {
    background: #dc3545;
    color: #fff;
  }
</style>






<style>
    /* =========================================================
   NEHWELL — Global Stylesheet
   Design tokens, layout primitives and reusable components.
   Organised so it is simple to lift into a PHP templating
   layer later (tokens -> components -> page-level helpers).
   ========================================================= */

:root{
  /* ---- Color tokens ---- */
  --bg:        #FAFAF7;   /* soft white site background  */
  --surface:   #FFFFFF;   /* cards, panels                */
  --surface-2: #F2F0E9;   /* subtle alternate section bg  */
  --text:      #20241F;   /* primary text                 */
  --muted:     #6B6F66;   /* secondary text                */
  --border:    #E7E4DA;   /* hairline borders               */
  /*--primary:   #bd4871;   */
  --primary:   #00005f;
  --primary-d: #21503A;   /* primary, darker (hover)       */
  --accent:    #C8973A;   /* warm gold — CTAs / highlights */
  --accent-d:  #AD7C24;   /* accent, darker (hover)        */
  /*--danger:    #B3432B;*/
  --danger:    #00005f;
  --success:   #2F6B4F;

  /* ---- Type ---- */
  --font-display: 'Fraunces', Georgia, serif;
  --font-body: 'Inter', -apple-system, Segoe UI, Roboto, sans-serif;

  /* ---- Radii / shadow ---- */
  --radius: 10px;
  --radius-sm: 6px;
  --shadow-sm: 0 1px 3px rgba(32,36,31,0.06);
  --shadow-md: 0 6px 24px rgba(32,36,31,0.08);
}

*{ box-sizing: border-box; }

html{ scroll-behavior: smooth; }

body{
  background: var(--bg);
  color: var(--text);
  font-family: var(--font-body);
  font-size: 16px;
  line-height: 1.6;
}

h1,h2,h3,h4,.font-display{
  font-family: var(--font-display);
  font-weight: 600;
  letter-spacing: -0.01em;
  color: var(--text);
}

a{ color: #00005f !important; text-decoration: none; }
a:hover{ color: var(--primary-d); }

:focus-visible{
  outline: 2px solid var(--accent);
  outline-offset: 2px;
}

.text-muted-soft{ color: var(--muted) !important; }
.bg-surface{ background: var(--surface); }
.bg-surface-2{ background: var(--surface-2); }
.section{ padding: 64px 0; }
.section-sm{ padding: 36px 0; }

.eyebrow{
  font-size: .75rem;
  letter-spacing: .14em;
  text-transform: uppercase;
  color: var(--accent-d);
  font-weight: 600;
}

/* =========== Buttons =========== */
.btn-brand{
  background: #00005f !important;
  border: 1px solid #00005f !important;
  color: #fff;
  font-weight: 600;
  border-radius: var(--radius-sm);
  padding: .65rem 1.4rem;
  transition: background .15s ease, transform .1s ease;
  border-radius: 0px;
}
.btn-brand:hover{ background: var(--primary-d); color:#fff; }
.btn-brand:active{ transform: translateY(1px); }

.btn-accent{
  background: #00005f !important;
  border: 1px solid var(--accent);
  color: #ffff;
  font-weight: 600;
  border-radius: var(--radius-sm);
  padding: .65rem 1.4rem;
}
.btn-accent:hover{ background: var(--accent-d); color:#1F1A0C; }

.btn-outline-brand{
  background: transparent;
  border: 1px solid #00005f !important;
  color: #00005f !important;
  font-weight: 600;
  border-radius: var(--radius-sm);
  padding: .6rem 1.3rem;
}
.btn-outline-brand:hover{ background: #00005f !important; color:#fff !important; }

.btn-ghost{
  background: transparent;
  border: 1px solid var(--border);
  color: var(--text);
  border-radius: var(--radius-sm);
}
.btn-ghost:hover{ background: var(--surface-2); }

/* =========== Header =========== */
.site-header{
  background: var(--surface);
  border-bottom: 1px solid var(--border);
  position: sticky;
  top: 0;
  z-index: 1030;
}
.topbar{
  background: #00005f !important;
  color: #EDEFE9;
  font-size: .8rem;
}
.topbar a{ color: #EDEFE9; }

.brand-mark{
  font-family: var(--font-display);
  font-weight: 700;
  font-size: 1.5rem;
  color: var(--text);
  letter-spacing: -0.02em;
}
.brand-mark span{ color: #00005f !important; }

.main-nav .nav-link{
  color: var(--text);
  font-weight: 500;
  font-size: .93rem;
  padding: .5rem .85rem !important;
}
.main-nav .nav-link:hover,
.main-nav .nav-link.active{ color: #00005f !important; }

/* Country / currency switcher — the page's signature control */
.locale-pill{
  display: inline-flex;
  align-items: center;
  gap: .45rem;
  border: 1px solid var(--border);
  border-radius: 999px;
  padding: .35rem .8rem .35rem .55rem;
  background: var(--surface);
  font-size: .82rem;
  font-weight: 600;
  cursor: pointer;
}
.locale-pill:hover{ border-color: #00005f !important; }
.locale-dot{
  width: 9px; height: 9px; border-radius: 50%;
  background: #00005f !important;
  box-shadow: 0 0 0 3px rgba(200,151,58,.18);
}
.mode-badge{
  font-size: .68rem;
  font-weight: 700;
  letter-spacing: .04em;
  padding: .15rem .5rem;
  border-radius: 999px;
  background: #00005f !important;
  color: #fff;
  border: 1px solid var(--border);
}

.icon-btn{
  width: 38px; height: 38px;
  display: inline-flex; align-items:center; justify-content:center;
  border-radius: 50%;
  border: 1px solid var(--border);
  background: var(--surface);
  position: relative;
  color: var(--text);
}
.icon-btn:hover{ border-color: #00005f !important; color: #00005f !important; }
.cart-count{
  position: absolute; top: -6px; right: -6px;
  background: #00005f !important; color:#ffff;
  font-size: .65rem; font-weight: 700;
  min-width: 18px; height: 18px; border-radius: 999px;
  display:flex; align-items:center; justify-content:center;
  border: 2px solid var(--surface);
}

/* =========== Hero =========== */
.hero{
  background: linear-gradient(180deg, var(--surface-2) 0%, var(--bg) 100%);
  border-bottom: 1px solid var(--border);
}
.hero-figure{
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: var(--radius);
  box-shadow: var(--shadow-md);
}

/* =========== Cards =========== */
.product-card{
  background: var(--surface);
  border: 1px solid var(--border);
  /* border-radius: var(--radius); */
  overflow: hidden;
  transition: box-shadow .15s ease, transform .15s ease;
  height: 100%;
}
.product-card:hover{ box-shadow: var(--shadow-md); transform: translateY(-2px); }
.product-card .thumb{
  aspect-ratio: 1/1;
  background: var(--surface-2);
  display:flex; align-items:center; justify-content:center;
  font-family: var(--font-display);
  color: #00005f !important;
  font-size: 2.4rem;
  border-bottom: 1px solid var(--border);
}
.product-card .body{ padding: 5px; }
.product-card .name{ font-weight: 600; font-size: .96rem; margin-bottom:.15rem; }
.product-card .cat{ font-size: .75rem; color: var(--muted); }
.price{ font-weight: 700; color: var(--text); }
.price-strike{ color: var(--muted); text-decoration: line-through; font-size:.85rem; font-weight:400; }
.b2b-note{ font-size: .72rem; color: var(--primary-d); background:#EAF1EC; border-radius: var(--radius-sm); padding: .15rem .5rem; display:inline-block; }

/* category chips */
.cat-chip{
  border: 1px solid var(--border);
  border-radius: 999px;
  padding: .4rem 1rem;
  background: var(--surface);
  font-size: .85rem;
  font-weight: 500;
  color: var(--text);
}
.cat-chip.active, .cat-chip:hover{ background: #00005f !important; border-color: #00005f !important; color:#fff; }

.cat-tile{
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: var(--radius);
  padding: 1.4rem 5px;
  text-align: center;
  display:block;
  color: var(--text);
}
.cat-tile:hover{ border-color: #00005f !important; box-shadow: var(--shadow-sm); color: var(--text); }
.cat-tile .ic{ font-size: 1.8rem; color: #00005f !important; margin-bottom:.4rem; }

/* =========== Forms =========== */
.form-control, .form-select{
  border-radius: var(--radius-sm);
  border: 1px solid var(--border);
  padding: .6rem .8rem;
}
.form-control:focus, .form-select:focus{
  border-color: #00005f !important;
  box-shadow: 0 0 0 .2rem rgba(47,107,79,.12);
}
label{ font-weight: 600; font-size: .85rem; }

.card-panel{
  background: var(--surface);
  border: 1px solid #9aa59a95;
  /* border-radius: var(--radius); */
  padding: 1.5rem;
}

/* steps for checkout */
.step-track{ display:flex; gap:.5rem; align-items:center; }
.step{
  display:flex; align-items:center; gap:.5rem;
  font-size:.85rem; font-weight:600; color: var(--muted);
}
.step .num{
  width: 26px; height:26px; border-radius:50%;
  border:1px solid var(--border);
  display:flex; align-items:center; justify-content:center;
  font-size:.75rem;
}
.step.active{ color: #00005f !important; }
.step.active .num{ background: #00005f !important; border-color: #00005f !important; color:#fff; }
.step-sep{ width: 28px; height: 1px; background: var(--border); }

/* =========== Footer =========== */
.site-footer{
  background: #00005f !important;
  color: #ffffff;
  padding: 56px 0 24px;
}
.site-footer a{ color: #ffffff; }
.site-footer a:hover{ color: #fff; }
.site-footer h6{ color:#fff; font-family: var(--font-display); letter-spacing:.02em; margin-bottom:1rem;}
.footer-bottom{ border-top: 1px solid rgb(255, 255, 255); margin-top: 32px; padding-top: 20px; font-size:.8rem; color:#9AA59A;}
.payment-icons span{
  border:1px solid rgb(255, 255, 255); border-radius:4px; padding:.15rem .45rem; font-size:.7rem; margin-right:.3rem; display:inline-block; margin-bottom:.3rem;
  background-color: #fff;
  color:#000;
}

/* misc */
.divider-soft{ border-top:1px solid var(--border); }
.badge-soft{ 
  background: var(--surface-2); 
  color: var(--primary-d); 
  border:1px solid var(--border); 
  font-weight:600;
  border-radius: 0px;
}
.testimonial-card{ background: var(--surface); border:1px solid var(--border); border-radius: var(--radius); padding: 1.4rem; height:100%;}
.rating{ color: var(--accent); letter-spacing:2px; }
.qty-box{ display:inline-flex; align-items:center; border:1px solid var(--border); border-radius: var(--radius-sm); }
.qty-box button{ width:34px; height:34px; border:none; background:transparent; font-weight:700; }
.qty-box input{ width:42px; text-align:center; border:none; background:transparent; font-weight:600; }

@media (max-width: 767px){
  .section{ padding: 42px 0; }
  .hero{ text-align:center; }
}




.product-card{
    border:0;
    border-radius:0;
    overflow:hidden;
    background:#fff;
}

.product-card .thumb{
    width:100%;
    height:520px;              /* Image container */
    background:#fff;
    display:flex;
    align-items:center;
    justify-content:center;
    overflow:hidden;
}

.product-card .product-img{
    width:100%;
    height:100%;               /* Parent ke equal */
    object-fit:cover;
    transition:.4s ease;
}

.product-card:hover .product-img{
    /* transform:scale(1.05); */
}

.product-card .body{
    padding:5px 0;
}

.product-card .name{
    font-size:18px;
    font-weight:500;
    line-height:1.5;
    /* min-height:54px; */
    color:#222;
}

.product-card .price{
    font-size:18px;
    font-weight:600;
}

/* Tablet */
@media (max-width:991px){
    .product-card .thumb{
        height:420px;
    }
}

/* Mobile */
@media (max-width:576px){
    .product-card .thumb{
        height:280px;
    }

    .product-card .name{
        font-size:15px;
        min-height:auto;
    }

    .product-card .price{
        font-size:16px;
    }
}
</style>










