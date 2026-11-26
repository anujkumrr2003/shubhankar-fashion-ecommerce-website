<?php
if (session_status() == PHP_SESSION_NONE) {
  session_start();
}
?>



<?php
include 'config.php';
error_reporting(0);
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$sql = "
SELECT
    p.*,
    s.subcategory_name
FROM products p
LEFT JOIN subcategories s
ON p.subcategory_id=s.id
WHERE p.id='$id'
LIMIT 1
";

$result = $conn->query($sql);

if ($result->num_rows == 0) {
  die("Product not found");
}

$product = $result->fetch_assoc();
?>

<?php
$variants = $conn->query("
SELECT *
FROM product_variants
WHERE product_id='" . $product['id'] . "'
ORDER BY id ASC
");
?>


<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($product['meta_title']) ?></title>
  <meta name="keywords" content="<?= htmlspecialchars($product['meta_keywords']) ?>">
  <meta name="description" content="<?= htmlspecialchars($product['meta_description']) ?>">
  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <!-- Site styles -->
  <link rel="stylesheet" href="https://shubhankarfashion.com/in/style.css">

  <!-- ============ PRODUCT GALLERY + ZOOM STYLES ============ -->
  <style>
    /* --- Gallery layout --- */
    .pdp-gallery {
      display: flex;
      flex-direction: column;
      gap: 14px;
      position: relative;
    }

    .pdp-main {
      position: relative;
      width: 100%;
      aspect-ratio: 1/1;
      background: var(--surface-2, #f4f2ee);
      border: 1px solid var(--border, #e7e2da);
      border-radius: 16px;
      overflow: hidden;
      cursor: zoom-in;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .pdp-main img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      display: block;
      transition: opacity .22s ease, transform .3s ease;
      user-select: none;
      -webkit-user-drag: none;
    }

    .pdp-main img.pdp-fade {
      opacity: 0;
      transform: scale(.98);
    }

    /* Lens that follows the cursor on the main image (desktop only) */
    .pdp-lens {
      position: absolute;
      top: 0;
      left: 0;
      width: 150px;
      height: 150px;
      border: 2px solid var(--primary, #7a5c3e);
      background: rgba(255, 255, 255, .18);
      border-radius: 10px;
      pointer-events: none;
      opacity: 0;
      transition: opacity .15s ease;
      box-shadow: 0 0 0 2000px rgba(0, 0, 0, .06);
    }

    .pdp-main.pdp-zoom-active .pdp-lens {
      opacity: 1;
    }

    .pdp-zoom-hint {
      position: absolute;
      bottom: 10px;
      right: 10px;
      font-size: .72rem;
      background: rgba(0, 0, 0, .55);
      color: #fff;
      padding: 4px 9px;
      border-radius: 20px;
      display: flex;
      align-items: center;
      gap: 5px;
      opacity: .9;
      pointer-events: none;
      transition: opacity .2s ease;
    }

    .pdp-main.pdp-zoom-active .pdp-zoom-hint {
      opacity: 0;
    }

    /* Zoom result pane -- shown beside the main image on large screens */
    .pdp-zoom-pane {
      position: absolute;
      top: 0;
      left: calc(100% + 16px);
      width: 100%;
      aspect-ratio: 1/1;
      border: 1px solid var(--border, #e7e2da);
      border-radius: 16px;
      background-color: #fff;
      background-repeat: no-repeat;
      box-shadow: 0 12px 30px rgba(0, 0, 0, .12);
      opacity: 0;
      visibility: hidden;
      transition: opacity .18s ease;
      z-index: 20;
    }

    .pdp-zoom-pane.pdp-visible {
      opacity: 1;
      visibility: visible;
    }

    /* Only show the side zoom pane where there's room for it */
    @media (max-width: 1399.98px) {
      .pdp-zoom-pane {
        display: none;
      }
    }

    /* --- Thumbnails --- */
    .pdp-thumbs {
      display: flex;
      gap: 10px;
      overflow-x: auto;
      padding-bottom: 2px;
      scrollbar-width: thin;
    }

    .pdp-thumbs::-webkit-scrollbar {
      height: 6px;
    }

    .pdp-thumbs::-webkit-scrollbar-thumb {
      background: var(--border, #e2ddd3);
      border-radius: 6px;
    }

    .pdp-thumb {
      flex: 0 0 auto;
      width: 78px;
      height: 78px;
      border-radius: 10px;
      overflow: hidden;
      border: 2px solid transparent;
      background: var(--surface-2, #f4f2ee);
      cursor: pointer;
      padding: 0;
      transition: border-color .18s ease, transform .18s ease, box-shadow .18s ease;
    }

    .pdp-thumb img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      display: block;
    }

    .pdp-thumb:hover {
      transform: translateY(-2px);
    }

    .pdp-thumb.active {
      border-color: var(--primary, #7a5c3e);
      box-shadow: 0 4px 10px rgba(0, 0, 0, .08);
    }

    /* --- Fullscreen tap-to-zoom overlay (touch devices) --- */
    .pdp-lightbox {
      position: fixed;
      inset: 0;
      background: rgba(20, 18, 15, .92);
      display: flex;
      align-items: center;
      justify-content: center;
      z-index: 1080;
      opacity: 0;
      visibility: hidden;
      transition: opacity .2s ease;
      touch-action: pinch-zoom;
    }

    .pdp-lightbox.pdp-open {
      opacity: 1;
      visibility: visible;
    }

    .pdp-lightbox img {
      max-width: 92vw;
      max-height: 86vh;
      object-fit: contain;
      border-radius: 10px;
      transition: transform .2s ease;
    }

    .pdp-lightbox-close {
      position: absolute;
      top: 18px;
      right: 18px;
      width: 42px;
      height: 42px;
      border-radius: 50%;
      background: rgba(255, 255, 255, .15);
      color: #fff;
      border: none;
      font-size: 1.3rem;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    @media (max-width: 767.98px) {
      .pdp-main {
        cursor: zoom-in;
        border-radius: 12px;
      }

      .pdp-thumb {
        width: 64px;
        height: 64px;
      }
    }

    /* --- Size selector --- */
    .size-option {
      background-color: #fff;
      color: var(--primary, #7a5c3e);
      border: 1px solid var(--primary, #7a5c3e) !important;
      transition: background-color .15s ease, color .15s ease;
      padding: 0px 7px;
    }

    .size-option:hover {
      background-color: var(--surface-2, #f4f2ee);
    }

    .size-option.active {
      background-color: var(--primary, #7a5c3e) !important;
      color: #fff !important;
    }
  </style>

</head>

<body> <?php include 'header.php'; ?> <section class="section-sm bg-surface-2 border-bottom">
    <div class="container">
      <nav class="small text-muted-soft mb-2"><a href="index.php">Home</a> / <a href="shop.php">Shop</a> / <span id="crumbName">Product</span></nav>
    </div>
  </section>


  <?php include 'cat.php'; ?>

  <section class="section">
    <div class="container">
      <div class="row g-5">
        <div class="col-lg-6"> <!-- ============ PRODUCT GALLERY ============ -->
          <div class="pdp-gallery" id="pdpGallery">
            <div class="pdp-main" id="pdpMain">
              <img id="pdpMainImg"
                src="admin/uploads/<?= htmlspecialchars($product['image']); ?>"
                alt="<?= htmlspecialchars($product['name']); ?>"
                draggable="false">
              <div class="pdp-lens" id="pdpLens"></div>
              <span class="pdp-zoom-hint">
                <i class="bi bi-zoom-in"></i>
                <span class="d-none d-md-inline">Hover to Zoom</span>
                <span class="d-inline d-md-none">Tap to Zoom</span>
              </span>
            </div>

            <div class="pdp-zoom-pane" id="pdpZoomPane"></div>

            <!-- Thumbnails -->
            <div class="pdp-thumbs" id="pdpThumbs">
              <!--
                data-variant-id="0" means "base product" (no variant selected).
                This is what gets sent to add-to-cart.php.
              -->
              <button
                type="button"
                class="pdp-thumb active"
                data-src="admin/uploads/<?= htmlspecialchars($product['image']); ?>"
                data-price="<?= $product['price']; ?>"
                data-old-price="<?= $product['rough_price']; ?>"
                data-variant-id="0">
                <img src="admin/uploads/<?= htmlspecialchars($product['image']); ?>">
              </button>

              <!-- Variant Images -->
              <?php
              $variants->data_seek(0);

              while ($v = $variants->fetch_assoc()) {

                $imgs = explode(',', $v['images']);

                foreach ($imgs as $img) {

                  $img = trim($img);

                  if ($img == "") continue;
              ?>
                  <button
                    type="button"
                    class="pdp-thumb"
                    data-src="admin/img/<?= htmlspecialchars($img) ?>"
                    data-price="<?= $v['variant_price'] ?>"
                    data-old-price="<?= $product['rough_price'] ?>"
                    data-variant-id="<?= (int) $v['id'] ?>">
                    <img src="admin/img/<?= htmlspecialchars($img) ?>" alt="">
                  </button>

              <?php
                }
              }
              ?>
            </div>
          </div>
          <!-- ============ /PRODUCT GALLERY ============ -->
        </div>

        <?php
        $discount = 0;

        if ($product['rough_price'] > $product['price']) {
          $discount = round(
            (($product['rough_price'] - $product['price']) / $product['rough_price']) * 100
          );
        }
        ?>



        <div class="col-lg-6"> <span class="cat text-uppercase small text-muted-soft">


            <?= $product['subcategory_name'] ?>
          </span>
          <h1 class="h3 mt-1"> <?= $product['name'] ?> </h1>
          <div class="rating mb-2"> ★★★★★ <span class="text-muted">(5.0)</span>
          </div>

          <div class="d-flex align-items-center gap-3 mb-3">

            <span id="productPrice"
              class="price fs-3"
              data-price-inr="<?= $product['price']; ?>">
              ₹<?= number_format($product['price']); ?>
            </span>

            <span id="productOldPrice"
              class="price-strike fs-5"
              data-price-inr="<?= $product['rough_price']; ?>"
              <?= ($product['rough_price'] > $product['price']) ? '' : 'style="display:none;"'; ?>>
              ₹<?= number_format($product['rough_price']); ?>
            </span>

            <span id="discountBadge"
              class="badge bg-success"
              <?= ($discount > 0) ? '' : 'style="display:none;"'; ?>>
              <?= $discount; ?>% OFF
            </span>

          </div>


          <div class="mb-3">
            <label class="fw-semibold d-block mb-2">Available Sizes</label>

            <?php
            if (!empty($product['size'])) {

              $sizes = array_map('trim', explode(',', $product['size']));

              foreach ($sizes as $size) {
                echo '<button type="button" class="btn size-option me-2 mb-2 " style="border-radius:10px !important;" data-size="'
                  . htmlspecialchars($size) . '">'
                  . htmlspecialchars($size) .
                  '</button>';
              }
            } else {

              echo '<span class="text-muted">No size available</span>';
            }
            ?>
          </div>

          <div class="b2c-only d-none mb-3"><span class="badge-soft px-2 py-1 rounded">Inclusive of all taxes</span>

          </div>
          <p class="text-muted-soft"> <?= $product['about_product'] ?> </p>
          <div class="d-flex align-items-center gap-3 my-4">
            <span class="fw-semibold small">Quantity</span>
            <div class="qty-box"> <button type="button" id="qtyMinus">−</button>
              <input type="text" id="qtyValue" value="1" readonly>
              <button type="button" id="qtyPlus">+</button>
            </div>
          </div>
          <div class="d-flex gap-2 flex-wrap">

            <button
              type="button"
              id="addBtn"
              class="btn btn-brand btn-lg"
              data-id="<?= $product['id']; ?>">

              <i class="bi bi-cart-plus"></i>
              Add to Cart

            </button>

            <button
              type="button"
              id="buyNowBtn"
              class="btn btn-outline-brand btn-lg"
              data-id="<?= $product['id']; ?>">

              Buy Now

            </button>


          </div>
          <ul class="list-unstyled small text-muted-soft mt-4">
            <li class="mb-1"><i class="bi bi-truck"></i> Free shipping on prepaid orders above &#8377;999 / equivalent</li>
            <li class="mb-1"><i class="bi bi-arrow-counterclockwise"></i> 7-day easy return policy</li>
            <li class="mb-1"><i class="bi bi-shield-check"></i> 100% authentic, dermatologically tested</li>
          </ul>
        </div>
      </div>
      <div class="mt-5">
        <h2 class="h5 mb-3">You may also like</h2> <?php $related = $conn->query(" SELECT * FROM products WHERE subcategory_id='" . $product['subcategory_id'] . "' AND id!='" . $product['id'] . "' LIMIT 4 "); ?> <div class="row g-3"> <?php while ($r = $related->fetch_assoc()) { ?> <div class="col-md-3">
              <div class="product-card"> <a href="product.php?id=<?= $r['id'] ?>"> <img src="admin/uploads/<?= $r['image'] ?>" class="img-fluid"> </a>
                <div class="body">
                  <div class="name text-center"> <?= $r['name'] ?> </div>
                  <div class="price text-center"> ₹<?= $r['price'] ?> </div>
                </div>
              </div>
            </div> <?php } ?> </div>
      </div>
    </div>
  </section>

  <!-- Fullscreen tap-to-zoom overlay (mobile / touch) -->
  <div class="pdp-lightbox" id="pdpLightbox"> <button type="button" class="pdp-lightbox-close" id="pdpLightboxClose" aria-label="Close"><i class="bi bi-x-lg"></i></button> <img id="pdpLightboxImg" src="" alt=""> </div> <!-- ============ FOOTER ============ -->
  <?php include 'footer.php'; ?>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <script>
    document.addEventListener("DOMContentLoaded", function() {
      // Quantity
      let qty = 1;
      const minus = document.getElementById("qtyMinus");
      const plus = document.getElementById("qtyPlus");
      const qtyInput = document.getElementById("qtyValue");

      minus.addEventListener("click", () => {
        if (qty > 1) qty--;
        qtyInput.value = qty;
      });
      plus.addEventListener("click", () => {
        qty++;
        qtyInput.value = qty;
      });

      // ====================== SIZE SELECTION ======================
      let selectedSize = null;
      const sizeButtons = document.querySelectorAll(".size-option");

      sizeButtons.forEach(btn => {
        btn.addEventListener("click", () => {
          sizeButtons.forEach(b => b.classList.remove("active"));
          btn.classList.add("active");
          selectedSize = btn.dataset.size;
        });
      });

      // Returns true if it's OK to proceed (either a size was selected,
      // or the product has no sizes to choose from at all).
      function sizeRequiredCheck() {
        if (sizeButtons.length === 0) {
          return true;
        }

        // if (!selectedSize) {
        //   alert("Please select a size before continuing.");
        //   return false;
        // }
        if (!selectedSize) {
          Swal.fire({
            icon: "warning",
            title: "Select Size",
            text: "Please select a size before continuing.",
            confirmButtonText: "OK",
            confirmButtonColor: "#000"
          });

          return false;
        }

        return true;
      }

      // ====================== GALLERY + ZOOM ======================

      const priceEl = document.getElementById("productPrice");
      const oldPriceEl = document.getElementById("productOldPrice");
      const discountEl = document.getElementById("discountBadge");

      function updatePrice(price, oldPrice) {

        price = parseFloat(price) || 0;
        oldPrice = parseFloat(oldPrice) || 0;

        // Current Price
        priceEl.setAttribute("data-price-inr", price);
        priceEl.innerHTML = "₹" + price.toLocaleString('en-IN');

        // Currency conversion
        if (typeof NEH !== "undefined" && typeof NEH.updatePriceEls === "function") {
          NEH.updatePriceEls();
        }

        // Old Price
        if (oldPriceEl) {

          if (oldPrice > price) {

            oldPriceEl.style.display = "";

            oldPriceEl.setAttribute("data-price-inr", oldPrice);

            oldPriceEl.innerHTML = "₹" + oldPrice.toLocaleString('en-IN');

          } else {

            oldPriceEl.style.display = "none";

          }

        }

        // Discount
        if (discountEl) {

          if (oldPrice > price) {

            let discount = Math.round(((oldPrice - price) / oldPrice) * 100);

            discountEl.style.display = "";

            discountEl.innerHTML = discount + "% OFF";

          } else {

            discountEl.style.display = "none";

          }

        }

        // Again update converted currency
        if (typeof NEH !== "undefined" && typeof NEH.updatePriceEls === "function") {
          NEH.updatePriceEls();
        }

      }

      const main = document.getElementById("pdpMain");
      const mainImg = document.getElementById("pdpMainImg");
      const lens = document.getElementById("pdpLens");
      const pane = document.getElementById("pdpZoomPane");
      const thumbsContainer = document.getElementById("pdpThumbs");
      const zoomLevel = 2.5;

      let currentSrc = mainImg.src;

      // Tracks which variant is currently selected. "0" = base product (no variant).
      let currentVariantId = "0";

      function switchImage(src, btn) {

        mainImg.classList.add("pdp-fade");

        setTimeout(() => {

          mainImg.src = src;
          currentSrc = src;
          pane.style.backgroundImage = `url('${src}')`;

          mainImg.classList.remove("pdp-fade");

        }, 180);

        document.querySelectorAll(".pdp-thumb")
          .forEach(x => x.classList.remove("active"));

        btn.classList.add("active");

        // Remember the selected variant so Add to Cart / Buy Now can send it
        currentVariantId = btn.dataset.variantId || "0";

        updatePrice(
          btn.dataset.price,
          btn.dataset.oldPrice
        );
      }

      thumbsContainer.querySelectorAll('.pdp-thumb').forEach(btn => {
        btn.addEventListener('click', () => {
          switchImage(btn.dataset.src, btn);
        });
      });

      // ====================== DESKTOP ZOOM ======================
      if (window.matchMedia("(hover: hover)").matches) {

        main.addEventListener("mouseenter", () => {
          pane.classList.add("pdp-visible");
          main.classList.add("pdp-zoom-active");
          pane.style.backgroundImage = `url('${currentSrc}')`;
          pane.style.backgroundSize = (main.offsetWidth * zoomLevel) + "px " + (main.offsetHeight * zoomLevel) + "px";
        });

        main.addEventListener("mousemove", (e) => {
          const rect = main.getBoundingClientRect();
          const lensW = lens.offsetWidth;
          const lensH = lens.offsetHeight;

          let x = e.clientX - rect.left - lensW / 2;
          let y = e.clientY - rect.top - lensH / 2;

          x = Math.max(0, Math.min(x, rect.width - lensW));
          y = Math.max(0, Math.min(y, rect.height - lensH));

          lens.style.left = x + "px";
          lens.style.top = y + "px";

          const ratioX = (rect.width * zoomLevel - pane.offsetWidth) / (rect.width - lensW);
          const ratioY = (rect.height * zoomLevel - pane.offsetHeight) / (rect.height - lensH);

          pane.style.backgroundSize = (rect.width * zoomLevel) + "px " + (rect.height * zoomLevel) + "px";
          pane.style.backgroundPosition = (-x * ratioX) + "px " + (-y * ratioY) + "px";
        });

        main.addEventListener("mouseleave", () => {
          pane.classList.remove("pdp-visible");
          main.classList.remove("pdp-zoom-active");
        });
      }

      // ====================== MOBILE LIGHTBOX ======================
      const lightbox = document.getElementById("pdpLightbox");
      const lightboxImg = document.getElementById("pdpLightboxImg");
      const lightboxClose = document.getElementById("pdpLightboxClose");

      if (!window.matchMedia("(hover: hover)").matches) {
        main.addEventListener("click", () => {
          lightbox.classList.add("pdp-open");
          lightboxImg.src = currentSrc;
          document.body.style.overflow = "hidden";
        });

        lightboxClose.addEventListener("click", () => {
          lightbox.classList.remove("pdp-open");
          document.body.style.overflow = "";
        });
      }

      // ====================== CART ======================
      const addBtn = document.getElementById("addBtn");
      const buyBtn = document.getElementById("buyNowBtn");

      function addCart(productId, qty, buyNow = false) {

        fetch("add-to-cart.php", {

            method: "POST",

            headers: {
              "Content-Type": "application/x-www-form-urlencoded"
            },

            // currentVariantId ("0" for base product) now travels with the request
            // body: "product_id=" + productId +
            //   "&qty=" + qty +
            //   "&variant_id=" + currentVariantId

            body: "product_id=" + productId +
              "&qty=" + qty +
              "&variant_id=" + currentVariantId +
              "&size=" + encodeURIComponent(selectedSize)

          })

          .then(res => res.text())

          .then(function(data) {

            data = data.trim();

            if (data == "login") {

              window.location = "login.php";

              return;

            }

            // if (data == "success") {

            //   if (buyNow) {

            //     window.location = "cart.php";

            //   } else {

            //     addBtn.innerHTML = '<i class="bi bi-check2"></i> Added';

            //     setTimeout(function() {

            //       addBtn.innerHTML = '<i class="bi bi-cart-plus"></i> Add to Cart';

            //     }, 1500);

            //   }

            // }


            if (data == "success") {

              // Update header cart count instantly
              if (typeof loadCartCount === "function") {
                loadCartCount();
              }

              if (buyNow) {

                window.location = "cart.php";

              } else {

                addBtn.innerHTML = '<i class="bi bi-check2"></i> Added';

                setTimeout(function() {

                  addBtn.innerHTML = '<i class="bi bi-cart-plus"></i> Add to Cart';

                }, 1500);

              }

            }

          });

      }

      addBtn.addEventListener("click", function() {

        if (!sizeRequiredCheck()) return;

        addCart(this.dataset.id, qty, false);

      });

      buyBtn.addEventListener("click", function() {

        if (!sizeRequiredCheck()) return;

        addCart(this.dataset.id, qty, true);

      });


    });
  </script>
  <script>
    document.addEventListener("DOMContentLoaded", function() {
      // categories
      document.getElementById("homeCategoryRow").innerHTML = CATEGORIES.map(c => `
      <div class="col-4 col-md-2">
        <a href="shop.php?cat=${c.slug}" class="cat-tile">
          <div class="ic"><i class="bi ${c.icon}"></i></div>
          <div class="small fw-semibold">${c.name}</div>
        </a>
      </div>`).join("");

      // featured products (first 8 marked bestseller/new, else first 8)
      const featured = PRODUCTS.filter(p => p.badge).slice(0, 8);
      document.getElementById("homeProductRow").innerHTML = featured.map(NEH.productCardHTML).join("");
      document.dispatchEvent(new CustomEvent("neh:rendered"));
      NEH.updatePriceEls();
    });
  </script>
</body>

</html>