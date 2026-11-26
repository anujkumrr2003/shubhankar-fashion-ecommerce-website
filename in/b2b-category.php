<?php
session_start();
include "config.php";

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location:b2b.php");
    exit;
}

$subcategory_id = (int)$_GET['id'];

/* Get Subcategory Name */

$sub = mysqli_query($conn, "
SELECT *
FROM subcategories
WHERE id='$subcategory_id'
LIMIT 1
");

if (mysqli_num_rows($sub) == 0) {
    die("Subcategory not found.");
}

$subcategory = mysqli_fetch_assoc($sub);

/* Fetch Products */

$products = [];

$sql = mysqli_query($conn, "
SELECT
    id,
    name,
    image,
    price,
    rough_price,
    avliable_stock
FROM products
WHERE subcategory_id='$subcategory_id'
ORDER BY id DESC
");

while ($row = mysqli_fetch_assoc($sql)) {
    $products[] = $row;
}
?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home | Shubhankar</title>
    <meta name="description" content="Shubhankar — herbal skincare, haircare and wellness essentials. B2B pricing for India, retail pricing worldwide.">
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


    <?php include "header.php"; ?>

    <h2>
        <?php echo $subcategory['subcategory_name']; ?>
    </h2>

    <div class="row">

        <?php foreach ($products as $product) { ?>

            <div class="col-lg-3 col-md-4 col-6 mb-4">

                <div class="card h-100">

                    <img src="../admin/uploads/<?php echo $product['image']; ?>"
                        class="card-img-top"
                        style="height:220px;object-fit:cover;">

                    <div class="card-body">

                        <h6>
                            <?php echo htmlspecialchars($product['name']); ?>
                        </h6>

                        <p class="mb-1">
                            ₹<?php echo number_format($product['price'], 2); ?>
                        </p>

                        <a href="b2b-product.php?id=<?php echo $product['id']; ?>"
                            class="btn btn-dark w-100">

                            View Product

                        </a>

                    </div>

                </div>

            </div>

        <?php } ?>

        <?php if (empty($products)) { ?>

            <div class="col-12">

                <div class="alert alert-warning">

                    No products found.

                </div>

            </div>

        <?php } ?>

    </div>


    <br>
    <?php include '../footer.php'; ?>

</body>

</html>