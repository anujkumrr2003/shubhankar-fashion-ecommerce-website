

<?php include('menu.php') ?>

<div class="row">
  <div class="col-xl-12">
    <div class="card">
      <div class="card-header" style="background-color: #ffcdc1">
        <h3 class="header-title" style="color: #fff" >Assign Main Category to Category</h3>
      </div>
      

<?php
include "config.php";

/* ================= DELETE ================= */
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Delete by ID (optional, agar individual delete chahiye)
    if (isset($_POST['delete_id'])) {
        $delete_id = intval($_POST['delete_id']);
        $stmt = $conn->prepare("DELETE FROM category_select WHERE id = ?");
        $stmt->bind_param("i", $delete_id);
        $stmt->execute();
        $stmt->close();
    }

    // Delete all categories for a developer
    if (isset($_POST['delete_name'])) {
        $delete_name = trim($_POST['delete_name']);
        $stmt = $conn->prepare("DELETE FROM category_select WHERE developer_name = ?");
        $stmt->bind_param("s", $delete_name);
        $stmt->execute();
        $stmt->close();
    }
}

/* ================= INSERT ================= */
if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST['developer_name'], $_POST['category_dropdown'])
) {
    $developer_name = trim($_POST['developer_name']);
    $category_name  = trim($_POST['category_dropdown']);

    if ($developer_name !== "" && $category_name !== "") {
        $stmt = $conn->prepare(
            "INSERT INTO category_select (developer_name, category_name) VALUES (?, ?)"
        );
        $stmt->bind_param("ss", $developer_name, $category_name);
        $stmt->execute();
        $stmt->close();
    }
}

/* ================= FETCH CATEGORY DROPDOWN ================= */
$result_categories = $conn->query("SELECT category_name FROM categories");

/* ================= FETCH LIST ================= */
$result = $conn->query("
    SELECT developer_name, GROUP_CONCAT(category_name SEPARATOR ', ') AS categories
    FROM category_select
    GROUP BY developer_name
    ORDER BY developer_name
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Category Select</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5">

    <!-- <h3 class="text-primary mb-4">Assign Main Category to Category</h3> -->

    <!-- FORM -->
    <form method="post" class="card p-3 mb-4">

        <div class="mb-3">
            <label class="form-label">Main Category</label>
            <input type="text" name="developer_name" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Select Category</label>
            <select name="category_dropdown" class="form-select" required>
                <option value="">-- Select Category --</option>
                <?php while ($row = $result_categories->fetch_assoc()): ?>
                    <option value="<?= htmlspecialchars($row['category_name']) ?>">
                        <?= htmlspecialchars($row['category_name']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <button class="btn btn-success">Add</button>
    </form>

    <!-- LIST -->
    <!-- <h4>Category Mapping List</h4> -->

    <?php
include "config.php";

/* ================= POST HANDLING ================= */
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    /* DELETE ENTIRE DEVELOPER */
    if (isset($_POST['delete_name'])) {
        $name = $_POST['delete_name'];
        $stmt = $conn->prepare("DELETE FROM category_select WHERE developer_name=?");
        $stmt->bind_param("s", $name);
        $stmt->execute();
        $stmt->close();
    }

    /* DELETE SINGLE PROPERTY */
    if (isset($_POST['delete_property'], $_POST['developer_name'])) {
        $stmt = $conn->prepare("DELETE FROM category_select WHERE developer_name=? AND category_name=?");
        $stmt->bind_param("ss", $_POST['developer_name'], $_POST['delete_property']);
        $stmt->execute();
        $stmt->close();
    }

    /* UPDATE DEVELOPER NAME */
    if (isset($_POST['old_name'], $_POST['new_name'])) {
        $stmt = $conn->prepare("UPDATE category_select SET developer_name=? WHERE developer_name=?");
        $stmt->bind_param("ss", $_POST['new_name'], $_POST['old_name']);
        $stmt->execute();
        $stmt->close();
    }

    /* ADD PROPERTY FROM DROPDOWN */
    if (isset($_POST['developer_name'], $_POST['property_dropdown'])) {
        $dev = $_POST['developer_name'];
        $prop = $_POST['property_dropdown'];

        // prevent duplicate
        $check = $conn->prepare("SELECT id FROM category_select WHERE developer_name=? AND category_name=?");
        $check->bind_param("ss", $dev, $prop);
        $check->execute();
        $check->store_result();

        if ($check->num_rows == 0) {
            $stmt = $conn->prepare("INSERT INTO category_select (developer_name, category_name) VALUES (?,?)");
            $stmt->bind_param("ss", $dev, $prop);
            $stmt->execute();
            $stmt->close();
        }
        $check->close();
    }
}

/* ================= FETCH DATA ================= */
$developers = $conn->query("
    SELECT developer_name, GROUP_CONCAT(category_name SEPARATOR ',') AS categories
    FROM category_select
    GROUP BY developer_name
    ORDER BY developer_name
");

$allCategories = $conn->query("SELECT category_name FROM categories");
$categoryList = [];
while ($c = $allCategories->fetch_assoc()) {
    $categoryList[] = $c['category_name'];
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Main Category Manager</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5">
<h4 class="mb-3 text-success">Main Category List</h4>

<table class="table table-bordered align-middle">
<thead class="table-light">
<tr>
    <th>Main Category</th>
    <th>Properties</th>
    <th>Action</th>
</tr>
</thead>
<tbody>

<?php while ($row = $developers->fetch_assoc()):
    $props = explode(',', $row['categories']);
?>
<tr>

<!-- DEVELOPER UPDATE -->
<td>
<form method="post" class="d-flex gap-2">
    <input type="hidden" name="old_name" value="<?= htmlspecialchars($row['developer_name']) ?>">
    <input type="text" name="new_name" value="<?= htmlspecialchars($row['developer_name']) ?>" class="form-control form-control-sm" required>
    <button class="btn btn-success btn-sm">Save</button>
</form>
</td>

<!-- PROPERTY LIST -->
<td>
<?php foreach ($props as $p): ?>
<form method="post" class="d-flex gap-2 mb-1">
    <input type="hidden" name="developer_name" value="<?= htmlspecialchars($row['developer_name']) ?>">
    <input type="hidden" name="delete_property" value="<?= htmlspecialchars($p) ?>">
    <input type="text" class="form-control form-control-sm" value="<?= htmlspecialchars($p) ?>" readonly>
    <button class="btn btn-danger btn-sm">X</button>
</form>
<?php endforeach; ?>

<!-- ADD PROPERTY DROPDOWN -->
<form method="post" class="d-flex gap-2 mt-2">
    <input type="hidden" name="developer_name" value="<?= htmlspecialchars($row['developer_name']) ?>">
    <select name="property_dropdown" class="form-select form-select-sm" required>
        <option value="">-- Select Property --</option>
        <?php foreach ($categoryList as $cat): ?>
            <option value="<?= htmlspecialchars($cat) ?>"><?= htmlspecialchars($cat) ?></option>
        <?php endforeach; ?>
    </select>
    <button class="btn btn-primary btn-sm">Add</button>
</form>
</td>

<!-- DELETE DEVELOPER -->
<td class="text-center">
<form method="post" onsubmit="return confirm('Delete entire developer?');">
    <input type="hidden" name="delete_name" value="<?= htmlspecialchars($row['developer_name']) ?>">
    <button class="btn btn-danger btn-sm">Delete</button>
</form>
</td>

</tr>
<?php endwhile; ?>

</tbody>
</table>
</div>

</body>
</html>


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

<?php $conn->close(); ?>
