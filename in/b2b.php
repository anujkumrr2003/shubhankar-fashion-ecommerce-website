<?php
session_start();
include "config.php";

/*
|--------------------------------------------------------------------------
| Fetch Categories
|--------------------------------------------------------------------------
*/

$categories = [];

$sql = mysqli_query($conn, "
SELECT
    id,
    subcategory_name,
    image,
    status
FROM subcategories
WHERE status='Active'
ORDER BY subcategory_name ASC
");

while ($row = mysqli_fetch_assoc($sql)) {
    $categories[] = $row;
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

    <br>
    <?php foreach ($categories as $cat) { ?>

        <div class="category-card">

            <a href="b2b-category.php?id=<?= $cat['id']; ?>">

                <img src="../admin/uploads/<?= $cat['image']; ?>" width="120">

                <h5><?= htmlspecialchars($cat['subcategory_name']); ?></h5>

            </a>

        </div>

    <?php } ?>




    <br>
<?php include '../footer.php'; ?>

</body>
</html>