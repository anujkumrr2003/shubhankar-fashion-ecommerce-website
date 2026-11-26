<?php include('menu.php') ?>

<div class="row">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-header">
                <h3 class="header-title">Add Category </h3>
            </div>
          <?php
include "config.php";

$edit = null;
$upload = "uploads/";

if (!is_dir($upload)) {
    mkdir($upload);
}

/* SAVE / UPDATE */
if (isset($_POST['save'])) {

    $id = $_POST['id'] ?? '';
    $product = trim($_POST['product'] ?? '');
    $status = $_POST['status'] ?? 'Active';

    $image = "";

    /* IMAGE UPLOAD */
    if (!empty($_FILES['image']['name'])) {
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $image = time() . rand(1000, 9999) . "." . $ext;
        move_uploaded_file($_FILES['image']['tmp_name'], $upload . $image);
    }

    /* INSERT / UPDATE PRODUCT */
    if ($product != "") {
        if ($id == "") {
            // New Product
            $conn->query("INSERT INTO subcategories (category_id, subcategory_name, image, status)
                          VALUES (1, '$product', '$image', '$status')");
            echo "<script>alert('Product Added Successfully');location='category.php';</script>";
        } else {
            // Update Product
            $query = "UPDATE subcategories SET 
                      subcategory_name='$product',
                      status='$status'";
            
            if ($image != "") {
                $query .= ", image='$image'";
            }
            
            $query .= " WHERE id=$id";
            $conn->query($query);
            
            echo "<script>alert('Product Updated Successfully');location='category.php';</script>";
        }
    } else {
        echo "<script>alert('Please enter Product Name');</script>";
    }
}

/* DELETE */
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    
    // Optional: Delete old image file
    $old = $conn->query("SELECT image FROM subcategories WHERE id=$id")->fetch_assoc();
    if ($old && $old['image'] && file_exists("uploads/" . $old['image'])) {
        unlink("uploads/" . $old['image']);
    }
    
    $conn->query("DELETE FROM subcategories WHERE id=$id");
    echo "<script>alert('Deleted Successfully');location='category.php';</script>";
}

/* EDIT */
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $edit = $conn->query("SELECT * FROM subcategories WHERE id = $id")->fetch_assoc();
}

/* FETCH ALL PRODUCTS */
$data = $conn->query("SELECT * FROM subcategories ORDER BY subcategory_name ASC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Category</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <div class="card">
            <div class="card-header bg-dark text-white">
                <h4><?= $edit ? "Edit Category" : "Add New Category" ?></h4>
            </div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="id" value="<?= $edit['id'] ?? '' ?>">

                    <div class="row align-items-end">
                        <div class="col-md-3">
                            <label>Add Category <span class="text-danger">*</span></label>
                            <input type="text"
                                   name="product"
                                   class="form-control"
                                   value="<?= $edit['subcategory_name'] ?? '' ?>"
                                   required>
                        </div>

                        <div class="col-md-2">
                            <label>Status</label>
                            <select name="status" class="form-control">
                                <option value="Active" <?= isset($edit['status']) && $edit['status']=="Active" ? "selected" : "" ?>>Active</option>
                                <option value="Inactive" <?= isset($edit['status']) && $edit['status']=="Inactive" ? "selected" : "" ?>>Inactive</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label>Category Image (Optional)</label>
                            <input type="file" name="image" class="form-control">
                            <?php if ($edit && $edit['image']) { ?>
                                <img src="uploads/<?= $edit['image'] ?>" width="80" class="mt-2">
                            <?php } ?>
                        </div>

                        <div class="col-md-3">
                            <button class="btn btn-success w-100" name="save">
                                <?= $edit ? "Update" : "Add Product" ?>
                            </button>
                        </div>
                    </div>
                </form>

                <hr>

                <h5 class="mb-3">All Category</h5>
                <table class="table table-bordered">
                    <tr class="table-dark">
                        <th>Category Name</th>
                        <th>Status</th>
                        <th>Image</th>
                        <th>Action</th>
                    </tr>

                    <?php while ($row = $data->fetch_assoc()) { ?>
                        <tr>
                            <td><?= $row['subcategory_name'] ?></td>
                            <td>
                                <span class="badge bg-<?= $row['status'] == 'Active' ? 'success' : 'secondary' ?>">
                                    <?= $row['status'] ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($row['image']) { ?>
                                    <img src="uploads/<?= $row['image'] ?>" width="60">
                                <?php } else { ?>
                                    <small class="text-muted">No Image</small>
                                <?php } ?>
                            </td>
                            <td>
                                <a href="?edit=<?= $row['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                                <a href="?delete=<?= $row['id'] ?>"
                                   onclick="return confirm('Delete this product?')"
                                   class="btn btn-danger btn-sm">Delete</a>
                            </td>
                        </tr>
                    <?php } ?>
                </table>
            </div>
        </div>
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