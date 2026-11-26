<?php

if (session_status() == PHP_SESSION_NONE) {
  session_start();
}

include 'config.php';
error_reporting(0);

if (!isset($_GET['product']) || empty($_GET['product'])) {
  die("Product not found");
}

// $product_name = rawurldecode($_GET['product']);
$product_name = rawurldecode($_GET['product']);
// $product_name = str_replace('-', ' ', $product_name);
// $product_name = preg_replace('/\s+/', ' ', trim($product_name));
$product_name = mysqli_real_escape_string($conn, $product_name);

$sql = "
SELECT
    p.*,
    s.subcategory_name
FROM products p
LEFT JOIN subcategories s
ON p.subcategory_id = s.id
WHERE p.name = '$product_name'
LIMIT 1
";

$result = $conn->query($sql);

if (!$result || $result->num_rows == 0) {
  die("Product not found");
}

$product = $result->fetch_assoc();

$variants = $conn->query("
SELECT *
FROM product_variants
WHERE product_id = '" . $product['id'] . "'
ORDER BY id ASC
");
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($product['meta_title'] ?: $product['name']) ?> | Shubhankar</title>

  <meta name="title" content="<?= htmlspecialchars($product['meta_title'] ?: $product['name']) ?>">
  <meta name="description" content="<?= htmlspecialchars($product['meta_description']) ?>">
  <meta name="keywords" content="<?= htmlspecialchars($product['meta_keywords']) ?>">
  <meta name="robots" content="index,follow,max-image-preview:large,max-snippet:-1,max-video-preview:-1">

  <!--<link rel="canonical" href="https://shubhankarfashion.com/product/<?= rawurlencode($product['name']) ?>">-->
  <link rel="canonical" href="https://shubhankarfashion.com/product/<?= urlencode(preg_replace('/\s+/', ' ', trim($product['name']))) ?>">

  <!-- Open Graph -->
  <meta property="og:type" content="product">
  <meta property="og:site_name" content="Shubhankar">
  <meta property="og:title" content="<?= htmlspecialchars($product['meta_title'] ?: $product['name']) ?>">
  <meta property="og:description" content="<?= htmlspecialchars($product['meta_description']) ?>">
  <meta property="og:url" content="https://shubhankarfashion.com/product/<?= rawurlencode($product['name']) ?>">
  <meta property="og:image" content="https://shubhankarfashion.com/admin/uploads/<?= rawurlencode($product['image']) ?>">
  <meta property="og:image:secure_url" content="https://shubhankarfashion.com/admin/uploads/<?= rawurlencode($product['image']) ?>">
  <meta property="og:image:alt" content="<?= htmlspecialchars($product['name']) ?>">

  <!-- Twitter -->
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="<?= htmlspecialchars($product['meta_title'] ?: $product['name']) ?>">
  <meta name="twitter:description" content="<?= htmlspecialchars($product['meta_description']) ?>">
  <meta name="twitter:image" content="https://shubhankarfashion.com/admin/uploads/<?= rawurlencode($product['image']) ?>">

  <!-- Favicon -->
  <link rel="icon" type="image/png" href="https://shubhankarfashion.com/img/logo-removebg-preview.png">
  <link rel="apple-touch-icon" href="https://shubhankarfashion.com/img/logo-removebg-preview.png">

  <meta name="theme-color" content="#000000">

  <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "Product",
      "name": "<?= addslashes($product['name']) ?>",
      "image": "https://shubhankarfashion.com/admin/uploads/<?= rawurlencode($product['image']) ?>",
      "description": "<?= addslashes(strip_tags($product['meta_description'])) ?>",
      "sku": "<?= $product['id'] ?>",
      "brand": {
        "@type": "Brand",
        "name": "Shubhankar"
      },
      "offers": {
        "@type": "Offer",
        "url": "https://shubhankarfashion.com/product/<?= rawurlencode($product['name']) ?>",
        "priceCurrency": "INR",
        "price": "<?= $product['price'] ?>",
        "availability": "https://schema.org/InStock",
        "itemCondition": "https://schema.org/NewCondition"
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
  <base href="https://shubhankarfashion.com/">
  <link rel="stylesheet" href="https://shubhankarfashion.com/css/style.css">


<style>
    .wishlist-btn {
    position: absolute;
    top: 10px;
    right: 10px;
    z-index: 15;
    width: 38px;
    height: 38px;
    border-radius: 50%;
    border: none;
    background: rgba(255, 255, 255, 0.9);
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 1px 4px rgba(0,0,0,0.15);
    cursor: pointer;
    transition: transform .15s ease;
}
.wishlist-btn:hover { transform: scale(1.08); }
.wishlist-btn i { font-size: 1.15rem; color: #6c757d; }
.wishlist-btn.is-active i { color: #dc3545; }
</style>
  <!-- ============ PRODUCT GALLERY + ZOOM STYLES ============ -->
  <style>
    .pdp-gallery { display: flex; flex-direction: column; gap: 14px; position: relative; }
    .pdp-main { position: relative; width: 100%; aspect-ratio: 1/1; background: var(--surface-2, #f4f2ee); border: 1px solid var(--border, #e7e2da); overflow: hidden; cursor: zoom-in; display: flex; align-items: center; justify-content: center; }
    .pdp-main img { width: 100%; height: 100%; display: block; transition: opacity .22s ease, transform .3s ease; user-select: none; -webkit-user-drag: none; }
    .pdp-main img.pdp-fade { opacity: 0; transform: scale(.98); }
    .pdp-lens { position: absolute; top: 0; left: 0; width: 150px; height: 150px; border: 2px solid var(--primary, #7a5c3e); background: rgba(255, 255, 255, .18); border-radius: 10px; pointer-events: none; opacity: 0; transition: opacity .15s ease; box-shadow: 0 0 0 2000px rgba(0, 0, 0, .06); }
    .pdp-main.pdp-zoom-active .pdp-lens { opacity: 1; }
    .pdp-zoom-hint { position: absolute; bottom: 10px; right: 10px; font-size: .72rem; background: rgba(0, 0, 0, .55); color: #fff; padding: 4px 9px; border-radius: 20px; display: flex; align-items: center; gap: 5px; opacity: .9; pointer-events: none; transition: opacity .2s ease; }
    .pdp-main.pdp-zoom-active .pdp-zoom-hint { opacity: 0; }
    .pdp-zoom-pane { position: absolute; top: 0; left: calc(100% + 16px); width: 100%; aspect-ratio: 1/1; border: 1px solid var(--border, #e7e2da); border-radius: 16px; background-color: #fff; background-repeat: no-repeat; box-shadow: 0 12px 30px rgba(0, 0, 0, .12); opacity: 0; visibility: hidden; transition: opacity .18s ease; z-index: 20; }
    .pdp-zoom-pane.pdp-visible { opacity: 1; visibility: visible; }
    @media (max-width: 1399.98px) { .pdp-zoom-pane { display: none; } }
    .pdp-thumbs { display: flex; gap: 10px; overflow-x: auto; padding-bottom: 2px; scrollbar-width: thin; }
    .pdp-thumbs::-webkit-scrollbar { height: 6px; }
    .pdp-thumbs::-webkit-scrollbar-thumb { background: var(--border, #e2ddd3); border-radius: 6px; }
    .pdp-thumb { flex: 0 0 auto; width: 78px; height: 78px; border-radius: 0px; overflow: hidden; border: 2px solid transparent; background: var(--surface-2, #f4f2ee); cursor: pointer; padding: 0; transition: border-color .18s ease, transform .18s ease, box-shadow .18s ease; }
    .pdp-thumb img { width: 100%; height: 100%; object-fit: cover; display: block; }
    .pdp-thumb:hover { transform: translateY(-2px); }
    .pdp-thumb.active { border-color: var(--primary, #7a5c3e); box-shadow: 0 4px 10px rgba(0, 0, 0, .08); }
    .pdp-lightbox { position: fixed; inset: 0; background: rgba(20, 18, 15, .92); display: flex; align-items: center; justify-content: center; z-index: 1080; opacity: 0; visibility: hidden; transition: opacity .2s ease; touch-action: pinch-zoom; }
    .pdp-lightbox.pdp-open { opacity: 1; visibility: visible; }
    .pdp-lightbox img { max-width: 92vw; max-height: 86vh; object-fit: contain; border-radius: 10px; transition: transform .2s ease; }
    .pdp-lightbox-close { position: absolute; top: 18px; right: 18px; width: 42px; height: 42px; border-radius: 50%; background: rgba(255, 255, 255, .15); color: #fff; border: none; font-size: 1.3rem; display: flex; align-items: center; justify-content: center; }
    @media (max-width: 767.98px) { .pdp-main { cursor: zoom-in; border-radius: 5px; } .pdp-thumb { width: 64px; height: 64px; } }
    .size-option { background-color: #fff; color: var(--primary, #7a5c3e); border: 1px solid var(--primary, #7a5c3e) !important; transition: background-color .15s ease, color .15s ease; padding: 0px 7px; }
    .size-option:hover { background-color: var(--surface-2, #f4f2ee); }
    .size-option.active { background-color: var(--primary, #7a5c3e) !important; color: #fff !important; }
  </style>

</head>

<body>
  <?php include 'header.php'; ?>

  <section class="section-sm bg-surface-2 border-bottom">
    <div class="container">
      <nav class="small text-muted-soft mb-2"><a href="index">Home</a> / <a href="shop">Shop</a> / <span id="crumbName">Product</span></nav>
    </div>
  </section>

  <?php //include 'cat.php'; ?>

  <section class="section">
    <div class="container">
      <div class="row">

        <!-- LEFT SIDEBAR -->
        <div class="col-lg-3">
          <div class="category-sidebar">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <h4 class="mb-0">Categories</h4>
              <a href="categories" class="small">View All</a>
            </div>
<hr>
            <?php
            include 'config.php';

            $categories = $conn->query("
                         SELECT id, category_id, subcategory_name, image
    FROM subcategories
    WHERE status='Active'
    ORDER BY subcategory_name ASC
    LIMIT 10
                    ");

            while ($cat = $categories->fetch_assoc()) {

              $image = (!empty($cat['image']) && file_exists("admin/uploads/" . $cat['image']))
                ? "admin/uploads/" . $cat['image']
                : "admin/uploads/no-image.png";
            ?>
              <a href="shop.php?category=<?= urlencode($cat['subcategory_name']) ?>" class="category-item">
                <span><?= htmlspecialchars($cat['subcategory_name']) ?></span>
              </a>
            <?php } ?>

          </div>
        </div>
        <!-- END LEFT SIDEBAR -->

        <!-- RIGHT SIDE: PRODUCT GALLERY + DETAILS -->
        <div class="col-lg-9">
          <div class="row g-5">

            <div class="col-lg-6">
              <div class="pdp-gallery" id="pdpGallery">
                <!--<div class="pdp-main" id="pdpMain">-->
                <!--  <img id="pdpMainImg"-->
                <!--    src="admin/uploads/<?= htmlspecialchars($product['image']); ?>"-->
                <!--    alt="<?= htmlspecialchars($product['name']); ?>"-->
                <!--    draggable="false">-->
                <!--  <div class="pdp-lens" id="pdpLens"></div>-->
                <!--  <span class="pdp-zoom-hint">-->
                <!--    <i class="bi bi-zoom-in"></i>-->
                <!--    <span class="d-none d-md-inline">Hover to Zoom</span>-->
                <!--    <span class="d-inline d-md-none">Tap to Zoom</span>-->
                <!--  </span>-->
                <!--</div>-->
                
                <div class="pdp-main" id="pdpMain">

  <?php $wishlistSizes = !empty($product['size']) ? strtoupper(implode(',', array_filter(array_map('trim', explode(',', $product['size']))))) : ''; ?>

  <button
      type="button"
      class="wishlist-btn js-wishlist-toggle"
      data-id="<?= $product['id'] ?>"
      data-name="<?= htmlspecialchars($product['name']) ?>"
      data-price="<?= $product['price'] ?>"
      data-rough-price="<?= $product['rough_price'] ?>"
      data-image="https://shubhankarfashion.com/admin/uploads/<?= htmlspecialchars($product['image']) ?>"
      data-url="https://shubhankarfashion.com/product/<?= urlencode($product['name']) ?>"
      data-sizes="<?= htmlspecialchars($wishlistSizes) ?>"
      aria-label="Add to wishlist"
      onclick="event.stopPropagation();">
      <i class="bi bi-heart"></i>
  </button>

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

                <div class="pdp-thumbs" id="pdpThumbs">

                  <button
                    type="button"
                    class="pdp-thumb active"
                    data-src="admin/uploads/<?= htmlspecialchars($product['image']); ?>"
                    data-price="<?= $product['price']; ?>"
                    data-old-price="<?= $product['rough_price']; ?>"
                    data-variant-id="0">
                    <img src="admin/uploads/<?= htmlspecialchars($product['image']); ?>">
                  </button>

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
            </div>

            <?php
            $discount = 0;

            if ($product['rough_price'] > $product['price']) {
              $discount = round(
                (($product['rough_price'] - $product['price']) / $product['rough_price']) * 100
              );
            }
            ?>

            <div class="col-lg-6">
              <span class="cat text-uppercase small text-muted-soft">
                <?= $product['subcategory_name'] ?>
              </span>
              <h1 class="h3 mt-1"> <?= $product['name'] ?> </h1>
              <div class="rating mb-2"> ★★★★★ <span class="text-muted">(5.0)</span></div>

              <div class="d-flex align-items-center gap-3 mb-3">
                <span id="productPrice" class="price fs-3" data-price-inr="<?= $product['price']; ?>">
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

              <!--<div class="mb-3">-->
              <!--  <label class="fw-semibold d-block mb-2">Available Sizes</label>-->
              <!--  <?php-->
              <!--  if (!empty($product['size'])) {-->

              <!--    $sizes = array_map('trim', explode(',', $product['size']));-->

              <!--    foreach ($sizes as $size) {-->
              <!--      echo '<button type="button" class="btn size-option me-2 mb-2" style="border-radius:0px !important;" data-size="'-->
              <!--        . htmlspecialchars($size) . '">'-->
              <!--        . htmlspecialchars($size) .-->
              <!--        '</button>';-->
              <!--    }-->
              <!--  } else {-->
              <!--    echo '<span class="text-muted">No size available</span>';-->
              <!--  }-->
              <!--  ?>-->
              <!--</div>-->
              <div class="mb-3">
    <label class="fw-semibold d-block mb-2">Available Sizes</label>

    <?php
    if (!empty($product['size'])) {

        $sizes = array_map('trim', explode(',', $product['size']));
        $sizes = array_filter($sizes);

        /*
         * Dynamic size sorting
         *
         * Examples:
         * XS, S, M, L, XL, XXL, 3XL, 4XL, 5XL
         *
         * No hardcoded size list required.
         */
        usort($sizes, function ($a, $b) {

            $a = strtoupper(trim($a));
            $b = strtoupper(trim($b));

            /*
             * Separate numeric part and alphabetic part
             */
            preg_match('/^(\d+)?\s*(.*)$/', $a, $matchA);
            preg_match('/^(\d+)?\s*(.*)$/', $b, $matchB);

            $aNumber = !empty($matchA[1]) ? (int)$matchA[1] : 0;
            $bNumber = !empty($matchB[1]) ? (int)$matchB[1] : 0;

            $aText = $matchA[2] ?? '';
            $bText = $matchB[2] ?? '';

            /*
             * Normal clothing size progression
             *
             * Smaller alphabetic size comes first:
             * XS < S < M < L < XL < XXL
             */
            $sizeValue = function ($text) {

                $text = strtoupper(trim($text));

                $value = 0;

                // Count X's before S/M/L
                $xCount = substr_count($text, 'X');

                if (strpos($text, 'S') !== false) {
                    $value = 100 - ($xCount * 10);
                }
                elseif (strpos($text, 'M') !== false) {
                    $value = 200;
                }
                elseif (strpos($text, 'L') !== false) {
                    $value = 300 + ($xCount * 10);
                }
                else {
                    $value = 400;
                }

                return $value;
            };

            $aValue = $sizeValue($aText);
            $bValue = $sizeValue($bText);

            /*
             * Normal sizes first:
             * XS, S, M, L, XL, XXL
             *
             * Then numbered sizes:
             * 3XL, 4XL, 5XL, 6XL...
             */
            if ($aNumber == 0 && $bNumber > 0) {
                return -1;
            }

            if ($aNumber > 0 && $bNumber == 0) {
                return 1;
            }

            if ($aNumber > 0 && $bNumber > 0) {
                return $aNumber <=> $bNumber;
            }

            return $aValue <=> $bValue;
        });

        foreach ($sizes as $size) {

            echo '<button
                type="button"
                class="btn size-option me-2 mb-2"
                style="border-radius:0px !important;"
                data-size="' . htmlspecialchars($size, ENT_QUOTES, 'UTF-8') . '">'
                . htmlspecialchars($size, ENT_QUOTES, 'UTF-8') .
            '</button>';
        }

    } else {

        echo '<span class="text-muted">No size available</span>';

    }
    ?>
</div>

              <label class="fw-semibold d-block mb-2">Available Colurs</label>
              <?php
              if (!empty($product['color'])) {

                $colors = array_map('trim', explode(',', $product['color']));

                foreach ($colors as $color) {

                  $colorCode = strtolower(trim($color));

                  $colorMap = [
                    "black"      => "#000000",
                    "white"      => "#ffffff",
                    "red"        => "#ff0000",
                    "blue"       => "#0066ff",
                    "green"      => "#28a745",
                    "yellow"     => "#ffd700",
                    "orange"     => "#ff7f00",
                    "pink"       => "#ff69b4",
                    "purple"     => "#800080",
                    "brown"      => "#8b4513",
                    "grey"       => "#808080",
                    "gray"       => "#808080",
                    "navy"       => "#001f54",
                    "maroon"     => "#800000",
                    "olive"      => "#808000",
                    "sky blue"   => "#87ceeb",
                    "beige"      => "#f5f5dc",
                    "cream"      => "#fffdd0",
                    "gold"       => "#d4af37",
                    "silver"     => "#c0c0c0",
                    "multicolor" => "linear-gradient(45deg,#ff0000,#ff9800,#ffeb3b,#4caf50,#2196f3,#9c27b0)"
                  ];

                  $bg = $colorMap[$colorCode] ?? "#cccccc";

                  echo '
        <button
            type="button"
            class="btn color-option me-2 mb-2"
            data-color="' . htmlspecialchars($color) . '"
            style="border:1px solid #7a5c3e;border-radius:10px;">
            <span style="
                display:inline-block;
                width:16px;
                height:16px;
                border-radius:50%;
                border:1px solid #999;
                margin-right:5px;
                vertical-align:middle;
                background:' . $bg . ';
                margin-top: -2px;
            "></span>
            ' . htmlspecialchars($color) . '
        </button>';
                }
              } else {
                echo '<span class="text-muted">No color available</span>';
              }
              ?>
              <style>
                .color-option { background: #fff; color: #7a5c3e; border: 1px solid #7a5c3e !important; transition: .2s; }
                .color-option:hover { background: #f4f2ee; }
                .color-option.active { background: var(--primary) !important; color: #fff !important; }
                .color-option.active span { border-color: #fff; }
              </style>

              <div class="b2c-only d-none mb-3 mt-2"><span class="badge-soft px-2 py-1 rounded">Inclusive of all taxes</span></div>

              <div class="d-flex align-items-center gap-3 my-4">
                <span class="fw-semibold small">Quantity</span>
                <div class="qty-box">
                  <button type="button" id="qtyMinus">−</button>
                  <input type="text" id="qtyValue" value="1" readonly>
                  <button type="button" id="qtyPlus">+</button>
                </div>
              </div>

              <div class="d-flex gap-2 flex-wrap">
                <button type="button" id="addBtn" class="btn btn-brand btn-lg" data-id="<?= $product['id']; ?>">
                  <i class="bi bi-cart-plus"></i> Add to Cart
                </button>

                <button type="button" id="buyNowBtn" class="btn btn-outline-brand btn-lg" data-id="<?= $product['id']; ?>" style="border-radius:0px !important;">
                  Buy Now
                </button>
              </div>

              <p class="text-muted-soft"> <?= $product['about_product'] ?> </p>

              <ul class="list-unstyled small text-muted-soft mt-4">
                <li class="mb-1"><i class="bi bi-truck"></i> Free shipping on prepaid orders above &#8377;999 / equivalent</li>
                <li class="mb-1"><i class="bi bi-arrow-counterclockwise"></i> 7-day easy return policy</li>
                <li class="mb-1"><i class="bi bi-shield-check"></i> 100% authentic, dermatologically tested</li>
              </ul>
            </div>
          </div>
          <!-- END row g-5 (gallery+details) -->

          <!-- YOU MAY ALSO LIKE -->
          <div class="mt-5">
            <h2 class="h5 mb-3">You may also like</h2>
            <?php
            $related = $conn->query("
                SELECT * FROM products
                WHERE subcategory_id='" . $product['subcategory_id'] . "'
                AND id!='" . $product['id'] . "'
                LIMIT 4
            ");
            ?>
            <div class="row g-3">
              <?php while ($r = $related->fetch_assoc()) { ?>
                <div class="col-md-3">
                  <div class="product-card">
                    <a href="product/<?= urlencode($r['name']) ?>">
                      <img src="admin/uploads/<?= $r['image'] ?>" class="img-fluid">
                    </a>
                    <div class="body">
                      <div class="name text-center"> <?= $r['name'] ?> </div>
                      <div class="price text-center"> ₹<?= $r['price'] ?> </div>
                    </div>
                  </div>
                </div>
              <?php } ?>
            </div>
          </div>
          <!-- END YOU MAY ALSO LIKE -->

        </div>
        <!-- END RIGHT SIDE -->

      </div>
      <!-- END outer row -->
    </div>
  </section>

  <style>
    .category-sidebar { background: #fff; border: 1px solid #e5e5e5; padding: 15px; position: sticky; top: 90px; }
    .category-item { display: flex; align-items: center; gap: 12px; text-decoration: none; color: #222; padding: 10px; border-bottom: 1px solid #f1f1f1; transition: .3s; }
    .category-item:last-child { border-bottom: none; }
    .category-item:hover { background: #f8f8f8; color: #000; }
    .category-item img { width: 55px; height: 55px; border-radius: 0px; }
    .category-item span { font-size: 15px; font-weight: 600; }
    @media(max-width:991px) { .category-sidebar { position: relative; top: 0; margin-bottom: 20px; } }
  </style>

  <!-- Fullscreen tap-to-zoom overlay (mobile / touch) -->
  <div class="pdp-lightbox" id="pdpLightbox">
    <button type="button" class="pdp-lightbox-close" id="pdpLightboxClose" aria-label="Close"><i class="bi bi-x-lg"></i></button>
    <img id="pdpLightboxImg" src="" alt="">
  </div>

<style>
.cart-success-toast {
    position: fixed;
    left: 50%;
    bottom: 30px;
    transform: translate(-50%, 30px);

    background: #fff;
    color: #222;

    padding: 14px 22px;
    min-width: 280px;
    max-width: 90%;

    display: flex;
    align-items: center;
    gap: 10px;

    box-shadow: 0 8px 30px rgba(0,0,0,0.18);
    border: 1px solid #eeeeee;

    z-index: 999999;

    opacity: 0;
    visibility: hidden;

    transition: all .35s ease;
}

.cart-success-toast.show {
    opacity: 1;
    visibility: visible;
    transform: translate(-50%, 0);
}

.cart-success-toast i {
    font-size: 22px;
    color: #198754;
}

.cart-success-toast strong {
    font-size: 15px;
}

.cart-success-toast span {
    display: block;
    font-size: 13px;
    color: #777;
    margin-top: 2px;
}

@media (max-width: 576px) {
    .cart-success-toast {
        bottom: 20px;
        min-width: 90%;
        padding: 13px 16px;
    }
}
</style>

<div id="cartSuccessToast" class="cart-success-toast">
    <i class="bi bi-check-circle-fill"></i>

    <div>
        <strong>Added to Cart</strong>
        <span>Product has been added successfully.</span>
    </div>
</div>


  <!-- ============ FOOTER ============ -->
  <?php include 'footer.php'; ?>


 
<script src="/js/wishlist.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <script>
    document.addEventListener("DOMContentLoaded", function() {

      const sizeButtons = document.querySelectorAll(".size-option");
      const colorButtons = document.querySelectorAll(".color-option");
      const qtyInput = document.getElementById("qtyValue");
      const minus = document.getElementById("qtyMinus");
      const plus = document.getElementById("qtyPlus");

      // ====================== DETECT RETURN-FROM-LOGIN ======================
      // Only trust sessionStorage "pending_*" values when this page load is the
      // redirect target that was stored right before sending the user to login.
      // Otherwise a stale value from a previous product page would leak in.
      const cameFromLogin = sessionStorage.getItem("redirect_after_login") === window.location.href;

      let selectedSize = "";
      let selectedColor = "";
      let pendingVariantId = "0";
      let qty = 1;

      if (cameFromLogin) {
        selectedSize = sessionStorage.getItem("pending_product_size") || "";
        selectedColor = sessionStorage.getItem("pending_product_color") || "";
        pendingVariantId = sessionStorage.getItem("pending_product_variant") || "0";
        qty = parseInt(sessionStorage.getItem("pending_product_qty") || "1", 10);

        // Consume the flags so they don't re-apply on a later visit / other product
        sessionStorage.removeItem("redirect_after_login");
        sessionStorage.removeItem("pending_product_size");
        sessionStorage.removeItem("pending_product_color");
        sessionStorage.removeItem("pending_product_qty");
        sessionStorage.removeItem("pending_product_variant");
      }

      if (!qty || qty < 1) qty = 1;
      qtyInput.value = qty;

      minus.addEventListener("click", () => {
        if (qty > 1) qty--;
        qtyInput.value = qty;
      });

      plus.addEventListener("click", () => {
        qty++;
        qtyInput.value = qty;
      });

      // ====================== SIZE SELECTION ======================
      sizeButtons.forEach(btn => {
        btn.addEventListener("click", function() {
          sizeButtons.forEach(b => b.classList.remove("active"));
          this.classList.add("active");
          selectedSize = this.dataset.size;
        });
      });

      // ====================== COLOR SELECTION ======================
      colorButtons.forEach(btn => {
        btn.addEventListener("click", function() {
          colorButtons.forEach(b => b.classList.remove("active"));
          this.classList.add("active");
          selectedColor = this.dataset.color;
        });
      });

      // ====================== APPLY SIZE (restored, else default first) ======================
      if (sizeButtons.length > 0) {
        let sizeBtnToSelect = null;

        if (selectedSize) {
          sizeBtnToSelect = Array.from(sizeButtons).find(b => b.dataset.size === selectedSize);
        }

        // Fall back to the first size if nothing was restored or the restored
        // value no longer matches an available size.
        if (!sizeBtnToSelect) {
          sizeBtnToSelect = sizeButtons[0];
        }

        sizeButtons.forEach(b => b.classList.remove("active"));
        sizeBtnToSelect.classList.add("active");
        selectedSize = sizeBtnToSelect.dataset.size;
      }

      // ====================== APPLY COLOR (restored, else default first) ======================
      if (colorButtons.length > 0) {
        let colorBtnToSelect = null;

        if (selectedColor) {
          colorBtnToSelect = Array.from(colorButtons).find(b => b.dataset.color === selectedColor);
        }

        if (!colorBtnToSelect) {
          colorBtnToSelect = colorButtons[0];
        }

        colorButtons.forEach(b => b.classList.remove("active"));
        colorBtnToSelect.classList.add("active");
        selectedColor = colorBtnToSelect.dataset.color;
      }

      // Returns true if it's OK to proceed (either a size was selected,
      // or the product has no sizes to choose from at all).
      function sizeRequiredCheck() {
        if (sizeButtons.length === 0) return true;

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

      function colorRequiredCheck() {
        if (colorButtons.length === 0) return true;

        if (!selectedColor) {
          Swal.fire({
            icon: "warning",
            title: "Select Color",
            text: "Please select a color before continuing.",
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

        priceEl.setAttribute("data-price-inr", price);
        priceEl.innerHTML = "₹" + price.toLocaleString('en-IN');

        if (typeof NEH !== "undefined" && typeof NEH.updatePriceEls === "function") {
          NEH.updatePriceEls();
        }

        if (oldPriceEl) {
          if (oldPrice > price) {
            oldPriceEl.style.display = "";
            oldPriceEl.setAttribute("data-price-inr", oldPrice);
            oldPriceEl.innerHTML = "₹" + oldPrice.toLocaleString('en-IN');
          } else {
            oldPriceEl.style.display = "none";
          }
        }

        if (discountEl) {
          if (oldPrice > price) {
            let discount = Math.round(((oldPrice - price) / oldPrice) * 100);
            discountEl.style.display = "";
            discountEl.innerHTML = discount + "% OFF";
          } else {
            discountEl.style.display = "none";
          }
        }

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

        document.querySelectorAll(".pdp-thumb").forEach(x => x.classList.remove("active"));
        btn.classList.add("active");

        currentVariantId = btn.dataset.variantId || "0";

        updatePrice(btn.dataset.price, btn.dataset.oldPrice);
      }

      thumbsContainer.querySelectorAll('.pdp-thumb').forEach(btn => {
        btn.addEventListener('click', () => {
          switchImage(btn.dataset.src, btn);
        });
      });

      // Restore the variant/image the customer had picked before login
      if (cameFromLogin && pendingVariantId && pendingVariantId !== "0") {
        const variantBtn = thumbsContainer.querySelector('.pdp-thumb[data-variant-id="' + pendingVariantId + '"]');
        if (variantBtn) {
          switchImage(variantBtn.dataset.src, variantBtn);
        }
      }

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

    //   function addCart(productId, qty, buyNow = false) {
    //     fetch("add-to-cart.php", {
    //         method: "POST",
    //         headers: {
    //           "Content-Type": "application/x-www-form-urlencoded"
    //         },
    //         body: "product_id=" + productId +
    //           "&qty=" + qty +
    //           "&variant_id=" + currentVariantId +
    //           "&size=" + encodeURIComponent(selectedSize) +
    //           "&color=" + encodeURIComponent(selectedColor)
    //       })
    //       .then(res => res.text())
    //       .then(function(data) {
    //         data = data.trim();

    //         if (data == "login") {
    //           // Save current product page as the redirect target
    //           sessionStorage.setItem("redirect_after_login", window.location.href);

    //           // Save selected size / color / qty / variant so they can be
    //           // restored automatically once the user is back on this page
    //           sessionStorage.setItem("pending_product_size", selectedSize);
    //           sessionStorage.setItem("pending_product_color", selectedColor);
    //           sessionStorage.setItem("pending_product_qty", qty);
    //           sessionStorage.setItem("pending_product_variant", currentVariantId);

    //           window.location.href = "login";
    //           return;
    //         }

    //         if (data == "success") {
    //           if (typeof loadCartCount === "function") {
    //             loadCartCount();
    //           }

    //           if (buyNow) {
    //             window.location = "cart";
    //           } else {
    //             addBtn.innerHTML = '<i class="bi bi-check2"></i> Added';
    //             setTimeout(function() {
    //               addBtn.innerHTML = '<i class="bi bi-cart-plus"></i> Add to Cart';
    //             }, 1500);
    //           }
    //         }
    //       });
    //   }
    
     
function addCart(productId, qty, buyNow = false) {

    fetch("add-to-cart.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded"
        },
        body: "product_id=" + encodeURIComponent(productId) +
              "&qty=" + encodeURIComponent(qty) +
              "&variant_id=" + encodeURIComponent(currentVariantId) +
              "&size=" + encodeURIComponent(selectedSize) +
              "&color=" + encodeURIComponent(selectedColor)
    })

    .then(res => res.text())

    .then(function(data) {

        data = data.trim();

        console.log("Add Cart Response:", data);

        /* =========================
           LOGIN REQUIRED
        ========================= */
        if (data == "login") {

            sessionStorage.setItem(
                "redirect_after_login",
                window.location.href
            );

            sessionStorage.setItem(
                "pending_product_size",
                selectedSize
            );

            sessionStorage.setItem(
                "pending_product_color",
                selectedColor
            );

            sessionStorage.setItem(
                "pending_product_qty",
                qty
            );

            sessionStorage.setItem(
                "pending_product_variant",
                currentVariantId
            );

            window.location.href = "login";
            return;
        }


        /* =========================
           ADD TO CART SUCCESS
        ========================= */
        if (data == "success") {

            // Update cart count
            if (typeof loadCartCount === "function") {
                loadCartCount();
            }


            /* =========================
               BUY NOW
            ========================= */
            if (buyNow) {

                window.location.href = "cart";
                return;
            }


            /* =========================
               NORMAL ADD TO CART
            ========================= */

            // Change button
            addBtn.innerHTML =
                '<i class="bi bi-check2"></i> Added';


            // Get popup
            const toast =
                document.getElementById("cartSuccessToast");


            // Show popup
            if (toast) {

                toast.classList.add("show");

                console.log("Cart success popup shown");

                // Hide after 3 seconds
                setTimeout(function() {

                    toast.classList.remove("show");

                }, 3000);
            }


            // Reset button after 1.5 seconds
            setTimeout(function() {

                addBtn.innerHTML =
                    '<i class="bi bi-cart-plus"></i> Add to Cart';

            }, 2000);

        }

        /* =========================
           ERROR
        ========================= */
        else {

            Swal.fire({
                icon: "error",
                title: "Something went wrong",
                text: data || "Unable to add product to cart.",
                confirmButtonText: "OK",
                confirmButtonColor: "#000"
            });

        }

    })

    .catch(function(error) {

        console.error("Add to Cart Error:", error);

        Swal.fire({
            icon: "error",
            title: "Error",
            text: "Unable to add product to cart. Please try again.",
            confirmButtonText: "OK",
            confirmButtonColor: "#000"
        });

    });
}
 


      addBtn.addEventListener("click", function() {
        if (!sizeRequiredCheck()) return;
        if (!colorRequiredCheck()) return;
        addCart(this.dataset.id, qty, false);
      });

      buyBtn.addEventListener("click", function() {
        if (!sizeRequiredCheck()) return;
        if (!colorRequiredCheck()) return;
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