<?php include('menu.php') ?>

<div class="row">
  <div class="col-xl-12">
    <div class="card">
      <div class="card-header">
        <h4 class="header-title">Best Seller Product</h4>
      </div>

      

<?php
include "config.php";

// Add best seller
if (isset($_POST['save_best'])) {
    $product_id = intval($_POST['product_id']);

    // Check if already exists
    $check = $conn->query("SELECT * FROM best_seller WHERE product_id=$product_id");
    if ($check->num_rows == 0) {
        $conn->query("INSERT INTO best_seller (product_id) VALUES ($product_id)");
    }

   echo "<script>
        alert('Product updated successfully');
        window.location.href = 'best_seller_products.php';
      </script>";
exit;

}

// Delete best seller entry
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM best_seller WHERE id=$id");
    header("Location: best_seller_products.php");
    exit;
}

// Fetch all products
$products = $conn->query("SELECT id, category_id, subcategory_id, name FROM products ORDER BY id DESC");

// Fetch best seller list
$best_seller = $conn->query("
    SELECT bs.id, p.name 
    FROM best_seller bs 
    JOIN products p ON p.id = bs.product_id 
    ORDER BY bs.id DESC
");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Best Seller Products</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">

    <style>
        body {
            background: #f8f9fa;
        }

        .page-box {
            background: #fff;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0px 4px 12px rgba(0,0,0,0.1);
        }

        .table th, .table td {
            vertical-align: middle;
        }

        .form-control, .btn {
            border-radius: 8px;
        }

        .heading-title {
            font-weight: 700;
            font-size: 28px;
            color: #333;
        }

        /* Mobile spacing */
        @media (max-width: 768px) {
            .page-box {
                padding: 15px;
            }

            .heading-title {
                font-size: 22px;
            }
        }
    </style>
</head>

<body>

<div class="container mt-4">
    <div class="page-box">

        <h2 class="heading-title mb-4">Best Seller Product Manager</h2>

        <!-- Add Best Seller Form -->
        <form method="POST" class="row g-3 mb-4">

            <div class="col-md-6">
                <label class="fw-bold mb-1">Select Product</label>
                <select name="product_id" class="form-control" required>
                    <option value="">Select Product</option>
                    <?php while ($p = $products->fetch_assoc()) { ?>
                        <option value="<?php echo $p['id']; ?>">
                            <?php echo $p['name']; ?> 
                            (ID:<?php echo $p['id']; ?> / Cat:<?php echo $p['category_id']; ?> / Sub:<?php echo $p['subcategory_id']; ?>)
                        </option>
                    <?php } ?>
                </select>
            </div>

            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" name="save_best" class="btn btn-success w-100">
                    Add to Best Seller
                </button>
            </div>

        </form>

        <hr>

        <!-- Best Seller List -->
        <h4 class="fw-bold mb-3">Current Best Seller List</h4>

        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle">
                <thead class="table-dark">
                    <tr>
                        <th width="80">#</th>
                        <th>Product Name</th>
                        <th width="120">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($b = $best_seller->fetch_assoc()) { ?>
                        <tr>
                            <td><?php echo $b['id']; ?></td>
                            <td><?php echo $b['name']; ?></td>
                            <td>
                                <a href="best_seller_products.php?delete=<?php echo $b['id']; ?>" 
                                   onclick="return confirm('Are you sure you want to remove this?')" 
                                   class="btn btn-danger btn-sm w-100">
                                   Delete
                                </a>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>

    </div>
</div>



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
