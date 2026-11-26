<?php include('menu.php') ?>

<div class="row">
  <div class="col-xl-12">
    <div class="card">
      <div class="card-header">
        <h4 class="header-title">Add Tag Manager</h4>
      </div>


<?php
include 'config.php';

// --- INSERT RECORD ---
if (isset($_POST['add'])) {
    $head_tag = $conn->real_escape_string($_POST['head_tag_manager']);
    $body_tag = $conn->real_escape_string($_POST['body_tag_manager']);

    $sql = "INSERT INTO tag_manager (head_tag_manager, body_tag_manager) VALUES ('$head_tag', '$body_tag')";
    if ($conn->query($sql)) {
        echo "<script>alert('Record added successfully!');</script>";
    } else {
        echo "<script>alert('Error: " . $conn->error . "');</script>";
    }
}

// --- UPDATE RECORD ---
if (isset($_POST['update'])) {
    $id = (int)$_POST['id'];
    $head_tag = $conn->real_escape_string($_POST['head_tag_manager']);
    $body_tag = $conn->real_escape_string($_POST['body_tag_manager']);

    $sql = "UPDATE tag_manager SET head_tag_manager='$head_tag', body_tag_manager='$body_tag' WHERE id=$id";
    if ($conn->query($sql)) {
        echo "<script>alert('Record updated successfully!');</script>";
    } else {
        echo "<script>alert('Error: " . $conn->error . "');</script>";
    }
}

// --- DELETE RECORD ---
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM tag_manager WHERE id=$id");
    echo "<script>alert('Record deleted successfully!'); window.location='tag-manager.php';</script>";
    exit;
}

// --- FETCH RECORDS ---
$result = $conn->query("SELECT * FROM tag_manager ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Tag Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <h3 class="mb-4 text-center">Head & Body Tag Manager</h3>

    <!-- ADD/UPDATE FORM -->
    <form method="post" class="p-4 bg-white shadow rounded">
        <input type="hidden" name="id" value="<?= isset($_GET['edit']) ? (int)$_GET['edit'] : '' ?>">

        <div class="mb-3">
            <label class="form-label">Head Tag Manager</label>
            <textarea name="head_tag_manager" class="form-control" rows="4" required><?php
                if (isset($_GET['edit'])) {
                    $id = (int)$_GET['edit'];
                    $edit = $conn->query("SELECT * FROM tag_manager WHERE id=$id")->fetch_assoc();
                    echo htmlspecialchars($edit['head_tag_manager']);
                }
            ?></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Body Tag Manager</label>
            <textarea name="body_tag_manager" class="form-control" rows="4" required><?php
                if (isset($_GET['edit'])) echo htmlspecialchars($edit['body_tag_manager']);
            ?></textarea>
        </div>

        <button type="submit" name="<?= isset($_GET['edit']) ? 'update' : 'add' ?>" class="btn btn-primary">
            <?= isset($_GET['edit']) ? 'Update Record' : 'Add Record' ?>
        </button>
        <?php if (isset($_GET['edit'])): ?>
            <a href="tag-manager.php" class="btn btn-secondary ms-2">Cancel</a>
        <?php endif; ?>
    </form>

    <!-- RECORD LIST -->
    <div class="mt-5">
        <h5 class="mb-3">Saved Tag Records</h5>
        <table class="table table-bordered table-striped align-middle">
            <thead class="table-dark">
                <tr>
                   
                    <th>Head Tag Manager</th>
                    <th>Body Tag Manager</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            
                            <td><pre><?= htmlspecialchars($row['head_tag_manager']) ?></pre></td>
                            <td><pre><?= htmlspecialchars($row['body_tag_manager']) ?></pre></td>
                            <td>
                                <a href="?edit=<?= $row['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                                <a href="?delete=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="4" class="text-center">No records found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>




      

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
