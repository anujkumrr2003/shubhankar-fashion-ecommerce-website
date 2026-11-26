<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

?>
<?php
include 'config.php';

// $category = isset($_GET['category']) ? urldecode($_GET['category']) : '';
$category = '';

if (isset($_GET['category'])) {
    $category = urldecode($_GET['category']);
    $category = str_replace('-', ' ', $category);
}

$catResult = $conn->query("
SELECT id, subcategory_name
FROM subcategories
WHERE status='Active'
ORDER BY subcategory_name ASC
");

// ---- Sizes for the Size Filter (read from product_variants.variant_name) ----
$sizeOrder = ['XS' => 0, 'S' => 1, 'M' => 2, 'L' => 3, 'XL' => 4, 'XXL' => 5, 'XXXL' => 6, '3XL' => 6, '4XL' => 7];

$allSizes = [];
$sizeResult = $conn->query("
SELECT DISTINCT variant_name
FROM product_variants
WHERE variant_name IS NOT NULL AND variant_name != ''
");

if ($sizeResult) {
    while ($sizeRow = $sizeResult->fetch_assoc()) {
        $allSizes[] = $sizeRow['variant_name'];
    }
}

usort($allSizes, function ($a, $b) use ($sizeOrder) {
    $ai = array_key_exists(strtoupper($a), $sizeOrder) ? $sizeOrder[strtoupper($a)] : 999;
    $bi = array_key_exists(strtoupper($b), $sizeOrder) ? $sizeOrder[strtoupper($b)] : 999;
    if ($ai === $bi) {
        return strcmp($a, $b);
    }
    return $ai <=> $bi;
});

// ---- Products, joined with subcategory + a comma list of available sizes ----
if (!empty($category)) {

    $category = $conn->real_escape_string($category);

    $sql = "
    SELECT p.*, s.subcategory_name,
           GROUP_CONCAT(DISTINCT pv.variant_name ORDER BY pv.variant_name SEPARATOR ',') AS sizes
    FROM products p
    LEFT JOIN subcategories s ON p.subcategory_id = s.id
    LEFT JOIN product_variants pv ON pv.product_id = p.id
    WHERE s.subcategory_name='$category'
    GROUP BY p.id
    ORDER BY p.id DESC
    ";

} else {

    $sql = "
    SELECT p.*, s.subcategory_name,
           GROUP_CONCAT(DISTINCT pv.variant_name ORDER BY pv.variant_name SEPARATOR ',') AS sizes
    FROM products p
    LEFT JOIN subcategories s ON p.subcategory_id = s.id
    LEFT JOIN product_variants pv ON pv.product_id = p.id
    GROUP BY p.id
    ORDER BY p.id DESC
    ";

}

$productResult = $conn->query($sql);

?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
 <title>Shop Men's Fashion Online in India | Shubhankar India</title>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<meta name="title" content="Shop Men's Fashion Online in India | Shubhankar India">

<meta name="description" content="Shop premium men's fashion online in India at Shubhankar. Explore jeans, t-shirts, shirts, blazers, trousers and more with secure payments, fast shipping and PAN India delivery.">

<meta name="keywords" content="Buy Men's Clothing Online India, Men's Fashion India, Men's Jeans India, Men's Shirts India, Men's T Shirts India, Men's Blazers India, Men's Trousers India, Online Clothing Store India, Men's Wear India, Shubhankar India">

<meta name="author" content="Shubhankar">
<meta name="publisher" content="Shubhankar">

<meta name="robots" content="index,follow,max-image-preview:large,max-snippet:-1,max-video-preview:-1">

<link rel="canonical" href="https://shubhankarfashion.com/in/shop">

<link rel="alternate" hreflang="en-IN" href="https://shubhankarfashion.com/in/shop">
<link rel="alternate" hreflang="x-default" href="https://shubhankarfashion.com/in/shop">

<!-- Open Graph -->
<meta property="og:type" content="website">
<meta property="og:site_name" content="Shubhankar India">
<meta property="og:title" content="Shop Men's Fashion Online in India | Shubhankar India">
<meta property="og:description" content="Discover premium men's clothing in India. Shop jeans, shirts, t-shirts, blazers and more with PAN India delivery.">
<meta property="og:url" content="https://shubhankarfashion.com/in/shop">
<meta property="og:image" content="https://shubhankarfashion.com/img/logo-removebg-preview.png">
<meta property="og:image:secure_url" content="https://shubhankarfashion.com/img/logo-removebg-preview.png">
<meta property="og:image:type" content="image/png">
<meta property="og:image:alt" content="Shubhankar India Logo">
<meta property="og:locale" content="en_IN">

<!-- Twitter -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="Shop Men's Fashion Online in India | Shubhankar India">
<meta name="twitter:description" content="Buy premium men's clothing online in India from Shubhankar.">
<meta name="twitter:image" content="https://shubhankarfashion.com/img/logo-removebg-preview.png">

<!-- Favicon -->
<link rel="icon" type="image/png" href="https://shubhankarfashion.com/img/logo-removebg-preview.png">
<link rel="shortcut icon" href="https://shubhankarfashion.com/img/logo-removebg-preview.png">
<link rel="apple-touch-icon" href="https://shubhankarfashion.com/img/logo-removebg-preview.png">

<meta name="theme-color" content="#000000">
<meta name="application-name" content="Shubhankar India">
<meta name="apple-mobile-web-app-title" content="Shubhankar India">
<meta name="format-detection" content="telephone=no">

<meta name="geo.region" content="IN">
<meta name="geo.country" content="India">
<meta name="ICBM" content="20.5937,78.9629">
<script type="application/ld+json">
{
  "@context":"https://schema.org",
  "@type":"CollectionPage",
  "name":"Shop Men's Fashion Online in India",
  "url":"https://shubhankarfashion.com/in/shop",
  "description":"Browse premium men's fashion products including jeans, shirts, t-shirts, blazers and trousers available for delivery across India.",
  "isPartOf":{
    "@type":"WebSite",
    "name":"Shubhankar India",
    "url":"https://shubhankarfashion.com/in/"
  }
}
</script>


<script type="application/ld+json">
{
  "@context":"https://schema.org",
  "@type":"BreadcrumbList",
  "itemListElement":[
    {
      "@type":"ListItem",
      "position":1,
      "name":"Home",
      "item":"https://shubhankarfashion.com/in/"
    },
    {
      "@type":"ListItem",
      "position":2,
      "name":"Shop",
      "item":"https://shubhankarfashion.com/in/shop"
    }
  ]
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
   <base href="https://shubhankarfashion.com/in/">
  <link rel="stylesheet" href="style.css">

  <style>
    /* --- Wishlist heart --- */
    .thumb { position: relative; }
    .wishlist-btn {
      position: absolute;
      top: 8px;
      right: 8px;
      z-index: 5;
      width: 34px;
      height: 34px;
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
    .wishlist-btn i { font-size: 1.05rem; color: #6c757d; }
    .wishlist-btn.is-active i { color: #dc3545; }

    /* --- Size chart --- */
    .size-chart-table th, .size-chart-table td {
      text-align: center;
      vertical-align: middle;
    }

    /* --- Size filter chips --- */
    .size-btn { min-width: 42px; }

    /* --- Search --- */
    #shopSearch { max-width: 100%; }
  </style>

</head>

<body>

  <?php include 'header.php'; ?>

  <section class="section-sm bg-surface-2 border-bottom">
    <div class="container">
      <nav class="small text-muted-soft mb-2"><a href="index.php">Home</a> / <span>Shop</span></nav>
      <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <h1 class="h3 mb-0">
          <?php
          if (!empty($category)) {
              echo htmlspecialchars($category);
          } else {
              echo "Shop All Products";
          }
          ?>
        </h1>

        <div class="input-group" style="max-width:320px;">
          <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
          <input type="text" id="shopSearch" class="form-control" placeholder="Search products or category...">
        </div>
      </div>
    </div>
  </section>

  <section class="section">
    <div class="container">
      <div class="row g-4">

        <!-- Filters -->
        <div class="col-lg-3">
          <div class="card-panel">
            <h2 class="h6 mb-3">Categories</h2>
            <div class="d-flex flex-wrap gap-2 mb-4">

              <button class="btn btn-sm btn-dark filter-btn active"
                data-id="all">
                All
              </button>

              <?php while ($cat = $catResult->fetch_assoc()) { ?>

                <button class="btn btn-sm btn-outline-dark filter-btn"
                  data-id="<?= $cat['id']; ?>">
                  <?= htmlspecialchars($cat['subcategory_name']); ?>
                </button>

              <?php } ?>

            </div>

            <!-- Size Chart -->
            <h2 class="h6 mb-3">Size Chart</h2>
            <div class="table-responsive mb-4">
              <table class="table table-sm table-bordered size-chart-table mb-0">
                <thead class="table-light">
                  <tr>
                    <th>Size</th>
                    <th>Chest (in)</th>
                    <th>Shoulder (in)</th>
                    <th>Length (in)</th>
                  </tr>
                </thead>
                <tbody>
                  <tr><td>S</td><td>38</td><td>17</td><td>27</td></tr>
                  <tr><td>M</td><td>40</td><td>17.5</td><td>28</td></tr>
                  <tr><td>L</td><td>42</td><td>18</td><td>29</td></tr>
                  <tr><td>XL</td><td>44</td><td>18.5</td><td>30</td></tr>
                  <tr><td>XXL</td><td>46</td><td>19</td><td>31</td></tr>
                </tbody>
              </table>
              <!--<p class="small text-muted-soft mt-1 mb-0">Measurements are approximate; check individual product pages for exact fit.</p>-->
            </div>

            <!-- Size Filter -->
            <?php if (!empty($allSizes)) { ?>
              <h2 class="h6 mb-3">Size</h2>
              <div class="d-flex flex-wrap gap-2 mb-4" id="sizeFilterGroup">
                <button class="btn btn-sm btn-outline-dark size-btn active" data-size="all">All</button>
                <?php foreach ($allSizes as $sizeName) { ?>
                  <button class="btn btn-sm btn-outline-dark size-btn" data-size="<?= htmlspecialchars($sizeName) ?>">
                    <?= htmlspecialchars($sizeName) ?>
                  </button>
                <?php } ?>
              </div>
            <?php } ?>

            <h2 class="h6 mb-3">Sort by</h2>
            <select class="form-select form-select-sm mb-4" id="sortSelect">
              <option value="default">Featured</option>
              <option value="priceLow">Price: Low to High</option>
              <option value="priceHigh">Price: High to Low</option>
              <option value="rating">Top Rated</option>
            </select>

            <a href="wishlist" class="btn btn-outline-dark btn-sm w-100 mb-2 text-white" style="background: #00005f;  ">
              <i class="bi bi-heart"></i> My Wishlist
              <span class="badge bg-white js-wishlist-count d-none text-black">0</span>
            </a>

            <div class="b2b-only d-none">
              <div class="b2b-note d-block mb-2"><i class="bi bi-info-circle"></i> B2B mode: prices shown exclude GST. Bulk discounts apply at checkout for 10+ units.</div>
            </div>
          </div>
        </div>

        <!-- Grid -->
        <div class="col-lg-9">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <span class="small text-muted-soft" id="resultCount"></span>
          </div>

          <div class="row g-3" id="shopGrid">

            <?php while ($row = $productResult->fetch_assoc()) {
                $sizesAttr = isset($row['sizes']) ? $row['sizes'] : '';
                $productUrl = "b2b-product/" . urlencode($row['name']);
                $imageUrlAbs = "https://shubhankarfashion.com/admin/uploads/" . $row['image'];
                $productUrlAbs = "https://shubhankarfashion.com/in/b2b-product/" . urlencode($row['name']);
            ?>

              <div class="col-6 col-md-4 col-lg-4 product-item"
                data-cat="<?= $row['subcategory_id']; ?>"
                data-price="<?= $row['price']; ?>"
                data-name="<?= htmlspecialchars(strtolower($row['name'])); ?>"
                data-catname="<?= htmlspecialchars(strtolower($row['subcategory_name'] ?? '')); ?>"
                data-sizes="<?= htmlspecialchars(strtoupper($sizesAttr)); ?>">

                <div class="product-card">

                  <a href="<?= $productUrl ?>">

                    <div class="thumb">

                      <button
                        type="button"
                        class="wishlist-btn js-wishlist-toggle"
                        data-id="<?= $row['id'] ?>"
                        data-name="<?= htmlspecialchars($row['name']) ?>"
                        data-price="<?= $row['price'] ?>"
                        data-rough-price="<?= $row['rough_price'] ?>"
                        data-image="<?= $imageUrlAbs ?>"
                        data-url="<?= $productUrlAbs ?>"
                        data-sizes="<?= htmlspecialchars(strtoupper($sizesAttr)) ?>"
                        aria-label="Add to wishlist">
                        <i class="bi bi-heart"></i>
                      </button>

                      <img src="../admin/uploads/<?= $row['image']; ?>"
                        class="img-fluid product-img"
                        alt="<?= htmlspecialchars($row['name']); ?>">
                    </div>

                  </a>

                     <div class="body">

                <?php if ($row['trending_type'] != "") { ?>
                  <span class="badge badge-soft mb-2 d-none">
                    <?= $row['trending_type'] ?>
                  </span>
                <?php } ?>

                <div class="cat text-uppercase d-none">
                  <?= $row['subcategory_name'] ?>
                </div>

                <a href="b2b-product/<?= urlencode($row['name']) ?>" class="text-decoration-none text-center">
                  <div class="name">
                    <?= $row['name'] ?>
                  </div>
                </a>

                <?php if (!empty($sizesAttr)) { ?>
                  <div class="small text-muted-soft mb-2">
                    Sizes: <?= htmlspecialchars(str_replace(',', ', ', strtoupper($sizesAttr))) ?>
                  </div>
                <?php } ?>

                <div class="rating small mb-2 d-none">
                  ★★★★★
                  <span class="text-muted">(5.0)</span>
                </div>

                <div class="d-flex align-items-center gap-2">

                  <!--<span class="price" data-price-inr="<?= $row['price'] ?>" style="margin:auto;">-->
                  <!--  ₹<?= $row['price'] ?>-->
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

              </div>

            <?php } ?>

          </div>

          <div id="noResults" class="text-center text-muted-soft py-5 d-none">
            <i class="bi bi-emoji-frown fs-2 d-block mb-2"></i>
            No products match your filters.
          </div>

        </div>
      </div>
    </div>
  </section>

  <!-- ============ FOOTER ============ -->
  <?php include 'footer.php'; ?>

  <script src="wishlist.js"></script>

  <script>
    document.addEventListener("DOMContentLoaded", function() {

      const filterBtns = document.querySelectorAll(".filter-btn");
      const sizeBtns = document.querySelectorAll(".size-btn");
      const products = document.querySelectorAll(".product-item");
      const resultCount = document.getElementById("resultCount");
      const noResults = document.getElementById("noResults");
      const sortSelect = document.getElementById("sortSelect");
      const searchInput = document.getElementById("shopSearch");

      let activeCat = "all";
      let activeSize = "all";
      let activeSearch = "";

      function applyFilters() {
        let visible = 0;

        products.forEach(function(item) {
          const matchesCat = (activeCat === "all" || item.dataset.cat === activeCat);

          const sizes = (item.dataset.sizes || "").split(",").map(s => s.trim()).filter(Boolean);
          const matchesSize = (activeSize === "all" || sizes.includes(activeSize));

          const haystack = (item.dataset.name || "") + " " + (item.dataset.catname || "");
          const matchesSearch = (activeSearch === "" || haystack.includes(activeSearch));

          const show = matchesCat && matchesSize && matchesSearch;
          item.style.display = show ? "" : "none";

          if (show) visible++;
        });

        resultCount.innerHTML = visible + " Products";
        noResults.classList.toggle("d-none", visible !== 0);
      }

      // Category Filter
      filterBtns.forEach(function(btn) {
        btn.addEventListener("click", function() {

          filterBtns.forEach(function(b) {
            b.classList.remove("active", "btn-dark");
            b.classList.add("btn-outline-dark");
          });

          this.classList.add("active", "btn-dark");
          this.classList.remove("btn-outline-dark");

          activeCat = this.dataset.id;
          applyFilters();
        });
      });

      // Size Filter
      sizeBtns.forEach(function(btn) {
        btn.addEventListener("click", function() {

          sizeBtns.forEach(function(b) {
            b.classList.remove("active", "btn-dark");
            b.classList.add("btn-outline-dark");
          });

          this.classList.add("active", "btn-dark");
          this.classList.remove("btn-outline-dark");

          activeSize = this.dataset.size;
          applyFilters();
        });
      });

      // Search
      let searchTimer = null;
      searchInput.addEventListener("input", function() {
        clearTimeout(searchTimer);
        const val = this.value;
        searchTimer = setTimeout(function() {
          activeSearch = val.trim().toLowerCase();
          applyFilters();
        }, 150);
      });

      // Sorting
      sortSelect.addEventListener("change", function() {

        let cards = Array.from(products);
        let grid = document.getElementById("shopGrid");

        if (this.value === "priceLow") {

          cards.sort(function(a, b) {
            return parseFloat(a.dataset.price) - parseFloat(b.dataset.price);
          });

        } else if (this.value === "priceHigh") {

          cards.sort(function(a, b) {
            return parseFloat(b.dataset.price) - parseFloat(a.dataset.price);
          });

        }

        cards.forEach(function(card) {
          grid.appendChild(card);
        });

      });

      applyFilters();

      if (typeof NEH !== "undefined") {
        NEH.updatePriceEls();
      }

    });
  </script>

</body>

</html>