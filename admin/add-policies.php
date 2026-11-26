<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>

<?php include('menu.php') ?>
<div class="row">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-header">
                <h4 class="header-title">Add Policy</h4>
            </div>

<?php
include 'config.php';

// Fetch existing records for edit
$edit_id = isset($_GET['edit_id']) ? intval($_GET['edit_id']) : 0;
$policy = ['confirmation_policy'=>'','refund_policy'=>'','cancellation_policy'=>'','payment_term_policy'=>''];

if($edit_id > 0){
    $res = $conn->query("SELECT * FROM policies WHERE id=$edit_id");
    if($res->num_rows > 0){
        $policy = $res->fetch_assoc();
    }
}

// Handle form submission
if(isset($_POST['save_policy'])){
    $confirmation = $conn->real_escape_string($_POST['confirmation_policy']);
    $refund       = $conn->real_escape_string($_POST['refund_policy']);
    $cancellation = $conn->real_escape_string($_POST['cancellation_policy']);
    $payment      = $conn->real_escape_string($_POST['payment_term_policy']);

    if($edit_id > 0){
        // Update
        $conn->query("UPDATE policies SET confirmation_policy='$confirmation', refund_policy='$refund', cancellation_policy='$cancellation', payment_term_policy='$payment' WHERE id=$edit_id");
        echo "<script>alert('Policy updated successfully');window.location='add-ploicies.php';</script>";
    } else {
        // Insert
        $conn->query("INSERT INTO policies (confirmation_policy, refund_policy, cancellation_policy, payment_term_policy) VALUES ('$confirmation','$refund','$cancellation','$payment')");
        echo "<script>alert('Policy saved successfully');window.location='add-ploicies.php';</script>";
    }
}

// Handle delete
if(isset($_GET['delete_id'])){
    $del_id = intval($_GET['delete_id']);
    $conn->query("DELETE FROM policies WHERE id=$del_id");
    echo "<script>alert('Policy deleted successfully');window.location='add-ploicies.php';</script>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Manage Policies</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.ckeditor.com/4.22.1/standard/ckeditor.js"></script>
    <style>
    
        .card { margin-bottom: 20px; }
        table td, table th { vertical-align: middle; }
    </style>
</head>
<body>

<div class="container">
    <div class="card shadow-sm">
        <div class="card-header text-white">
            <h4 class="mb-0"><?= $edit_id > 0 ? 'Edit Policy' : 'Add Policy' ?></h4>
        </div>
        <div class="card-body">
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Confirmation Policy</label>
                    <textarea name="confirmation_policy" class="form-control"><?= htmlspecialchars($policy['confirmation_policy']) ?></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Refund Policy</label>
                    <textarea name="refund_policy" class="form-control"><?= htmlspecialchars($policy['refund_policy']) ?></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Cancellation Policy</label>
                    <textarea name="cancellation_policy" class="form-control"><?= htmlspecialchars($policy['cancellation_policy']) ?></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Payment Term Policy</label>
                    <textarea name="payment_term_policy" class="form-control"><?= htmlspecialchars($policy['payment_term_policy']) ?></textarea>
                </div>

                <button type="submit" name="save_policy" class="btn btn-success"><?= $edit_id > 0 ? 'Update' : 'Save' ?></button>
                <?php if($edit_id > 0): ?>
                    <a href="add-ploicies.php" class="btn btn-secondary">Cancel Edit</a>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <h4 class="mt-5 mb-3">Existing Policies</h4>
    <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Confirmation</th>
                    <th>Refund</th>
                    <th>Cancellation</th>
                    <th>Payment Term</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php
            $res = $conn->query("SELECT * FROM policies ORDER BY id DESC");
            while($row = $res->fetch_assoc()):
            ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= htmlspecialchars(substr(strip_tags($row['confirmation_policy']),0,50)) ?>...</td>
                    <td><?= htmlspecialchars(substr(strip_tags($row['refund_policy']),0,50)) ?>...</td>
                    <td><?= htmlspecialchars(substr(strip_tags($row['cancellation_policy']),0,50)) ?>...</td>
                    <td><?= htmlspecialchars(substr(strip_tags($row['payment_term_policy']),0,50)) ?>...</td>
                    <td>
                        <a href="add-ploicies.php?edit_id=<?= $row['id'] ?>" class="btn btn-sm btn-primary mb-1">Edit</a>
                        <a href="add-ploicies.php?delete_id=<?= $row['id'] ?>" class="btn btn-sm btn-danger mb-1" onclick="return confirm('Are you sure?')">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    CKEDITOR.replace('confirmation_policy');
    CKEDITOR.replace('refund_policy');
    CKEDITOR.replace('cancellation_policy');
    CKEDITOR.replace('payment_term_policy');
</script>
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
