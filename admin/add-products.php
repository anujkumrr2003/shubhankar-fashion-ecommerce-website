<?php include('menu.php') ?>

<div class="row">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-header">
                <h4 class="header-title">Add Products</h4>
            </div>
            
            
            
            
         <?php
include "config.php";




/* =========================================================
  FETCH SIZES AND COLORS
========================================================= */

$sizes = [];

$size_result = $conn->query("
    SELECT id, name
    FROM product_attributes
    WHERE type = 'size'
    ORDER BY id ASC
");

if (!$size_result) {
    die("Size Fetch Error : " . $conn->error);
}

while ($row = $size_result->fetch_assoc()) {
    $sizes[] = $row;
}


$colors = [];

$color_result = $conn->query("
    SELECT id, name
    FROM product_attributes
    WHERE type = 'color'
    ORDER BY id ASC
");

if (!$color_result) {
    die("Color Fetch Error : " . $conn->error);
}

while ($row = $color_result->fetch_assoc()) {
    $colors[] = $row;
}








/* SAVE / UPDATE PRODUCT */
if (isset($_POST['save_product'])) {

    $id = (int)($_POST['id'] ?? 0);

    // $subcategory_id   = mysqli_real_escape_string($conn, $_POST['subcategory_id'] ?? '');
    
     $subcategory_id = !empty($_POST['subcategory_id'])
    ? (int)$_POST['subcategory_id']
    : "NULL";
    
    $trending_type    = mysqli_real_escape_string($conn, $_POST['trending_type'] ?? '');
    $name             = mysqli_real_escape_string($conn, $_POST['name'] ?? '');
    $price            = mysqli_real_escape_string($conn, $_POST['price'] ?? '');
    $rough            = mysqli_real_escape_string($conn, $_POST['rough_price'] ?? '');
    $about            = mysqli_real_escape_string($conn, $_POST['about_product'] ?? '');
    $avliable_stock   = mysqli_real_escape_string($conn, $_POST['avliable_stock'] ?? '');
    $meta_title       = mysqli_real_escape_string($conn, $_POST['meta_title'] ?? '');
    $meta_keywords    = mysqli_real_escape_string($conn, $_POST['meta_keywords'] ?? '');
    $meta_description = mysqli_real_escape_string($conn, $_POST['meta_description'] ?? '');

    $size = "";
    if (!empty($_POST['size'])) {
        $size = mysqli_real_escape_string($conn, implode(",", $_POST['size']));
    }


    $color = "";
if (!empty($_POST['color'])) {
    $color = mysqli_real_escape_string($conn, implode(",", $_POST['color']));
}

    $image_name = "";

    if (!empty($_FILES['image']['name'])) {

        $image_name = time() . "_" . basename($_FILES['image']['name']);

        move_uploaded_file(
            $_FILES['image']['tmp_name'],
            "uploads/" . $image_name
        );
    }

    /* INSERT */
    if ($id == 0) {

        $sql = "INSERT INTO products
        (
            subcategory_id,
            trending_type,
            name,
            price,
            rough_price,
            about_product,
            meta_title,
            meta_keywords,
            meta_description,
            image,
            avliable_stock,
            size,
            color
        )
        VALUES
        (
             $subcategory_id,
            '$trending_type',
            '$name',
            '$price',
            '$rough',
            '$about',
            '$meta_title',
            '$meta_keywords',
            '$meta_description',
            '$image_name',
            '$avliable_stock',
            '$size',
            '$color'
        )";

        if (!$conn->query($sql)) {
            die("Insert Error : " . $conn->error);
        }

        $msg = "Product Added";

    } else {

        $sql = "UPDATE products SET

            subcategory_id=$subcategory_id,
            trending_type='$trending_type',
            name='$name',
            price='$price',
            rough_price='$rough',
            about_product='$about',
            meta_title='$meta_title',
            meta_keywords='$meta_keywords',
            meta_description='$meta_description',
            avliable_stock='$avliable_stock',
            size='$size',
            color='$color'";

        if (!empty($image_name)) {
            $sql .= ", image='$image_name'";
        }

        $sql .= " WHERE id='$id'";

        if (!$conn->query($sql)) {
            die("Update Error : " . $conn->error);
        }

        $msg = "Product Updated";
    }

    echo "<script>
        alert('$msg');
        window.location='add-products.php';
    </script>";
    exit;
}

/* DELETE PRODUCT */
if (isset($_GET['delete_product'])) {

    $id = (int)$_GET['delete_product'];

    $check = $conn->query("SELECT image FROM products WHERE id='$id'");

    if ($check && $check->num_rows > 0) {

        $row = $check->fetch_assoc();

        if (!empty($row['image']) && file_exists("uploads/" . $row['image'])) {
            unlink("uploads/" . $row['image']);
        }
    }

    if (!$conn->query("DELETE FROM products WHERE id='$id'")) {
        die("Delete Error : " . $conn->error);
    }

    echo "<script>
        alert('Product Deleted Successfully');
        window.location='add-products.php';
    </script>";
    exit;
}


/* EDIT PRODUCT */
$edit_data = [];

if (isset($_GET['edit_product'])) {

    $id = (int)$_GET['edit_product'];

    $result = $conn->query("SELECT * FROM products WHERE id='$id'");

    if (!$result) {
        die("Edit Error : " . $conn->error);
    }

    if ($result->num_rows > 0) {
        $edit_data = $result->fetch_assoc();
    }
}


/* FETCH PRODUCTS */
$products = $conn->query("
    SELECT
        p.*,
        s.subcategory_name
    FROM products p
    LEFT JOIN subcategories s
        ON p.subcategory_id = s.id
    ORDER BY p.id DESC
");

if (!$products) {
    die("Products Fetch Error : " . $conn->error);
}


/* FETCH ALL SUBCATEGORIES FOR DROPDOWN */
$subcategories = $conn->query("
    SELECT *
    FROM subcategories
    ORDER BY subcategory_name ASC
");

if (!$subcategories) {
    die("Subcategory Fetch Error : " . $conn->error);
} 
?>





            <!DOCTYPE html>
            <html>

            <head>
                <title>Product Management</title>
                <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css">
                <script src="https://cdn.ckeditor.com/4.22.1/standard/ckeditor.js"></script>
            </head>

            <body>
                <div class="container mt-4">
                    <div class="card mb-4">
                        <div class="card-header text-white" style="background:#000;">
                            <h4>Add / Edit Product</h4>
                        </div>
                        <div class="card-body">
                            <form method="POST" enctype="multipart/form-data" class="row g-3">
                                <input type="hidden" name="id" value="<?= $edit_data['id'] ?? '' ?>">

                                <!-- SUBCATEGORY -->
                                <div class="col-md-4">
                                    <label>Category (Optional) <span class="text-danger"> </span></label>
                                    <select name="subcategory_id" class="form-control"  >
                                        <option value="">Select Category</option>
                                        <?php while ($s = $subcategories->fetch_assoc()) {
                                            $sel = (isset($edit_data['subcategory_id']) && $edit_data['subcategory_id'] == $s['id']) ? "selected" : "";
                                        ?>
                                            <option value="<?= $s['id'] ?>" <?= $sel ?>><?= $s['subcategory_name'] ?></option>
                                        <?php } ?>
                                    </select>
                                </div>

                                <!-- TRENDING TYPE -->
                                <div class="col-md-4">
                                    <label>Trending Type</label>

                                    <?php
                                    $types = [
                                        "Bestseller",
                                        "New Arrival",
                                        "Trending",
                                        "Top Rated",
                                        "Limited Offer",
                                        "Hot Deal",
                                        "Best Value",
                                        "Customer Favorite",
                                        "Limited Stock",
                                        "Trending Now"
                                    ];
                                    ?>

                                    <select name="trending_type" class="form-control">
                                        <option value="">Select Trending Type</option>

                                        <?php foreach ($types as $type) { ?>
                                            <option value="<?= $type ?>"
                                                <?= (isset($edit_data['trending_type']) && $edit_data['trending_type'] == $type) ? 'selected' : '' ?>>
                                                <?= $type ?>
                                            </option>
                                        <?php } ?>

                                    </select>
                                </div>

                                <!-- PRODUCT NAME -->
                                <div class="col-md-4">
                                    <label>Product Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control"
                                        value="<?= $edit_data['name'] ?? '' ?>" required>
                                </div>

                                <div class="col-md-4">
                                    <label>Price</label>
                                    <input type="number" name="price" class="form-control"
                                        value="<?= $edit_data['price'] ?? '' ?>">
                                </div>

                                <div class="col-md-4">
                                    <label>MRP Rate (Optional)</label>
                                    <input type="number" name="rough_price" class="form-control"
                                        value="<?= $edit_data['rough_price'] ?? '' ?>">
                                </div>

                                <div class="col-md-4">
                                    <label>Image</label>
                                    <input type="file" name="image" class="form-control">
                                    <?php if (!empty($edit_data['image'])) { ?>
                                        <br><img src="uploads/<?= $edit_data['image'] ?>" width="80">
                                    <?php } ?>
                                </div>

                              <?php
$selectedSizes = [];

if (!empty($edit_data['size'])) {
    $selectedSizes = array_map(
        'trim',
        explode(',', $edit_data['size'])
    );
}
?>

<div class="col-12">

    <label class="form-label fw-bold mb-3">
        <i class="bi bi-rulers"></i> Clothing Sizes
    </label>

    <div class="row g-2">

        <?php foreach ($sizes as $size) { ?>

            <div class="col-6 col-md-3 col-lg-2">

                <input
                    type="checkbox"
                    class="btn-check"
                    name="size[]"
                    value="<?= htmlspecialchars($size['name']) ?>"
                    id="size<?= $size['id'] ?>"
                    autocomplete="off"
                    <?= in_array(
                        $size['name'],
                        $selectedSizes
                    ) ? "checked" : "" ?>
                >

                <label
                    class="btn btn-outline-primary w-100 py-2"
                    for="size<?= $size['id'] ?>"
                >
                    <?= htmlspecialchars($size['name']) ?>
                </label>

            </div>

        <?php } ?>

    </div>

    <small class="text-muted">
        Select one or more available sizes.
    </small>

</div>


                               <?php
$selectedColors = [];

if (!empty($edit_data['color'])) {
    $selectedColors = array_map(
        'trim',
        explode(',', $edit_data['color'])
    );
}
?>

<div class="col-12 mt-3">

    <label class="form-label fw-bold mb-3">
        Available Colors
    </label>

    <div class="row g-2">

        <?php foreach ($colors as $color) { ?>

            <div class="col-6 col-md-3 col-lg-2">

                <input
                    type="checkbox"
                    class="btn-check"
                    name="color[]"
                    value="<?= htmlspecialchars($color['name']) ?>"
                    id="color<?= $color['id'] ?>"
                    autocomplete="off"
                    <?= in_array(
                        $color['name'],
                        $selectedColors
                    ) ? "checked" : "" ?>
                >

                <label
                    class="btn btn-outline-danger w-100 py-2"
                    for="color<?= $color['id'] ?>"
                >
                    <?= htmlspecialchars($color['name']) ?>
                </label>

            </div>

        <?php } ?>

    </div>

    <small class="text-muted">
        Select one or more available colors.
    </small>

</div>
                                <!-- META TITLE -->
                                <div class="col-md-4">
                                    <label>Meta Title</label>
                                    <textarea
                                        name="meta_title"
                                        class="form-control"
                                        rows="1"><?= $edit_data['meta_title'] ?? '' ?></textarea>
                                </div>

                                <!-- META KEYWORDS -->
                                <div class="col-md-4">
                                    <label>Meta Keywords</label>
                                    <textarea
                                        name="meta_keywords"
                                        class="form-control"
                                        rows="1"><?= $edit_data['meta_keywords'] ?? '' ?></textarea>
                                </div>

                                <!-- META DESCRIPTION -->
                                <div class="col-md-4">
                                    <label>Meta Description</label>
                                    <textarea
                                        name="meta_description"
                                        class="form-control"
                                        rows="1"><?= $edit_data['meta_description'] ?? '' ?></textarea>
                                </div>

                                <div class="col-md-12">
                                    <label>About Product</label>
                                    <textarea name="about_product" id="about_product" class="form-control"><?= $edit_data['about_product'] ?? '' ?></textarea>
                                </div>

                                <div class="col-md-12 text-end">
                                    <button class="btn btn-success" name="save_product">
                                        Save Product
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- PRODUCT LIST -->
                    <div class="card">
                        <div class="card-header text-white" style="background:#f0ced3;">
                            <h4>Products List</h4>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <tr>
                                    <th>Category</th>
                                    <th>Trending Type</th>
                                    <th>Name</th>
                                    <th>Price</th>
                                    <th>Image</th>
                                    <th width="220">Action</th>
                                </tr>
                                <?php while ($p = $products->fetch_assoc()) { ?>
                                    <tr>
                                        <td><?= $p['subcategory_name'] ?></td>
                                        <td><?= $p['trending_type'] ?></td>
                                        <td><?= $p['name'] ?></td>
                                        <td><?= $p['price'] ?></td>
                                        <td>
                                            <?php if ($p['image']) { ?>
                                                <img src="uploads/<?= $p['image'] ?>" width="60">
                                            <?php } ?>
                                        </td>
                                        <td>
                                            <a href="?edit_product=<?= $p['id'] ?>" class="btn btn-primary btn-sm">Edit</a>
                                            <a href="?delete_product=<?= $p['id'] ?>" onclick="return confirm('Delete product?')" class="btn btn-danger btn-sm">Delete</a>
                                            <a href="variants_list.php?product_id=<?= $p['id'] ?>" class="btn btn-warning btn-sm">Variants</a>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </table>
                        </div>
                    </div>
                </div>

                <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
                <script>
                    CKEDITOR.replace('about_product');
                </script>
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