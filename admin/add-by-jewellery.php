<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>

<?php include('menu.php') ?>
<div class="row">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-header">
                <h4 class="header-title">Add Developer Property</h4>
            </div>

<?php
include "config.php";

/* ============================================================
   DELETE DEVELOPER + PROPERTIES
============================================================ */
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['delete_id'])) {
    $delete_id = intval($_POST['delete_id']);

    $conn->query("DELETE FROM dev_pro WHERE developer_id = $delete_id");
    $conn->query("DELETE FROM developers WHERE id = $delete_id");
}

/* ============================================================
   REMOVE SINGLE PROPERTY
============================================================ */
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['remove_property'])) {

    $property = trim($_POST['remove_property']);
    $dev_id = intval($_POST['developer_id']);

    $stmt = $conn->prepare("DELETE FROM dev_pro WHERE developer_id = ? AND subcategory_name = ?");
    $stmt->bind_param("is", $dev_id, $property);
    $stmt->execute();
    $stmt->close();
}

/* ============================================================
   UPDATE DEVELOPER NAME
============================================================ */
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['update_name'])) {

    $dev_id = intval($_POST['developer_id']);
    $new_name = trim($_POST['new_name']);

    if (!empty($new_name)) {
        $stmt = $conn->prepare("UPDATE developers SET name = ? WHERE id = ?");
        $stmt->bind_param("si", $new_name, $dev_id);
        $stmt->execute();
        $stmt->close();
    }
}

/* ============================================================
   ADD DEVELOPER OR ASSIGN PROPERTY
============================================================ */
if (isset($_POST['property'])) {

    $property = trim($_POST['property']);
    $developer_id = trim($_POST['developer_id']);
    $new_dev = trim($_POST['new_developer']);

    if (!empty($new_dev)) {
        $stmt = $conn->prepare("INSERT INTO developers (name) VALUES (?)");
        $stmt->bind_param("s", $new_dev);
        $stmt->execute();
        $developer_id = $stmt->insert_id;
        $stmt->close();
    }

    if (!empty($developer_id) && !empty($property)) {
        $stmt = $conn->prepare("INSERT INTO dev_pro (developer_id, subcategory_name) VALUES (?, ?)");
        $stmt->bind_param("is", $developer_id, $property);
        $stmt->execute();
        $stmt->close();
    }
}

/* ============================================================
   FETCH PROPERTIES FROM CK TABLE
============================================================ */
$videos = $conn->query("SELECT DISTINCT subcategory_name FROM subcategories WHERE subcategory_name != '' ORDER BY subcategory_name ASC");

/* ============================================================
   FETCH DEVELOPERS
============================================================ */
$developers = $conn->query("SELECT * FROM developers ORDER BY name ASC");

/* ============================================================
   FETCH DEV + PROPERTY MAPPING
============================================================ */
$list = $conn->query("
    SELECT d.id, d.name, dp.subcategory_name
    FROM developers d
    LEFT JOIN dev_pro dp ON d.id = dp.developer_id
    ORDER BY d.name ASC, dp.subcategory_name ASC
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Developer & Property Mapping</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5">

    <h3 class="text-primary mb-4">Add Developer & Property</h3>

    <form method="post" class="row g-3">

        <div class="col-md-4">
            <label class="form-label">Select Developer</label>
            <select name="developer_id" class="form-select">
                <option value="">-- Select Developer --</option>
                <?php while ($d = $developers->fetch_assoc()): ?>
                    <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['name']) ?></option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="col-md-4">
            <label class="form-label">Or Add New Developer</label>
            <input type="text" name="new_developer" class="form-control" placeholder="Enter new developer">
        </div>

        <div class="col-md-4">
            <label class="form-label">Select Property</label>
            <select name="property" class="form-select" required>
                <option value="">-- Select Property --</option>
                <?php while ($v = $videos->fetch_assoc()): ?>
                    <option value="<?= htmlspecialchars($v['subcategory_name']) ?>"><?= htmlspecialchars($v['subcategory_name']) ?></option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="col-12">
            <button class="btn btn-primary">Add</button>
        </div>

    </form>

    <hr>

    <h3 class="text-success mt-4">Developer List</h3>

    <table class="table table-bordered mt-3">
        <thead class="table-secondary text-center">
            <tr>
                <th style="width:20%">Developer</th>
                <th style="width:60%">Properties</th>
                <th style="width:20%">Action</th>
            </tr>
        </thead>

        <tbody>
        <?php
        $current_dev = null;
        $props = [];

        while ($row = $list->fetch_assoc()):
            if ($current_dev !== $row['id']) {

                if ($current_dev !== null) {
                    include "row-template.php";
                }

                $current_dev = $row['id'];
                $dev_name = $row['name'];
                $props = [];
            }

            if (!empty($row['subcategory_name'])) {
                $props[] = $row['subcategory_name'];
            }

        endwhile;

        if ($current_dev !== null) {
            include "row-template.php";
        }
        ?>
        </tbody>

    </table>

</div>

</body>
</html>

<?php $conn->close(); ?>





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
