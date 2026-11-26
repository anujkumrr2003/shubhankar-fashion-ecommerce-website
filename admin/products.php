<?php include('menu.php') ?>

<div class="row">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-header">
                <!-- <h4 class="header-title">Add Social Media</h4> -->
            </div>



<?php
include "config.php";

/* DELETE PRODUCT */
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);

    $q = $conn->prepare("SELECT image FROM products WHERE id=?");
    $q->bind_param("i", $id);
    $q->execute();
    $r = $q->get_result()->fetch_assoc();

    if (!empty($r['image']) && file_exists("uploads/" . $r['image'])) {
        @unlink("uploads/" . $r['image']);
    }

    $del = $conn->prepare("DELETE FROM products WHERE id=?");
    $del->bind_param("i", $id);
    $del->execute();

    echo "<script>alert('Product Deleted Successfully');window.location='products.php';</script>";
    exit;
}


/* UPDATE PRODUCT */
if (isset($_POST['update_product'])) {

    $id             = (int) $_POST['id'];
    $name           = $_POST['name'];
    $subcategory_id = (int) $_POST['subcategory_id'];
    $price          = (float) $_POST['price'];
    $rough          = (float) $_POST['rough_price'];
    $about          = $_POST['about_product'];
    $avliable_stock = (int) $_POST['avliable_stock'];

    // subcategory_name is derived server-side from subcategory_id, never
    // trusted from a form field, so the two can never fall out of sync.
    $subNameStmt = $conn->prepare("SELECT subcategory_name FROM subcategories WHERE id=?");
    $subNameStmt->bind_param("i", $subcategory_id);
    $subNameStmt->execute();
    $subNameRow = $subNameStmt->get_result()->fetch_assoc();
    $subcategory = $subNameRow ? $subNameRow['subcategory_name'] : '';

    $image = $_FILES['image']['name'];

    if ($image != "") {

        $tmp = $_FILES['image']['tmp_name'];
        move_uploaded_file($tmp, "uploads/" . $image);

        $stmt = $conn->prepare("
UPDATE products SET
name=?,
subcategory_id=?,
subcategory_name=?,
price=?,
rough_price=?,
about_product=?,
avliable_stock=?,
image=?
WHERE id=?
");

        // s (name), i (subcategory_id), s (subcategory_name), d (price),
        // d (rough_price), s (about_product), i (avliable_stock), s (image), i (id)
        $stmt->bind_param(
            "sisddsisi",
            $name,
            $subcategory_id,
            $subcategory,
            $price,
            $rough,
            $about,
            $avliable_stock,
            $image,
            $id
        );

    } else {

        $stmt = $conn->prepare("
UPDATE products SET
name=?,
subcategory_id=?,
subcategory_name=?,
price=?,
rough_price=?,
about_product=?,
avliable_stock=?
WHERE id=?
");

        // s (name), i (subcategory_id), s (subcategory_name), d (price),
        // d (rough_price), s (about_product), i (avliable_stock), i (id)
        $stmt->bind_param(
            "sisddsii",
            $name,
            $subcategory_id,
            $subcategory,
            $price,
            $rough,
            $about,
            $avliable_stock,
            $id
        );
    }

    $stmt->execute();

    echo "<script>alert('Product Updated Successfully');window.location='products.php';</script>";
    exit;
}

?>

<!DOCTYPE html>
<html>

<head>
    <title>Products</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.ckeditor.com/4.22.1/standard/ckeditor.js"></script>
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body {
            background: #f4f6f9;
        }

        .table img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 6px;
        }
    </style>
</head>

<body>

    <div class="container py-4">

        <h3 class="text-center mb-4" style="background: #000;color:white;padding:12px;border-radius:6px;">
            Products List
        </h3>

        <?php
        // Sanitize every incoming filter/paging value from $_GET before it
        // touches SQL.
        $search         = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
        $subcategory_id = isset($_GET['subcategory']) ? (int) $_GET['subcategory'] : '';
        $limit          = isset($_GET['limit']) ? $_GET['limit'] : 20;
        $page           = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;

        // $limit is either the literal string "all" or a number -- never
        // let anything else through into the SQL LIMIT clause.
        if ($limit !== "all") {
            $limit = (int) $limit;
            if ($limit <= 0) {
                $limit = 20;
            }
        }

        $start = ($page - 1) * ($limit === "all" ? 0 : $limit);

        $where = "WHERE 1";

        if ($search !== '') {
            $where .= " AND p.name LIKE '%$search%'";
        }

        if ($subcategory_id !== '') {
            $where .= " AND p.subcategory_id='$subcategory_id'";
        }

        /* total */
        $total_q = $conn->query("
SELECT COUNT(*) as total
FROM products p
$where
");

        $total_products = $total_q->fetch_assoc()['total'];

        // IMPORTANT: this query must use the SAME "p" alias as $where above,
        // or every filtered request throws a SQL error instead of filtering.
        if ($limit === "all") {

            $query = $conn->query("
SELECT p.*
FROM products p
$where
ORDER BY p.id DESC
");
        } else {

            $query = $conn->query("
SELECT p.*
FROM products p
$where
ORDER BY p.id DESC
LIMIT $start,$limit
");
        }

        $total_pages = ($limit === "all") ? 1 : ceil($total_products / $limit);

        // All subcategories, used for both the filter dropdown and every
        // edit modal's dropdown -- fetched once up front.
        $allSubcats = $conn->query("SELECT id, subcategory_name FROM subcategories ORDER BY subcategory_name ASC");
        $subcatList = [];
        while ($sc = $allSubcats->fetch_assoc()) {
            $subcatList[] = $sc;
        }
        ?>

        <div class="row mb-4 align-items-end">

            <div class="col-md-4">
                <label class="form-label fw-semibold">
                    <i class="fa fa-search text-primary me-1"></i> Search Product
                </label>
                <input type="text" id="search" class="form-control"
                    placeholder="Search by product name..."
                    value="<?= htmlspecialchars($search) ?>">
            </div>

            <div class="col-md-4">
                <label class="form-label fw-semibold">
                    <i class="fa fa-tags text-warning me-1"></i> Sub Category
                </label>

                <select id="subcategory" class="form-select">
                    <option value="">All Subcategories</option>

                    <?php foreach ($subcatList as $sc) { ?>
                        <option value="<?= $sc['id'] ?>" <?= ($subcategory_id == $sc['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($sc['subcategory_name']) ?>
                        </option>
                    <?php } ?>
                </select>
            </div>


            <div class="col-md-2">
                <label class="form-label fw-semibold">
                    <i class="fa fa-list text-info me-1"></i> Show
                </label>

                <select id="limit" class="form-select">
                    <option value="20" <?= ($limit == 20) ? 'selected' : '' ?>>20</option>
                    <option value="50" <?= ($limit == 50) ? 'selected' : '' ?>>50</option>
                    <option value="all" <?= ($limit === "all") ? 'selected' : '' ?>>All</option>
                </select>
            </div>


            <div class="col-md-2 d-grid">
                <label class="form-label fw-semibold">&nbsp;</label>

                <button onclick="refreshFilter()" class="btn btn-dark">
                    <i class="fa fa-rotate"></i>
                </button>
            </div>

        </div>

        <h5>Total Products: <?= $total_products ?></h5>

        <div id="productTable">

            <table class="table table-bordered table-striped">

                <thead class="table-dark">
                    <tr>
                        <th>S No.</th>
                        <th>Image</th>
                        <th>Name</th>
                        <!-- <th>Sub Category</th> -->
                        <th>Price</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>

                    <?php
                    $sn = ($page - 1) * ($limit === "all" ? $total_products : $limit) + 1;

                    while ($row = $query->fetch_assoc()) {
                    ?>

                        <tr>

                            <td><?= $sn++ ?></td>

                            <td>
                                <img src="uploads/<?= htmlspecialchars($row['image']) ?>" width="60">
                            </td>

                            <td><?= htmlspecialchars($row['name']) ?></td>

                            <!-- <td><?= htmlspecialchars($row['subcategory_name']) ?></td> -->

                            <td>₹<?= number_format($row['price'], 2) ?></td>

                            <td>
                                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#edit<?= $row['id'] ?>"> <i class="fa fa-pen"></i> </button>
                                <a href="?delete=<?= $row['id'] ?>"
                                    class="btn btn-sm btn-danger"
                                    onclick="return confirm('Delete product?')">
                                    <i class="fa fa-trash"></i>
                                </a>
                            </td>

                        </tr>
                        <!-- EDIT MODAL -->
                        <div class="modal fade" id="edit<?= $row['id'] ?>">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">

                                    <form method="post" enctype="multipart/form-data">

                                        <div class="modal-header">
                                            <h5>Edit Product</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>

                                        <div class="modal-body">

                                            <input type="hidden" name="id" value="<?= $row['id'] ?>">

                                            <div class="row">

                                                <div class="col-md-4 mb-2">
                                                    <label>Product Name</label>
                                                    <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($row['name']) ?>" required>
                                                </div>

                                                <div class="col-md-4 mb-2">
                                                    <label>Sub Category</label>

                                                    <select name="subcategory_id" class="form-control" required>
                                                        <option value="">Select Sub Category</option>

                                                        <?php foreach ($subcatList as $sc) { ?>
                                                            <option value="<?= $sc['id'] ?>"
                                                                <?= ($row['subcategory_id'] == $sc['id']) ? 'selected' : '' ?>>
                                                                <?= htmlspecialchars($sc['subcategory_name']) ?>
                                                            </option>
                                                        <?php } ?>

                                                    </select>
                                                </div>

                                                <div class="col-md-4 mb-2">
                                                    <label>Price</label>
                                                    <input type="number" step="0.01" name="price" class="form-control" value="<?= $row['price'] ?>">
                                                </div>

                                                <div class="col-md-6 mb-2">
                                                    <label>Rough Price (MRP)</label>
                                                    <input type="number" step="0.01" name="rough_price" class="form-control" value="<?= $row['rough_price'] ?>">
                                                </div>

                                                <!-- <div class="col-md-4 mb-2">
                                                    <label>Available Stock</label>
                                                    <input type="number" name="avliable_stock" class="form-control" value="<?= $row['avliable_stock'] ?>">
                                                </div> -->

                                                <div class="col-md-6 mb-2">
                                                    <label>Image</label>
                                                    <input type="file" name="image" class="form-control">
                                                </div>

                                                <div class="col-md-12 mb-2">
                                                    <label>About Product</label>

                                                    <textarea name="about_product" id="editor<?= $row['id'] ?>" class="form-control"><?= htmlspecialchars($row['about_product']) ?></textarea>

                                                    <script>
                                                        CKEDITOR.replace('editor<?= $row['id'] ?>');
                                                    </script>

                                                </div>

                                            </div>

                                        </div>

                                        <div class="modal-footer">
                                            <button class="btn btn-success" name="update_product">Update</button>
                                        </div>

                                    </form>

                                </div>
                            </div>
                        </div>
                    <?php } ?>

                </tbody>
            </table>

            <?php if ($limit !== "all") { ?>

                <nav>
                    <ul class="pagination justify-content-center">

                        <?php for ($i = 1; $i <= $total_pages; $i++) { ?>

                            <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">

                                <a class="page-link"
                                    href="javascript:void(0)"
                                    onclick="loadPage(<?= $i ?>)">

                                    <?= $i ?>

                                </a>

                            </li>

                        <?php } ?>

                    </ul>
                </nav>

            <?php } ?>

        </div>


        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        <script>
            function refreshFilter() {
                $("#search").val('');
                $("#subcategory").val('');
                $("#limit").val('20');

                loadProducts(); // ajax reload
            }
        </script>

        <script>
            $(document).ready(function() {

                /* =========================
                   LOAD PRODUCTS
                ==========================*/

                function loadProducts(page = 1) {
                    let search = $("#search").val();
                    let subcategory = $("#subcategory").val();
                    let limit = $("#limit").val();

                    $.get("products.php", {
                            search: search,
                            subcategory: subcategory,
                            limit: limit,
                            page: page
                        },
                        function(data) {

                            let html = $(data).find("#productTable").html();
                            $("#productTable").html(html);

                        });
                }

                window.loadPage = function(page) {
                    loadProducts(page);
                }


                /* =========================
                   SEARCH
                ==========================*/

                $("#search").on("keyup", function() {
                    loadProducts();
                });


                /* =========================
                   SUBCATEGORY FILTER
                ==========================*/

                $("#subcategory").on("change", function() {
                    loadProducts();
                });


                /* =========================
                   LIMIT
                ==========================*/

                $("#limit").on("change", function() {
                    loadProducts();
                });

            });
        </script>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

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