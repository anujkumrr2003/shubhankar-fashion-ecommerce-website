
<?php include('menu.php') ?>

<div class="row">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-header">
                <h4 class="header-title">Admin Panel</h4>
            </div>


<?php
include 'config.php';

// Fetch categories for dropdown
$cats = $conn->query("SELECT id, category_name FROM categories ORDER BY category_name ASC");

// Insert / Update
if (isset($_POST['save_subcategory'])) {
    $id = $_POST['id'];
    $category_id = $_POST['category_id'];
    $subcategory_name = $_POST['subcategory_name'];
    $status = $_POST['status'];
    $show_home = $_POST['show_home'];

    if ($id == "") {
        $conn->query("INSERT INTO subcategories(category_id, subcategory_name, status, show_home)
                      VALUES('$category_id', '$subcategory_name', '$status', '$show_home')");
    } else {
        $conn->query("UPDATE subcategories
                      SET category_id='$category_id', subcategory_name='$subcategory_name',
                          status='$status', show_home='$show_home'
                      WHERE id=$id");
    }
}

// DELETE
if (isset($_GET['delete'])) {
    $conn->query("DELETE FROM subcategories WHERE id=" . $_GET['delete']);
}

$records = $conn->query("SELECT subcategories.*, categories.category_name 
                         FROM subcategories 
                         LEFT JOIN categories ON subcategories.category_id = categories.id
                         ORDER BY subcategories.id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Subcategory Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">

    <div class="card shadow-sm">
        <div class="card-header bg-secondary text-white">
            <h4 class="m-0">Manage Subcategories</h4>
        </div>

        <div class="card-body">

            <form method="POST" class="row g-3">
                <input type="hidden" name="id" id="edit_id">

                <div class="col-md-4">
                    <label class="form-label">Category*</label>
                    <select name="category_id" id="category_id" class="form-control" required>
                        <option value="">Select Category</option>
                        <?php while ($c = $cats->fetch_assoc()) { ?>
                            <option value="<?=$c['id']?>"><?=$c['category_name']?></option>
                        <?php } ?>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Subcategory Name*</label>
                    <input type="text" name="subcategory_name" id="subcategory_name" class="form-control" required>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="status" id="status" class="form-control">
                        <option value="Active">Active</option>
                        <option value="Inactive">Inactive</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Show Home</label>
                    <select name="show_home" id="show_home" class="form-control">
                        <option value="Inactive">Inactive</option>
                        <option value="Active">Active</option>
                    </select>
                </div>

                <div class="col-md-12 text-end">
                    <button type="submit" name="save_subcategory" class="btn btn-success">Save</button>
                </div>
            </form>

            <hr>

            <table class="table table-bordered table-striped">
                <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>Category</th>
                    <th>Subcategory</th>
                    <th>Status</th>
                    <th>Show Home</th>
                    <th>Actions</th>
                </tr>
                </thead>

                <tbody>
                <?php while ($row = $records->fetch_assoc()) { ?>
                    <tr>
                        <td><?=$row['id']?></td>
                        <td><?=$row['category_name']?></td>
                        <td><?=$row['subcategory_name']?></td>
                        <td><?=$row['status']?></td>
                        <td><?=$row['show_home']?></td>
                        <td>
                            <button class="btn btn-warning btn-sm"
                                onclick="editSubcategory(<?=$row['id']?>, <?=$row['category_id']?>, '<?=$row['subcategory_name']?>', '<?=$row['status']?>', '<?=$row['show_home']?>')">
                                Edit
                            </button>

                            <a href="?delete=<?=$row['id']?>" class="btn btn-danger btn-sm"
                               onclick="return confirm('Delete this subcategory?')">Delete</a>
                        </td>
                    </tr>
                <?php } ?>
                </tbody>

            </table>
        </div>
    </div>
</div>

<script>
function editSubcategory(id, cat_id, name, status, show_home) {
    document.getElementById("edit_id").value = id;
    document.getElementById("category_id").value = cat_id;
    document.getElementById("subcategory_name").value = name;
    document.getElementById("status").value = status;
    document.getElementById("show_home").value = show_home;
}
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




            </body>

            </html>