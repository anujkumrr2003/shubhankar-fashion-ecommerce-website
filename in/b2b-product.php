<?php

session_start();
include "config.php";

/*
|--------------------------------------------------------------------------
| ERROR REPORTING
|--------------------------------------------------------------------------
| Testing ke time errors ON rakhe hain.
| Sab kuch working hone ke baad error_reporting(0) kar sakte ho.
*/
error_reporting(E_ALL);
ini_set('display_errors', 1);


/*
|--------------------------------------------------------------------------
| PRODUCT CHECK
|--------------------------------------------------------------------------
*/
if (!isset($_GET['product']) || empty($_GET['product'])) {
    header("Location: shop");
    exit;
}


/*
|--------------------------------------------------------------------------
| PRODUCT NAME
|--------------------------------------------------------------------------
*/
$product_name = urldecode($_GET['product']);
$product_name = trim($product_name);
$product_name = mysqli_real_escape_string($conn, $product_name);


/*
|--------------------------------------------------------------------------
| PRODUCT DETAILS
|--------------------------------------------------------------------------
| LEFT JOIN ki wajah se subcategory_id NULL hone par bhi
| product properly milega.
|--------------------------------------------------------------------------
*/

$productQuery = mysqli_query($conn, "
    SELECT
        p.*,
        s.subcategory_name
    FROM products p
    LEFT JOIN subcategories s
        ON p.subcategory_id = s.id
    WHERE p.name = '$product_name'
    LIMIT 1
");

if (!$productQuery) {
    die("Product Query Error: " . mysqli_error($conn));
}

if (mysqli_num_rows($productQuery) == 0) {
    die("Product not found.");
}

$product = mysqli_fetch_assoc($productQuery);


/*
|--------------------------------------------------------------------------
| PRODUCT VARIANTS
|--------------------------------------------------------------------------
*/

$product_id = (int)$product['id'];

/* IMPORTANT: Always initialize array */
$variants = [];

$variantQuery = mysqli_query($conn, "
    SELECT *
    FROM product_variants
    WHERE product_id = '$product_id'
    ORDER BY id ASC
");

if ($variantQuery) {

    while ($row = mysqli_fetch_assoc($variantQuery)) {
        $variants[] = $row;
    }

}

?>





<!DOCTYPE html>
<html>

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

        <base href="https://shubhankarfashion.com/in/">
    <link rel="stylesheet" href="style.css">


    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flag-icons/css/flag-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

<link rel="icon" type="image/png" href="https://shubhankarfashion.com/img/logo-removebg-preview.png">

    <style>
    
    
    .thumb { 
        position: relative; 
        
    width: 100%;
    aspect-ratio: 1 / 1;
    background: var(--surface-2, #f4f2ee);
    border: 1px solid var(--border, #e7e2da);
    overflow: hidden;
    cursor: zoom-in;
    display: flex;
    align-items: center;
    justify-content: center;
    }
.wishlist-btn 
{
    position: absolute;
    top: 10px;
    right: 10px;
    z-index: 5;
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



        .product-image {
            height: auto;
            object-fit: cover;
            width: 100%;
            border: 1px solid #ddd;
            /*border-radius: 10px;*/
        }

        .variant-img {
            width: 70px;
            height: 70px;
            object-fit: cover;
            cursor: pointer;
            border: 2px solid #ddd;
            /*border-radius: 8px;*/
            margin: 5px;
        }

        .variant-img:hover {
            border-color: #000;
        }
    </style>

</head>

<body>
    <?php include "header.php"; ?>


    <?php //include 'cat.php'; ?>


<?php
// include 'config.php';

// $categories = $conn->query("
//     SELECT id, category_id, subcategory_name, image
//     FROM subcategories
//     WHERE status='Active'
//     ORDER BY subcategory_name ASC
//     LIMIT 10
// ");
?>

<section class="section-sm">
  <div class="container">
    <div class="row g-4">

      <!-- LEFT SIDEBAR: CATEGORIES -->
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

            //   $image = (!empty($cat['image']) && file_exists("/admin/uploads/" . $cat['image']))
            //     ? "https://shubhankarfashion.com/admin/uploads/" . $cat['image']
            //     : "https://shubhankarfashion.com/admin/uploads/no-image.png";
            ?>
            
            
             <?php

        $serverPath = dirname(__DIR__) . "/admin/uploads/" . $cat['image'];

        if (!empty($cat['image']) && file_exists($serverPath)) {

          $image = "https://shubhankarfashion.com/admin/uploads/" . $cat['image'];
        } else {

          $image = "https://shubhankarfashion.com/admin/uploads/no-image.png";
        }

        ?>

              <a href="shop.php?category=<?= urlencode($cat['subcategory_name']) ?>"
                class="category-item">

                <!--<img src="<?= $image ?>" alt="<?= htmlspecialchars($cat['subcategory_name']) ?>">-->

                <span><?= htmlspecialchars($cat['subcategory_name']) ?></span>

              </a>

            <?php } ?>

          </div>

        </div>
        <!-- END LEFT SIDEBAR -->
      <!-- END LEFT SIDEBAR -->

<style>
    .category-sidebar {
      background: #fff;
      border: 1px solid #e5e5e5;
      /*border-radius: 10px;*/
      padding: 15px;
      position: sticky;
      top: 90px;
    }

    .category-item {
      display: flex;
      align-items: center;
      gap: 12px;
      text-decoration: none;
      color: #222;
      padding: 10px;
      border-bottom: 1px solid #f1f1f1;
      transition: .3s;
    }

    .category-item:last-child {
      border-bottom: none;
    }

    .category-item:hover {
      background: #f8f8f8;
      color: #000;
    }

    .category-item img {
      width: 55px;
      height: 55px;
      /*object-fit: cover;*/
      /*border-radius: 8px;*/
    }

    .category-item span {
      font-size: 15px;
      font-weight: 600;
    }

    @media(max-width:991px) {

      .category-sidebar {
        position: relative;
        top: 0;
        margin-bottom: 20px;
      }

    }
</style>

      <!-- RIGHT SIDE: PRODUCT GALLERY + DETAILS -->
      <div class="col-lg-9">

        <div class="row">

            <div class="col-md-6">


                <!--<img-->
                <!--    id="mainImage"-->
                <!--    src="../admin/uploads/<?php echo $product['image']; ?>"-->
                <!--    class="product-image">-->
                
                <div class="thumb">

    <img
        id="mainImage"
        src="../admin/uploads/<?php echo $product['image']; ?>"
        class="product-image">

    <?php
    $wishlistSizes = strtoupper(implode(',', array_filter(array_map('trim', explode(',', $product['size'])))));
    ?>

    <button
        type="button"
        class="wishlist-btn js-wishlist-toggle"
        data-id="<?= $product['id'] ?>"
        data-name="<?= htmlspecialchars($product['name']) ?>"
        data-price="<?= $product['price'] ?>"
        data-rough-price="<?= $product['rough_price'] ?>"
        data-image="https://shubhankarfashion.com/admin/uploads/<?= htmlspecialchars($product['image']) ?>"
        data-url="https://shubhankarfashion.com/in/b2b-product?product=<?= urlencode($product['name']) ?>"
        data-sizes="<?= htmlspecialchars($wishlistSizes) ?>"
        aria-label="Add to wishlist">
        <i class="bi bi-heart"></i>
    </button>

</div>

                <?php if (count($variants) > 0) { ?>

                    <!--<div class="mt-3 d-flex flex-wrap gap-2" >-->
                     <div class="mt-3 variant-scroll-wrap">

                        <?php foreach ($variants as $variant) { 

    /*
    |--------------------------------------------------------------------------
    | VARIANT IMAGES
    |--------------------------------------------------------------------------
    | Admin se agar images is type mein stored hain:
    |
    | image1.jpg,image2.jpg,image3.jpg,image4.jpg
    |
    | to sabhi images alag-alag show hongi.
    |--------------------------------------------------------------------------
    */

    $variantImages = [];

    if (!empty($variant['images'])) {

        $variantImages = explode(',', $variant['images']);

    }

    /*
    |--------------------------------------------------------------------------
    | Agar variant mein image nahi hai
    |--------------------------------------------------------------------------
    */

    if (empty($variantImages)) {

        $variantImages[] = $product['image'];

    }

    /*
    |--------------------------------------------------------------------------
    | Har image show karo
    |--------------------------------------------------------------------------
    */

    foreach ($variantImages as $variantImage) {

        $variantImage = trim($variantImage);

        if ($variantImage == '') {
            continue;
        }

?>

        <div class="variant-item">

            <img
                class="variant-img"
                src="../admin/img/<?= htmlspecialchars($variantImage) ?>"
                data-image="../admin/img/<?= htmlspecialchars($variantImage) ?>"
                data-price="<?= htmlspecialchars($variant['variant_price']) ?>"
                data-rough-price="<?= htmlspecialchars($product['rough_price']) ?>"
                data-variant-id="<?= htmlspecialchars($variant['id']) ?>"
                alt="<?= htmlspecialchars($product['name']) ?>"
            >

        </div>

<?php

    }

}
?>

<style>
/*    .variant-img {*/
/*    width: 70px;*/
/*    height: 70px;*/
/*    object-fit: cover;*/
/*    cursor: pointer;*/
/*    border: 2px solid #ddd;*/
/*    margin: 5px;*/
/*}*/

.variant-scroll-wrap {
    display: flex;
    flex-wrap: nowrap;      /* wrap band, ek line mein rahega */
    overflow-x: auto;       /* extra images ke liye horizontal scroll */
    gap: 8px;                /* single, controlled gap */
    padding-bottom: 8px;    /* scrollbar ke liye thodi jagah */
    -webkit-overflow-scrolling: touch;
}

.variant-item {
    flex: 0 0 auto;          /* shrink na ho scroll mein */
}

.variant-img {
    width: 70px;
    height: 70px;
    object-fit: cover;
    cursor: pointer;
    border: 2px solid #ddd;
    margin: 0;               /* margin hata diya, gap se spacing control hogi */
    display: block;
}

.variant-img:hover {
    border-color: #000;
}

/* Optional: thin scrollbar */
.variant-scroll-wrap::-webkit-scrollbar {
    height: 6px;
}
.variant-scroll-wrap::-webkit-scrollbar-thumb {
    background: #ccc;
}
</style>

                    </div>

                <?php } ?>

            </div>

            <div class="col-md-6">
                <?php if (!empty($product['subcategory_name'])) { ?>
    <span class="text-uppercase small text-muted">
        <?= htmlspecialchars($product['subcategory_name']); ?>
    </span>
<?php } ?>

                <h1><?php echo $product['name']; ?></h1>
                <div class="rating mb-2"> ★★★★★ <span class="text-muted">(5.0)</span>
                </div>

                <?php
                $discount = 0;

                if ($product['rough_price'] > $product['price']) {
                    $discount = round(
                        (($product['rough_price'] - $product['price']) / $product['rough_price']) * 100
                    );
                }
                ?>
                <!--<h3 class="mt-3" id="price">-->

                <!--    <span class=" fw-bold" style="color:#bd4871 !important;">-->
                <!--        ₹<?= number_format($product['price'], 2); ?>-->
                <!--    </span>-->

                <!--    <?php if ($product['rough_price'] > $product['price']) { ?>-->

                <!--        <span class="text-muted text-decoration-line-through ms-2" style="font-size: 18px;">-->
                <!--            ₹<?= number_format($product['rough_price'], 2); ?>-->
                <!--        </span>-->

                <!--        <span class="badge bg-success ms-2">-->
                <!--            <?= $discount; ?>% OFF-->
                <!--        </span>-->

                <!--    <?php } ?>-->

                <!--</h3>-->
                
                <?php
$display_price = (float)$product['price'] * 1.20;
$display_rough_price = (float)$product['rough_price'] * 1.20;
?>

<h3 class="mt-3" id="price">

    <span class="fw-bold" style="color:#bd4871 !important;">
        ₹<?= number_format($display_price, 2); ?>
    </span>

    <?php if ($product['rough_price'] > $product['price']) { ?>

        <span class="text-muted text-decoration-line-through ms-2" style="font-size: 18px;">
            ₹<?= number_format($display_rough_price, 2); ?>
        </span>

        <span class="badge bg-success ms-2">
            <?= $discount; ?>% OFF
        </span>

    <?php } ?>

</h3>
                 
                 <div class="b2b-only mb-3">
                    <!--<span class="b2b-note">B2B pricing &middot; GST extra &middot; bulk slabs available at checkout</span>-->
                     <span class="b2b-note">These are Retail Price &middot; For Wholesale Price </span>

                </div>
                <input type="hidden" id="variant_id" value="">

                
                
                
         <div class="mt-4">

    <label class="fw-bold mb-2 d-block">Select Size</label>

    <div id="sizeArea"> 
    <?php 
    $sizes = array_filter(array_map('trim', explode(',', $product['size'])));
    $totalSizes = count($sizes);

    foreach($sizes as $size){ 
    ?> 

        <button type="button" 
                class="btn btn-outline-dark size-btn m-1" 
                data-size="<?= htmlspecialchars($size) ?>"> 

            <?= htmlspecialchars($size) ?> 

        </button> 

    <?php } ?> 
</div>

<input type="hidden" id="total_sizes" value="<?= $totalSizes ?>">

</div>


<?php
$colors = explode(',', $product['color']);

$colorMap = [
    "black"        => "#000000",
    "white"        => "#ffffff",
    "red"          => "#ff0000",
    "blue"         => "#0066ff",
    "green"        => "#28a745",
    "yellow"       => "#ffd700",
    "orange"       => "#ff7f00",
    "pink"         => "#ff69b4",
    "purple"       => "#800080",
    "brown"        => "#8b4513",

    "grey"         => "#808080",
    "gray"         => "#808080",

    "navy"         => "#001f54",
    "maroon"       => "#800000",
    "olive"        => "#808000",

    "sky blue"     => "#87ceeb",
    "skyblue"      => "#87ceeb",

    "light blue"   => "#add8e6",
    "dark blue"    => "#00008b",

    "light green"  => "#90ee90",
    "dark green"   => "#006400",

    "light red"    => "#ffcccb",
    "dark red"     => "#8b0000",

    "light pink"   => "#ffb6c1",
    "dark pink"    => "#c71585",

    "light purple" => "#d8b4fe",
    "dark purple"  => "#4b0082",

    "beige"        => "#f5f5dc",
    "cream"        => "#fffdd0",

    "gold"         => "#d4af37",
    "silver"       => "#c0c0c0",

    "magenta"      => "#ff00ff",
    "cyan"         => "#00ffff",
    "teal"         => "#008080",
    "turquoise"    => "#40e0d0",

    "lime"         => "#00ff00",
    "coral"        => "#ff7f50",
    "peach"        => "#ffdab9",

    "khaki"        => "#f0e68c",
    "wine"         => "#722f37",
    "rust"         => "#b7410e",
    "mustard"      => "#ffdb58",
    "charcoal"     => "#36454f",
];
?>

<div class="mt-3">
    <label class="fw-bold mb-2 d-block">Select Color</label>

    <div id="colorArea">

        <?php foreach($colors as $color):

            $color = trim($color);

/* Normalize color name */
$colorKey = strtolower($color);
$colorKey = str_replace("-", " ", $colorKey);
$colorKey = preg_replace('/\s+/', ' ', $colorKey);

$bg = $colorMap[$colorKey] ?? "#cccccc";
        ?>

        <button type="button"
                class="color-btn"
                data-color="<?= htmlspecialchars($color) ?>">

            <span class="color-dot"
                  style="background:<?= $bg ?>;"></span>

            <?= htmlspecialchars($color) ?>

        </button>

        <?php endforeach; ?>

    </div>
</div>

<input type="hidden" id="selected_size" value="">
<input type="hidden" id="selected_color" value="">




               



                <div class="mt-4">

                    <!--<div class="mb-3">-->

                    <!--    <label class="form-label fw-bold">Quantity per Set </label>-->

                    <!--    <select id="qty" class="form-select" style="max-width:200px;">-->

                    <!--        <option value="5">5</option>-->
                    <!--        <option value="10">10</option>-->
                    <!--        <option value="20">20</option>-->
                    <!--        <option value="50">50</option>-->

                    <!--    </select>-->

                    <!--</div>-->
                    
                     
<div class="mb-3">

    <label class="form-label fw-bold">Quantity per Set</label>

    <div class="quantity-box">

        <button type="button" class="qty-btn" id="qtyMinus">−</button>

        <input
            type="number"
            id="qty"
            class="qty-input"
            min="1"
            value="1"
            readonly
        >

        <button type="button" class="qty-btn" id="qtyPlus">+</button>

    </div>

</div>

<style>
.quantity-box {
    display: flex;
    align-items: center;
    width: 200px;
    border: 1px solid #ddd;
}

.qty-btn {
    width: 50px;
    height: 45px;
    border: 0;
    background: #f5f5f5;
    font-size: 22px;
    cursor: pointer;
}

.qty-btn:hover {
    background: #00005f;
    color: #fff;
}

.qty-input {
    width: 100px;
    height: 45px;
    text-align: center;
    border: 0;
    font-size: 16px;
    font-weight: 600;
    outline: none;
}

/* Remove number input arrows */
.qty-input::-webkit-inner-spin-button,
.qty-input::-webkit-outer-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

.qty-input {
    -moz-appearance: textfield;
}
</style>

<script>
document.getElementById("qtyMinus").addEventListener("click", function () {

    let qty = document.getElementById("qty");
    let current = parseInt(qty.value) || 1;

    if (current > 1) {
        qty.value = current - 1;
    }

});

document.getElementById("qtyPlus").addEventListener("click", function () {

    let qty = document.getElementById("qty");
    let current = parseInt(qty.value) || 1;

    qty.value = current + 1;

});
</script>
 

                    <button
                        id="addCartBtn"
                        class="btn btn-dark btn-lg"
                        data-product="<?php echo $product['id']; ?>" style="border-radius::0px !important;">

                        Add To Cart

                    </button>
<style>
    .btn{
        --bs-btn-border-radius: 0px !important;
    }
    
</style>

                </div>
                
                
                <p style="text-decoration:justify">

                    <?php echo $product['about_product']; ?>

                </p>

            </div>

        </div>


        <div class="mt-5">
            <h2 class="h5 mb-3">You may also like</h2> 
            
            <?php
if (!empty($product['subcategory_id'])) {

    $subcategoryId = (int)$product['subcategory_id'];

    $related = $conn->query("
        SELECT *
        FROM products
        WHERE subcategory_id = '$subcategoryId'
        AND id != '" . (int)$product['id'] . "'
        LIMIT 4
    ");

} else {

    $related = $conn->query("
        SELECT *
        FROM products
        WHERE id != '" . (int)$product['id'] . "'
        ORDER BY id DESC
        LIMIT 4
    ");

}
?>
            
            <div class="row g-3"> 
            <?php while ($r = $related->fetch_assoc()) { ?> 
            <div class="col-md-3">
                        <div class="product-card"> <a href="b2b-product?product=<?= urlencode($r['name']) ?>"> <img src="../admin/uploads/<?= $r['image'] ?>" class="img-fluid"> </a>
                            <div class="body">
                                <div class="name text-center"> <?= $r['name'] ?> </div>
                                <div class="price text-center"> ₹<?= $r['price'] ?> </div>
                            </div>
                        </div>
                    </div> <?php } ?> </div>
        </div>

      </div>
      <!-- END RIGHT SIDE -->

    </div>
  </div>
</section>


<script src="wishlist.js"></script>

<style>
  .category-card {
    overflow: hidden;
    transition: .3s ease;
    background: #fff;
    border-radius: 0;
  }

  .category-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 12px 25px rgba(0, 0, 0, .12) !important;
  }

  .category-card img {
    width: 100%;
    /* height: 320px;
     
    object-fit: cover; */
    border-radius: 0;
  }

  .category-card .card-body {
    padding: 18px;
  }

  .category-card h6 {
    font-size: 18px;
    font-weight: 600;
    color: #222;
    margin: 0;
  }

  /* Tablet */
  @media (max-width:991px) {
    .category-card img {
      height: 260px;
    }
  }

  /* Mobile */
  @media (max-width:576px) {
    .category-card img {
      height: 180px;
    }

    .category-card h6 {
      font-size: 16px;
    }
  }
</style>

<style>
  .size-btn.active{
    background:#00005f;
    color:#fff;
}



.color-btn{
    display:inline-flex;
    align-items:center;
    gap:8px;
    padding:5px 10px;
    margin:5px;
    border:1px solid #ddd;
    border-radius:5px;
    background:#fff;
    cursor:pointer;
    transition:.3s;
    font-size:14px;
    font-weight:500;
}

.color-btn:hover{
    border-color:#00005f;
}

.color-btn.active{
    background:#00005f;
    color:#fff;
    border-color:#fff;
}

.color-dot{
    width:16px;
    height:16px;
    border-radius:50%;
    border:1px solid #ccc;
    display:inline-block;
}

 


</style>

<script>
    // Size Selection
// document.querySelectorAll(".size-btn").forEach(btn => {

//     btn.addEventListener("click", function(){

//         document.querySelectorAll(".size-btn")
//             .forEach(b => b.classList.remove("active"));

//         this.classList.add("active");

//         document.getElementById("selected_size").value =
//             this.dataset.size;
//     });

// });




// Multiple Size Selection
document.querySelectorAll(".size-btn").forEach(function(btn){

    btn.addEventListener("click", function(){

        // Toggle size selection
        this.classList.toggle("active");

        // Get all selected sizes
        let selectedSizes = [];

        document.querySelectorAll(".size-btn.active").forEach(function(activeBtn){
            selectedSizes.push(activeBtn.dataset.size);
        });

        // Store selected sizes
        document.getElementById("selected_size").value =
            selectedSizes.join(",");

    });

});



// Color Selection
document.querySelectorAll(".color-btn").forEach(function(btn){

    btn.addEventListener("click",function(){

        document.querySelectorAll(".color-btn").forEach(function(b){
            b.classList.remove("active");
        });

        this.classList.add("active");

        document.getElementById("selected_color").value =
            this.dataset.color;

    });

});

// Color Selection - Multiple Colors
// document.querySelectorAll(".color-btn").forEach(function(btn){

//     btn.addEventListener("click", function(){

//         // Toggle selected color
//         this.classList.toggle("active");

//         // Get all selected colors
//         let selectedColors = [];

//         document.querySelectorAll(".color-btn.active").forEach(function(activeBtn){
//             selectedColors.push(activeBtn.dataset.color);
//         });

//         // Store selected colors as comma separated value
//         document.getElementById("selected_color").value =
//             selectedColors.join(",");

//     });

// });

document.querySelector(".size-btn")?.click();
document.querySelector(".color-btn")?.click();
</script>
<script>
//     document.querySelectorAll(".variant-img").forEach(function(img) {

//     img.addEventListener("click", function() {

//         document.getElementById("mainImage").src = this.dataset.image;

//         let price = parseFloat(this.dataset.price);
//         let rough = parseFloat(this.dataset.roughPrice);

//         let discount = 0;

//         if (rough > price) {
//             discount = Math.round(((rough - price) / rough) * 100);
//         }

//         let html = `
//             <span class="fw-bold" style="color:#bd4871">
//                 ₹${price.toFixed(2)}
//             </span>
//         `;

//         if (rough > price) {

//             html += `
//                 <span class="text-muted text-decoration-line-through ms-2">
//                     ₹${rough.toFixed(2)}
//                 </span>

//                 <span class="badge bg-success ms-2">
//                     ${discount}% OFF
//                 </span>
//             `;

//         }

//         document.getElementById("price").innerHTML = html;

         

//         document.getElementById("selectedColorBox").style.background =
//             this.dataset.color;

//     });

// });




document.querySelectorAll(".variant-img").forEach(function(img) {

    img.addEventListener("click", function() {

        document.getElementById("mainImage").src = this.dataset.image;

        // let price = parseFloat(this.dataset.price);
        // let rough = parseFloat(this.dataset.roughPrice);
        
        let price = parseFloat(this.dataset.price) * 1.20;
let rough = parseFloat(this.dataset.roughPrice) * 1.20;

        let discount = 0;

        if (rough > price) {
            discount = Math.round(
                ((rough - price) / rough) * 100
            );
        }

        let html = `
            <span class="fw-bold" style="color:#bd4871">
                ₹${price.toFixed(2)}
            </span>
        `;

        if (rough > price) {

            html += `
                <span class="text-muted text-decoration-line-through ms-2">
                    ₹${rough.toFixed(2)}
                </span>

                <span class="badge bg-success ms-2">
                    ${discount}% OFF
                </span>
            `;
        }

        document.getElementById("price").innerHTML = html;

        document.getElementById("variant_id").value =
            this.dataset.variantId;

    });

});
</script>
 
<script>
document.getElementById("addCartBtn").addEventListener("click", function() {

    let productId = this.dataset.product;

    // Quantity entered by user = quantity PER SIZE
    let baseQty = parseInt(document.getElementById("qty").value) || 0;

    let variantId = document.getElementById("variant_id").value || 0;

    let size = document.getElementById("selected_size").value;
    let color = document.getElementById("selected_color").value;

    // Available total sizes
    let totalSizes = parseInt(
        document.getElementById("total_sizes").value || 0
    );

    // Selected sizes
    let selectedSizes = size
        .split(",")
        .map(s => s.trim())
        .filter(s => s !== "");

    /*
    =====================================================
    VALIDATION
    =====================================================
    */

    // Quantity validation
    if (baseQty < 1) {

        alert("Please enter a valid quantity.");

        return;
    }


    // Color validation
    if (color == "") {

        alert("Please select Color");

        return;
    }


    /*
    =====================================================
    SIZE VALIDATION
    =====================================================
    
    1-4 available sizes:
        ALL sizes required

    More than 4 available sizes:
        Minimum 4 sizes required
    */

    let requiredSizes = totalSizes <= 4 ? totalSizes : 4;


    if (selectedSizes.length < requiredSizes) {

        let popup = document.createElement("div");

        popup.innerHTML = `
            <div style="
                position:fixed;
                top:0;
                left:0;
                width:100%;
                height:100%;
                background:rgba(0,0,0,0.5);
                display:flex;
                align-items:center;
                justify-content:center;
                z-index:99999;
            ">

                <div style="
                    background:#fff;
                    padding:25px;
                    width:90%;
                    max-width:400px;
                    text-align:center;
                    box-shadow:0 5px 25px rgba(0,0,0,0.2);
                ">

                    <div style="
                        font-size:45px;
                        margin-bottom:10px;
                    ">
                        ⚠️
                    </div>

                    <h5 style="margin-bottom:10px;">
                        Size Selection Required
                    </h5>

                    <p style="
                        margin-bottom:20px;
                        color:#666;
                    ">
                        Please select ${requiredSizes} size${requiredSizes > 1 ? 's' : ''}.
                    </p>

                    <button id="closeSizePopup" style="
                        background:#00005f;
                        color:#fff;
                        border:0;
                        padding:10px 30px;
                        cursor:pointer;
                    ">
                        OK
                    </button>

                </div>

            </div>
        `;

        document.body.appendChild(popup);

        document.getElementById("closeSizePopup").onclick = function() {
            popup.remove();
        };

        return;
    }


    /*
    =====================================================
    IMPORTANT CALCULATION
    =====================================================

    Example:

    Quantity per Set = 5

    1 Size  = 5 × 1 = 5
    2 Sizes = 5 × 2 = 10
    3 Sizes = 5 × 3 = 15
    4 Sizes = 5 × 4 = 20
    */

    let finalQty = baseQty * selectedSizes.length;


    /*
    =====================================================
    ADD TO CART
    =====================================================
    */

    fetch("b2b-add-to-cart.php", {

        method: "POST",

        headers: {
            "Content-Type":
                "application/x-www-form-urlencoded"
        },

        body:
            "product_id=" +
            encodeURIComponent(productId) +

            "&qty=" +
            encodeURIComponent(finalQty) +

            "&variant_id=" +
            encodeURIComponent(variantId) +

            "&size=" +
            encodeURIComponent(size) +

            "&color=" +
            encodeURIComponent(color)

    })

    .then(res => res.json())

    .then(function(data) {

        /*
        =====================================================
        LOGIN REQUIRED
        =====================================================
        */

        if (data.status == "login") {

            const currentPage =
                window.location.pathname +
                window.location.search;

            const loginUrl =
                "register?redirect=" +
                encodeURIComponent(currentPage) +

                "&addcart=1" +

                "&product_id=" +
                encodeURIComponent(productId) +

                "&qty=" +
                encodeURIComponent(finalQty) +

                "&variant_id=" +
                encodeURIComponent(variantId) +

                "&size=" +
                encodeURIComponent(size) +

                "&color=" +
                encodeURIComponent(color);

            window.location.href = loginUrl;

            return;
        }


        /*
        =====================================================
        SUCCESS
        =====================================================
        */

        if (data.status == "success") {

            const addCartBtn =
                document.getElementById("addCartBtn");

            const toast =
                document.getElementById("cartSuccessToast");


            // Change button
            addCartBtn.innerHTML =
                '<i class="bi bi-check-circle"></i> Added';


            // Show toast
            if (toast) {
                toast.classList.add("show");
            }


            // Update cart count
            let badge =
                document.getElementById("cartCount");

            if (badge) {

                badge.innerHTML = data.count;

                if (parseInt(data.count) > 0) {
                    badge.style.display = "inline-flex";
                } else {
                    badge.style.display = "none";
                }

            }


            // Hide toast after 3 seconds
            setTimeout(function() {

                if (toast) {
                    toast.classList.remove("show");
                }

            }, 3000);


            // Reset button
            setTimeout(function() {

                addCartBtn.innerHTML =
                    '<i class="bi bi-cart-plus"></i> Add To Cart';

            }, 1500);

        }


        /*
        =====================================================
        ERROR
        =====================================================
        */

        else {

            alert(data.message);

        }

    })

    .catch(function(error) {

        console.error("Add to cart error:", error);

    });

});
</script>
 

<script>
    
    
// document.getElementById("addCartBtn").addEventListener("click", function() {

//     let productId = this.dataset.product;
//     let qty = document.getElementById("qty").value;
//     let variantId = document.getElementById("variant_id").value || 0;

//     let size = document.getElementById("selected_size").value;
//     let color = document.getElementById("selected_color").value;

//     // Available total sizes
//     let totalSizes = parseInt(
//         document.getElementById("total_sizes").value || 0
//     );

//     // Get currently selected sizes
//     let selectedSizes = size
//         .split(",")
//         .map(s => s.trim())
//         .filter(s => s !== "");

//     /*
//      * Logic:
//      * If product has 1, 2, 3 or 4 sizes:
//      *     User must select ALL available sizes.
//      *
//      * If product has more than 4 sizes:
//      *     User must select minimum 4 sizes.
//      */
//     let requiredSizes = totalSizes <= 4 ? totalSizes : 4;


//     // Color validation
//     if (color == "") {
//         alert("Please select Color");
//         return;
//     }


//     // Size validation
//     if (selectedSizes.length < requiredSizes) {

//         let popup = document.createElement("div");

//         popup.innerHTML = `
//             <div style="
//                 position:fixed;
//                 top:0;
//                 left:0;
//                 width:100%;
//                 height:100%;
//                 background:rgba(0,0,0,0.5);
//                 display:flex;
//                 align-items:center;
//                 justify-content:center;
//                 z-index:99999;
//             ">

//                 <div style="
//                     background:#fff;
//                     padding:25px;
//                     width:90%;
//                     max-width:400px;
//                     text-align:center;
//                     box-shadow:0 5px 25px rgba(0,0,0,0.2);
//                 ">

//                     <div style="
//                         font-size:45px;
//                         margin-bottom:10px;
//                     ">
//                         ⚠️
//                     </div>

//                     <h5 style="margin-bottom:10px;">
//                         Size Selection Required
//                     </h5>

//                     <p style="margin-bottom:20px;color:#666;">
//                         Please select ${requiredSizes} size${requiredSizes > 1 ? 's' : ''}.
//                     </p>

//                     <button id="closeSizePopup" style="
//                         background:#00005f;
//                         color:#fff;
//                         border:0;
//                         padding:10px 30px;
//                         cursor:pointer;
//                     ">
//                         OK
//                     </button>

//                 </div>
//             </div>
//         `;

//         document.body.appendChild(popup);

//         document.getElementById("closeSizePopup").onclick = function() {
//             popup.remove();
//         };

//         return;
//     }


//     // Add to Cart
//     fetch("b2b-add-to-cart.php", {

//         method: "POST",

//         headers: {
//             "Content-Type": "application/x-www-form-urlencoded"
//         },

//         body:
//             "product_id=" + encodeURIComponent(productId) +
//             "&qty=" + encodeURIComponent(qty) +
//             "&variant_id=" + encodeURIComponent(variantId) +
//             "&size=" + encodeURIComponent(size) +
//             "&color=" + encodeURIComponent(color)

//     })

//     .then(res => res.json())

//     .then(function(data) {

//         // Login required
//         if (data.status == "login") {

//             const currentPage =
//                 window.location.pathname + window.location.search;

//             const loginUrl =
//                 "register?redirect=" +
//                 encodeURIComponent(currentPage) +
//                 "&addcart=1" +
//                 "&product_id=" + encodeURIComponent(productId) +
//                 "&qty=" + encodeURIComponent(qty) +
//                 "&variant_id=" + encodeURIComponent(variantId) +
//                 "&size=" + encodeURIComponent(size) +
//                 "&color=" + encodeURIComponent(color);

//             window.location.href = loginUrl;

//             return;
//         }


//         // Successfully added
//         if (data.status == "success") {

//             const addCartBtn =
//                 document.getElementById("addCartBtn");

//             const toast =
//                 document.getElementById("cartSuccessToast");


//             // Change button
//             addCartBtn.innerHTML =
//                 '<i class="bi bi-check-circle"></i> Added';


//             // Show success popup
//             if (toast) {
//                 toast.classList.add("show");
//             }


//             // Update cart count
//             let badge =
//                 document.getElementById("cartCount");

//             if (badge) {

//                 badge.innerHTML = data.count;

//                 if (parseInt(data.count) > 0) {
//                     badge.style.display = "inline-flex";
//                 } else {
//                     badge.style.display = "none";
//                 }
//             }


//             // Hide toast after 3 seconds
//             setTimeout(function() {

//                 if (toast) {
//                     toast.classList.remove("show");
//                 }

//             }, 3000);


//             // Reset button after 1.5 seconds
//             setTimeout(function() {

//                 addCartBtn.innerHTML =
//                     '<i class="bi bi-cart-plus"></i> Add To Cart';

//             }, 1500);

//         }


//         // Error
//         else {

//             alert(data.message);

//         }

//     })

//     .catch(function(error) {

//         console.error("Add to cart error:", error);

//     });

// });
</script>
 


<style>
/* Add To Cart Success Toast */
.cart-success-toast {
    position: fixed;
    left: 50%;
    bottom: 30px;
    transform: translate(-50%, 30px);
    background: #ffffff;
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

/* Mobile */
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

    <script>
        document.querySelectorAll(".variant-img").forEach(function(img) {

            img.addEventListener("click", function() {

                document.getElementById("mainImage").src = this.dataset.image;

                // let price = parseFloat(this.dataset.price);
                // let rough = parseFloat(this.dataset.roughPrice);
                
                let price = parseFloat(this.dataset.price) * 1.20;
let rough = parseFloat(this.dataset.roughPrice) * 1.20;

                let discount = 0;

                if (rough > price) {
                    discount = Math.round(((rough - price) / rough) * 100);
                }

                let html = `
            <span class=" fw-bold" style="color:#bd4871 !important;" >
                ₹${price.toFixed(2)}
            </span>
        `;

                if (rough > price) {
                    html += `
                <span class="text-muted text-decoration-line-through ms-2" style='font-size: 15px;' >
                    ₹${rough.toFixed(2)}
                </span>

                <span class="badge bg-success ms-2" style='font-size: 15px;'>
                    ${discount}% OFF
                </span>
            `;
                }

                document.getElementById("price").innerHTML = html;

                document.getElementById("variant_id").value = this.dataset.variantId;
                
                
                

            });

        });
    </script>





    <br>
    <?php include 'footer.php'; ?>

</body>

</html>

</body>

</html>