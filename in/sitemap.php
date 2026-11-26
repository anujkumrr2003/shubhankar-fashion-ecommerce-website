<?php
include 'config.php';

header("Content-Type: application/xml; charset=UTF-8");

echo '<?xml version="1.0" encoding="UTF-8"?>';
?>

<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">

<?php
$base = "https://shubhankarfashion.com/in";

/* ===========================
   Static Pages
=========================== */

$pages = [
    "",
    "about",
    "contact",
    "shop",
    "categories"
];

foreach ($pages as $page) {

    $url = ($page == "") ? $base : $base . "/" . $page;
?>
<url>
    <loc><?= htmlspecialchars($url, ENT_XML1, 'UTF-8'); ?></loc>
    <changefreq>weekly</changefreq>
    <priority>0.9</priority>
</url>
<?php } ?>


<?php
/* ===========================
   Products
=========================== */

$productQuery = mysqli_query($conn, "SELECT name FROM products");

if ($productQuery) {

    while ($row = mysqli_fetch_assoc($productQuery)) {

        $url = $base . "/product/" . rawurlencode(trim($row['name']));
?>
<url>
    <loc><?= htmlspecialchars($url, ENT_XML1, 'UTF-8'); ?></loc>
    <changefreq>weekly</changefreq>
    <priority>0.8</priority>
</url>
<?php
    }
}
?>


<?php
/* ===========================
   Categories
=========================== */

$categoryQuery = mysqli_query($conn, "SELECT subcategory_name FROM subcategories");

if ($categoryQuery) {

    while ($row = mysqli_fetch_assoc($categoryQuery)) {

        $url = $base . "/shop/category/" . rawurlencode(trim($row['subcategory_name']));
?>
<url>
    <loc><?= htmlspecialchars($url, ENT_XML1, 'UTF-8'); ?></loc>
    <changefreq>weekly</changefreq>
    <priority>0.7</priority>
</url>
<?php
    }
}
?>

</urlset>