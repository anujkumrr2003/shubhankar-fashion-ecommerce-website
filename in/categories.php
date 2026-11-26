

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
<title>Men's Clothing Categories | Shubhankar India</title>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<meta name="title" content="Men's Clothing Categories | Shubhankar India">

<meta name="description" content="Browse men's fashion categories at Shubhankar India. Shop premium jeans, shirts, t-shirts, blazers, trousers and more with secure shopping and PAN India delivery.">

<meta name="keywords" content="Men's Clothing Categories India, Men's Fashion Categories, Jeans, Shirts, T Shirts, Blazers, Trousers, Men's Wear India, Online Shopping India, Shubhankar India">

<meta name="author" content="Shubhankar">
<meta name="publisher" content="Shubhankar">

<meta name="robots" content="index,follow,max-image-preview:large,max-snippet:-1,max-video-preview:-1">

<link rel="canonical" href="https://shubhankarfashion.com/in/categories">

<link rel="alternate" hreflang="en-IN" href="https://shubhankarfashion.com/in/categories">
<link rel="alternate" hreflang="x-default" href="https://shubhankarfashion.com/in/categories">

<!-- Open Graph -->
<meta property="og:type" content="website">
<meta property="og:site_name" content="Shubhankar India">
<meta property="og:title" content="Men's Clothing Categories | Shubhankar India">
<meta property="og:description" content="Explore all men's clothing categories including jeans, shirts, t-shirts, blazers and more.">
<meta property="og:url" content="https://shubhankarfashion.com/in/categories">
<meta property="og:image" content="https://shubhankarfashion.com/img/logo-removebg-preview.png">
<meta property="og:image:secure_url" content="https://shubhankarfashion.com/img/logo-removebg-preview.png">
<meta property="og:image:type" content="image/png">
<meta property="og:image:alt" content="Shubhankar India Logo">
<meta property="og:locale" content="en_IN">

<!-- Twitter -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="Men's Clothing Categories | Shubhankar India">
<meta name="twitter:description" content="Browse premium men's clothing categories online in India.">
<meta name="twitter:image" content="https://shubhankarfashion.com/img/logo-removebg-preview.png">

<!-- Favicon -->
<link rel="icon" type="image/png" href="https://shubhankarfashion.com/img/logo-removebg-preview.png">
<link rel="shortcut icon" href="https://shubhankarfashion.com/img/logo-removebg-preview.png">
<link rel="apple-touch-icon" href="https://shubhankarfashion.com/img/logo-removebg-preview.png">

<meta name="theme-color" content="#000000">
<meta name="application-name" content="Shubhankar India">
<meta name="apple-mobile-web-app-title" content="Shubhankar India">

<meta name="geo.region" content="IN">
<meta name="geo.country" content="India">
<meta name="ICBM" content="20.5937,78.9629">
<script type="application/ld+json">
{
  "@context":"https://schema.org",
  "@type":"CollectionPage",
  "name":"Men's Clothing Categories",
  "url":"https://shubhankarfashion.com/in/categories",
  "description":"Browse all men's clothing categories including jeans, shirts, t-shirts, blazers, trousers and more.",
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
      "name":"Categories",
      "item":"https://shubhankarfashion.com/in/categories"
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
<link rel="stylesheet" href="../css/style.css">

</head>
<body>

<?php include 'header.php'; ?>

<section class="section-sm bg-surface-2 border-bottom">
  <div class="container">
    <nav class="small text-muted-soft mb-2"><a href="index">Home</a> / <span>Categories</span></nav>
    <h1 class="h3 mb-0">Shop by Category</h1>
  </div>
</section>


<?php
include 'config.php';

$categories = $conn->query("
    SELECT id, category_id, subcategory_name, image
    FROM subcategories
    WHERE status='Active'
    ORDER BY subcategory_name ASC
");
?>

<section class="section">
    <div class="container">
        <div class="row g-4">

            <?php while($cat = $categories->fetch_assoc()) {

                $subId = $cat['id'];

                $countResult = $conn->query("
                    SELECT COUNT(*) AS total
                    FROM products
                    WHERE subcategory_id='$subId'
                ");

                $count = $countResult->fetch_assoc()['total'];
            ?>

            <div class="col-6 col-md-4 col-lg-3">

                <div class="card h-100 shadow-sm border-0 category-card" style="border-radius: 0px;">

                    <!-- <a href="shop.php?subcategory=<?php echo $cat['id']; ?>" class="text-decoration-none text-dark"> -->
                    <a href="shop.php?category=<?= urlencode($cat['subcategory_name']); ?>">
                        <?php
                        $image = (!empty($cat['image']) && file_exists("../admin/uploads/".$cat['image']))
                            ? "../admin/uploads/".$cat['image']
                            : "admin/uploads/no-image.png";
                        ?>

                        <img src="<?php echo $image; ?>"
                             class="card-img-top"
                             alt="<?php echo htmlspecialchars($cat['subcategory_name']); ?>" style=
                             "border-radius: 0px;">

                        <div class="card-body text-center">

                            <h6 class=" fw-bold">
                                <?php echo htmlspecialchars($cat['subcategory_name']); ?>
                            </h6>

                            <span class="text-muted small">
                                <?php echo $count; ?> Products
                            </span>

                        </div>

                    </a>

                </div>

            </div>

            <?php } ?>

        </div>
    </div>
</section>




<style>.category-card{
    /* border-radius:0px; */
    overflow:hidden;
    transition:.3s;
}

.category-card:hover{
    transform:translateY(-5px);
    box-shadow:0 10px 25px rgba(0,0,0,.12)!important;
}

.category-card img{
    width:100%;
    /* height:220px;
    object-fit:cover; */
}

.category-card .card-body{
    padding:5px;
}

.category-card h6{
    font-size:18px;
    margin-bottom:0px;
}

.category-card span{
    font-size:14px;
}</style>
<!-- <script>
  document.addEventListener("DOMContentLoaded", function () {
    document.getElementById("catGrid").innerHTML = CATEGORIES.map(function(c){
      var count = PRODUCTS.filter(function(p){ return p.cat === c.slug; }).length;
      return '<div class="col-6 col-md-4"><a href="shop.php?cat='+c.slug+'" class="cat-tile d-block h-100 py-4">' +
             '<div class="ic"><i class="bi '+c.icon+'"></i></div>' +
             '<div class="fw-semibold">'+c.name+'</div>' +
             '<div class="small text-muted-soft">'+count+' products</div></a></div>';
    }).join("");
  });
</script> -->

<!-- ============ FOOTER ============ -->
<?php include 'footer.php'; ?>


</body>
</html>
