<?php
include "config.php";
$id = $_GET['id'];

$product = $conn->query("SELECT * FROM products WHERE id=$id")->fetch_assoc();
$variants = $conn->query("SELECT * FROM product_variants WHERE product_id=$id");
?>

<!DOCTYPE html>
<html>
<head>
    <title><?= $product['name'] ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        .variant-box { cursor: pointer; border: 1px solid #ccc; padding: 8px; border-radius: 5px; margin-right: 10px; }
        .variant-box:hover { background: #eee; }
    </style>
</head>

<body>

<div class="container mt-4">
    <h3><?= $product['name'] ?></h3>
    <p>Base Price: ₹<?= $product['price'] ?></p>

    <hr>

    <h5>Variants</h5>

    <div class="d-flex">

        <?php while ($v = $variants->fetch_assoc()) { ?>
            <div class="variant-box" onclick="changeVariant('<?= $v['image'] ?>', '<?= $v['variant_price'] ?>')">
                <?= $v['color'] ?> - <?= $v['size'] ?>
            </div>
        <?php } ?>

    </div>

    <hr>

    <img id="mainImg" src="img/<?= $variants->fetch_assoc()['image'] ?>" width="200">

    <h4 class="mt-3">Price: ₹<span id="priceBox"><?= $product['price'] ?></span></h4>

</div>

<script>
function changeVariant(img, price) {
    document.getElementById("mainImg").src = "img/" + img;
    document.getElementById("priceBox").innerHTML = price;
}
</script>

</body>
</html>
