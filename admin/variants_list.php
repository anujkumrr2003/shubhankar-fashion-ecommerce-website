<?php include('menu.php') ?>

<div class="row">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-header">
                <h4 class="header-title">Add Varient Product</h4>
            </div>



         <?php
include "config.php";

if (!isset($_GET['product_id'])) {
    die("Product not selected.");
}

$product_id = intval($_GET['product_id']);

/* ================= DELETE SINGLE IMAGE ================= */
if (isset($_GET['delete_img'])) {
    $img = trim($_GET['delete_img']);
    $vid = intval($_GET['vid']);

    $q = $conn->query("SELECT images FROM product_variants WHERE id='$vid'");
    $r = $q->fetch_assoc();

    $imgs = explode(",", $r['images']);
    $newImgs = [];

    foreach ($imgs as $i) {
        if (trim($i) != $img) {
            $newImgs[] = trim($i);
        }
    }

    $filePath = "img/" . $img;
    if ($img != "" && file_exists($filePath)) {
        unlink($filePath);
    }

    $imgStr = implode(",", $newImgs);

    $conn->query("UPDATE product_variants SET images='$imgStr' WHERE id='$vid'");

    echo "<script>
    alert('Image Deleted Successfully');
    window.location='variants_list.php?product_id=".$product_id."&edit=".$vid."';
    </script>";
    exit;
}

/* ================= SAVE VARIANT ================= */
if (isset($_POST['save_variant'])) {

    $vid   = $_POST['variant_id'] ?? '';
    $price = $_POST['variant_price'] ?? '';

    $newImages = [];

    if (!empty($_FILES['variant_images']['name'][0])) {
        foreach ($_FILES['variant_images']['name'] as $key => $img) {
            if ($img != "") {
                $ext = pathinfo($img, PATHINFO_EXTENSION);
                $file = time() . rand(1000, 9999) . "." . $ext;

                move_uploaded_file($_FILES['variant_images']['tmp_name'][$key], "img/" . $file);
                $newImages[] = $file;
            }
        }
    }

    /* INSERT */
    if ($vid == "") {
        $imgStr = implode(",", $newImages);

        $conn->query("INSERT INTO product_variants (product_id, variant_price, images)
                      VALUES ('$product_id', '$price', '$imgStr')");

        echo "<script>
        alert('Variant Added');
        window.location='variants_list.php?product_id=$product_id';
        </script>";
        exit;
    } 
    /* UPDATE */ 
    else {
        $q = $conn->query("SELECT images FROM product_variants WHERE id='$vid'");
        $old = $q->fetch_assoc();

        $oldImgs = $old['images'] ? explode(",", $old['images']) : [];
        $allImgs = array_merge($oldImgs, $newImages);
        $imgStr = implode(",", $allImgs);

        $conn->query("UPDATE product_variants SET
                      variant_price='$price',
                      images='$imgStr'
                      WHERE id='$vid'");

        echo "<script>
        alert('Variant Updated');
        window.location='variants_list.php?product_id=$product_id';
        </script>";
        exit;
    }
}

/* ================= DELETE VARIANT ================= */
if (isset($_GET['delete'])) {
    $vid = $_GET['delete'];

    $q = $conn->query("SELECT images FROM product_variants WHERE id='$vid'");
    $r = $q->fetch_assoc();

    if ($r['images'] != "") {
        foreach (explode(",", $r['images']) as $img) {
            if (file_exists("img/" . $img)) {
                unlink("img/" . $img);
            }
        }
    }

    $conn->query("DELETE FROM product_variants WHERE id='$vid'");

    echo "<script>
    alert('Variant Deleted');
    window.location='variants_list.php?product_id=$product_id';
    </script>";
    exit;
}

/* ================= EDIT FETCH ================= */
$editData = null;
if (isset($_GET['edit'])) {
    $vid = $_GET['edit'];
    $editData = $conn->query("SELECT * FROM product_variants WHERE id='$vid'")->fetch_assoc();
}

/* ================= FETCH VARIANTS ================= */
$variants = $conn->query("
    SELECT 
        pv.*,
        p.name AS product_name,
        s.subcategory_name
    FROM product_variants pv
    LEFT JOIN products p ON pv.product_id = p.id
    LEFT JOIN subcategories s ON p.subcategory_id = s.id
    WHERE pv.product_id = '$product_id'
    ORDER BY pv.id DESC
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Variants</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .variant-img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 6px;
            margin: 3px;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <h3 class="mb-3">Manage Product Variants - <?= $variants->num_rows > 0 ? $variants->fetch_assoc()['product_name'] : '' ?></h3>

        <form method="POST" enctype="multipart/form-data" class="border p-3 mb-4 bg-light">
            <input type="hidden" name="variant_id" value="<?= $editData['id'] ?? '' ?>">

            <div class="row">
                <div class="col-md-4">
                    <label>Price</label>
                    <input type="number" name="variant_price" class="form-control"
                           value="<?= $editData['variant_price'] ?? '' ?>" required>
                </div>

                <div class="col-md-8">
                    <label>Variant Images</label>
                    <input type="file" name="variant_images[]" class="form-control" multiple>
                </div>
            </div>

            <?php if ($editData && $editData['images'] != "") { ?>
                <div class="mt-3">
                    <label>Existing Images</label><br>
                    <?php
                    foreach (explode(",", $editData['images']) as $img) {
                        if ($img != "") {
                    ?>
                        <div style="display:inline-block;position:relative">
                            <img src="img/<?= $img ?>" class="variant-img">
                            <a href="?product_id=<?= $product_id ?>&edit=<?= $editData['id'] ?>&delete_img=<?= $img ?>&vid=<?= $editData['id'] ?>"
                               onclick="return confirm('Delete image?')"
                               style="position:absolute;top:-5px;right:-5px;background:red;color:#fff;border-radius:50%;padding:2px 6px;font-size:12px;text-decoration:none">x</a>
                        </div>
                    <?php 
                        }
                    } 
                    ?>
                </div>
            <?php } ?>

            <button name="save_variant" class="btn btn-success mt-3">
                <?= $editData ? 'Update Variant' : 'Add Variant' ?>
            </button>
        </form>

        <table class="table table-bordered">
            <tr>
                <th>Category Name</th>
                <th width="120">Price</th>
                <th>Images</th>
                <th width="150">Action</th>
            </tr>

            <?php 
            // Reset pointer for reuse if needed
            $variants->data_seek(0);
            while ($v = $variants->fetch_assoc()) { 
            ?>
                <tr>
                    <td><?= $v['subcategory_name'] ?? 'N/A' ?></td>
                    <td>₹ <?= $v['variant_price'] ?></td>
                    <td>
                        <?php
                        if ($v['images']) {
                            foreach (explode(",", $v['images']) as $img) {
                                if ($img != "") {
                                    echo "<img src='img/$img' class='variant-img'>";
                                }
                            }
                        }
                        ?>
                    </td>
                    <td>
                        <a href="?product_id=<?= $product_id ?>&edit=<?= $v['id'] ?>"
                           class="btn btn-primary btn-sm">Edit</a>
                        <a href="?product_id=<?= $product_id ?>&delete=<?= $v['id'] ?>"
                           onclick="return confirm('Delete Variant?')"
                           class="btn btn-danger btn-sm">Delete</a>
                    </td>
                </tr>
            <?php } ?>
        </table>
    </div>
</body>
</html>
            <!-- Vendor js -->
            <script src="assets/js/vendor.min.js"></script>

            <!-- Daterangepicker js -->
            <script src="assets/vendor/daterangepicker/moment.min.js"></script>
            <script src="assets/vendor/daterangepicker/daterangepicker.js"></script>

            <!-- Apex Charts js -->
            <script src="assets/vendor/apexcharts/apexcharts.min.js"></script>

            <!-- Vector Maps js -->
            <script src="assets/vendor/admin-resources/jquery.vectormap/jquery-jvectormap-1.2.2.min.js"></script>
            <script src="assets/vendor/admin-resources/jquery.vectormap/maps/jquery-jvectormap-world-mill-en.js"></script>
            <script src="assets/vendor/admin-resources/jquery.vectormap/maps/jquery-jvectormap-us-merc-en.js"></script>
            <script src="assets/vendor/admin-resources/jquery.vectormap/maps/jquery-jvectormap-in-mill-en.js"></script>
            <script src="assets/vendor/admin-resources/jquery.vectormap/maps/jquery-jvectormap-au-mill-en.js"></script>
            <script src="assets/vendor/admin-resources/jquery.vectormap/maps/jquery-jvectormap-us-il-chicago-mill-en.js"></script>
            <script src="assets/vendor/admin-resources/jquery.vectormap/maps/jquery-jvectormap-uk-mill-en.js"></script>
            <script src="assets/vendor/admin-resources/jquery.vectormap/maps/jquery-jvectormap-ca-lcc-en.js"></script>
            <script src="assets/vendor/admin-resources/jquery.vectormap/maps/jquery-jvectormap-europe-mill-en.js"></script>
            <script src="assets/vendor/admin-resources/jquery.vectormap/maps/jquery-jvectormap-fr-merc-en.js"></script>
            <script src="assets/vendor/admin-resources/jquery.vectormap/maps/jquery-jvectormap-es-merc.js"></script>
            <script src="assets/vendor/admin-resources/jquery.vectormap/maps/jquery-jvectormap-es-mill.js"></script>

            <!-- Vector Maps Demo js -->
            <script src="assets/js/pages/vector-maps.init.js"></script>

            <!-- Dashboard App js -->
            <script src="assets/js/pages/dashboard.js"></script>

            <!-- App js -->
            <script src="assets/js/app.min.js"></script>




            </body>

            </html>