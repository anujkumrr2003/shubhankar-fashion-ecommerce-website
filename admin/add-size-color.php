<?php include('menu.php') ?>

<div class="row">
  <div class="col-xl-12">
    <div class="card">
      <div class="card-header">
        <h4 class="header-title">Add Size & Color</h4>
      </div>
      

 
<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

// session_start();
include "config.php";


/* =========================================================
   ADD / UPDATE
========================================================= */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id   = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $type = isset($_POST['type']) ? trim($_POST['type']) : '';
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';

    /* Validate Type */
    if ($type !== 'color' && $type !== 'size') {

        $_SESSION['error'] = "Invalid attribute type.";

        echo '<script>window.location.href="add-size-color.php";</script>';
        exit;
    }


    /* Validate Name */
    if ($name === '') {

        $_SESSION['error'] = "Please enter a name.";

        echo '<script>window.location.href="add-size-color.php";</script>';
        exit;
    }


    /* =====================================================
       UPDATE
    ===================================================== */

    if ($id > 0) {

        $stmt = $conn->prepare("
            UPDATE product_attributes
            SET type = ?, name = ?
            WHERE id = ?
        ");

        if (!$stmt) {
            die("Prepare Error: " . $conn->error);
        }

        $stmt->bind_param("ssi", $type, $name, $id);

        if ($stmt->execute()) {

            $_SESSION['success'] =
                ucfirst($type) . " updated successfully.";

        } else {

            if ($conn->errno == 1062) {
                $_SESSION['error'] =
                    ucfirst($type) . " name already exists.";
            } else {
                $_SESSION['error'] =
                    "Unable to update: " . $stmt->error;
            }
        }

        $stmt->close();

        echo '<script>window.location.href="add-size-color.php";</script>';
        exit;
    }


    /* =====================================================
       INSERT
    ===================================================== */

    else {

        $stmt = $conn->prepare("
            INSERT INTO product_attributes (type, name)
            VALUES (?, ?)
        ");

        if (!$stmt) {
            die("Prepare Error: " . $conn->error);
        }

        $stmt->bind_param("ss", $type, $name);

        if ($stmt->execute()) {

            $_SESSION['success'] =
                ucfirst($type) . " added successfully.";

        } else {

            if ($conn->errno == 1062) {
                $_SESSION['error'] =
                    ucfirst($type) . " name already exists.";
            } else {
                $_SESSION['error'] =
                    "Unable to add: " . $stmt->error;
            }
        }

        $stmt->close();

       echo '<script>window.location.href="add-size-color.php";</script>';
exit;
    }
}


/* =========================================================
   DELETE
========================================================= */

if (isset($_GET['delete'])) {

    $id = intval($_GET['delete']);

    if ($id > 0) {

        $stmt = $conn->prepare("
            DELETE FROM product_attributes
            WHERE id = ?
        ");

        if (!$stmt) {
            die("Prepare Error: " . $conn->error);
        }

        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {

            $_SESSION['success'] =
                "Attribute deleted successfully.";

        } else {

            $_SESSION['error'] =
                "Unable to delete: " . $stmt->error;
        }

        $stmt->close();
    }

    echo '<script>window.location.href="add-size-color.php";</script>';
    exit;
}


/* =========================================================
   FETCH COLORS
========================================================= */

$colors = [];

$result = $conn->query("
    SELECT id, name, created_at
    FROM product_attributes
    WHERE type = 'color'
    ORDER BY id DESC
");

if (!$result) {
    die("Color Query Error: " . $conn->error);
}

while ($row = $result->fetch_assoc()) {
    $colors[] = $row;
}


/* =========================================================
   FETCH SIZES
========================================================= */

$sizes = [];

$result = $conn->query("
    SELECT id, name, created_at
    FROM product_attributes
    WHERE type = 'size'
    ORDER BY id DESC
");

if (!$result) {
    die("Size Query Error: " . $conn->error);
}

while ($row = $result->fetch_assoc()) {
    $sizes[] = $row;
}


/* =========================================================
   EDIT DATA
========================================================= */

$editData = null;

if (isset($_GET['edit'])) {

    $editId = intval($_GET['edit']);

    if ($editId > 0) {

        $stmt = $conn->prepare("
            SELECT id, type, name
            FROM product_attributes
            WHERE id = ?
            LIMIT 1
        ");

        if (!$stmt) {
            die("Edit Prepare Error: " . $conn->error);
        }

        $stmt->bind_param("i", $editId);
        $stmt->execute();

        /* Avoid get_result() */

        $stmt->bind_result(
            $edit_id,
            $edit_type,
            $edit_name
        );

        if ($stmt->fetch()) {

            $editData = [
                'id'   => $edit_id,
                'type' => $edit_type,
                'name' => $edit_name
            ];
        }

        $stmt->close();
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>Manage Colors & Sizes</title>


<style>

/** {*/
/*    box-sizing: border-box;*/
/*}*/

/*body {*/
/*    margin: 0;*/
/*    font-family: Arial, sans-serif;*/
/*    background: #f5f6fa;*/
/*    color: #222;*/
/*}*/

.container {
    max-width: 1100px;
    margin: 40px auto;
    padding: 0 20px;
}

h1 {
    margin-bottom: 25px;
}

.card {
    background: #fff;
    padding: 25px;
    border-radius: 10px;
    margin-bottom: 25px;
    box-shadow: 0 3px 15px rgba(0,0,0,0.08);
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr auto;
    gap: 15px;
    align-items: end;
}

.form-group label {
    display: block;
    font-weight: 600;
    margin-bottom: 7px;
}

.form-group input,
.form-group select {
    width: 100%;
    padding: 11px 12px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 15px;
}

.btn {
    border: none;
    padding: 11px 20px;
    border-radius: 6px;
    cursor: pointer;
    text-decoration: none;
    display: inline-block;
    font-size: 14px;
}

.btn-primary {
    background: #111827;
    color: #fff;
}

.btn-update {
    background: #2563eb;
    color: #fff;
}

.btn-edit {
    background: #f59e0b;
    color: #fff;
}

.btn-delete {
    background: #dc2626;
    color: #fff;
}

.btn-cancel {
    background: #6b7280;
    color: #fff;
}

.alert {
    padding: 13px 15px;
    border-radius: 6px;
    margin-bottom: 20px;
}

.success {
    background: #dcfce7;
    color: #166534;
}

.error {
    background: #fee2e2;
    color: #991b1b;
}

.tables {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 25px;
}

.table-title {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}

.table-title h2 {
    margin: 0;
    font-size: 20px;
}

table {
    width: 100%;
    border-collapse: collapse;
}

th,
td {
    padding: 12px 10px;
    border-bottom: 1px solid #eee;
    text-align: left;
}

th {
    background: #f9fafb;
}

.actions {
    display: flex;
    gap: 6px;
}

.actions .btn {
    padding: 7px 10px;
    font-size: 12px;
}

.empty {
    text-align: center;
    color: #888;
    padding: 20px;
}

@media(max-width: 700px) {

    .form-row {
        grid-template-columns: 1fr;
    }

    .tables {
        grid-template-columns: 1fr;
    }

    .actions {
        flex-wrap: wrap;
    }

}

</style>

</head>


<body>

<div class="container">

    


    <!-- SUCCESS MESSAGE -->

    <?php if (isset($_SESSION['success'])): ?>

        <div class="alert success">

            <?= htmlspecialchars($_SESSION['success']); ?>

        </div>

        <?php unset($_SESSION['success']); ?>

    <?php endif; ?>


    <!-- ERROR MESSAGE -->

    <?php if (isset($_SESSION['error'])): ?>

        <div class="alert error">

            <?= htmlspecialchars($_SESSION['error']); ?>

        </div>

        <?php unset($_SESSION['error']); ?>

    <?php endif; ?>


    <!-- =====================================================
         ADD / EDIT FORM
    ====================================================== -->

    <div class="card">

        <h2>

            <?= $editData
                ? 'Edit Attribute'
                : 'Add New Attribute';
            ?>

        </h2>


        <form
            method="POST"
            action="add-size-color.php"
        >

            <input
                type="hidden"
                name="id"
                value="<?= $editData
                    ? intval($editData['id'])
                    : 0;
                ?>"
            >


            <div class="form-row">


                <!-- TYPE -->

                <div class="form-group">

                    <label>Type</label>

                    <select
                        name="type"
                        required
                    >

                        <option value="">
                            Select Type
                        </option>

                        <option
                            value="color"
                            <?= (
                                $editData &&
                                $editData['type'] === 'color'
                            )
                            ? 'selected'
                            : '';
                            ?>
                        >
                            Color
                        </option>

                        <option
                            value="size"
                            <?= (
                                $editData &&
                                $editData['type'] === 'size'
                            )
                            ? 'selected'
                            : '';
                            ?>
                        >
                            Size
                        </option>

                    </select>

                </div>


                <!-- NAME -->

                <div class="form-group">

                    <label>Name</label>

                    <input
                        type="text"
                        name="name"
                        placeholder="Enter color or size"
                        value="<?= $editData
                            ? htmlspecialchars($editData['name'])
                            : '';
                        ?>"
                        required
                    >

                </div>


                <!-- BUTTON -->

                <div>

                    <?php if ($editData): ?>

                        <button
                            type="submit"
                            class="btn btn-update"
                        >
                            Update
                        </button>

                        <a
                            href="add-size-color.php"
                            class="btn btn-cancel"
                        >
                            Cancel
                        </a>

                    <?php else: ?>

                        <button
                            type="submit"
                            class="btn btn-primary"
                        >
                            Add Attribute
                        </button>

                    <?php endif; ?>

                </div>


            </div>

        </form>

    </div>


    <!-- =====================================================
         TABLES
    ====================================================== -->

    <div class="tables">


        <!-- COLORS -->

        <div class="card">

            <div class="table-title">

                <h2>Colors</h2>

                <span>
                    <?= count($colors); ?>
                </span>

            </div>


            <?php if (count($colors) > 0): ?>

                <table>

                    <thead>

                        <tr>

                            <th>#</th>

                            <th>Color Name</th>

                            <th>Action</th>

                        </tr>

                    </thead>


                    <tbody>

                    <?php foreach (
                        $colors as $index => $color
                    ): ?>

                        <tr>

                            <td>
                                <?= $index + 1; ?>
                            </td>

                            <td>
                                <?= htmlspecialchars(
                                    $color['name']
                                ); ?>
                            </td>

                            <td>

                                <div class="actions">

                                    <a
                                        href="add-size-color.php?edit=<?= intval($color['id']); ?>"
                                        class="btn btn-edit"
                                    >
                                        Edit
                                    </a>


                                    <a
                                        href="add-size-color.php?delete=<?= intval($color['id']); ?>"
                                        class="btn btn-delete"
                                        onclick="return confirm('Are you sure you want to delete this color?');"
                                    >
                                        Delete
                                    </a>

                                </div>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                    </tbody>

                </table>

            <?php else: ?>

                <div class="empty">
                    No colors found.
                </div>

            <?php endif; ?>

        </div>


        <!-- SIZES -->

        <div class="card">

            <div class="table-title">

                <h2>Sizes</h2>

                <span>
                    <?= count($sizes); ?>
                </span>

            </div>


            <?php if (count($sizes) > 0): ?>

                <table>

                    <thead>

                        <tr>

                            <th>#</th>

                            <th>Size Name</th>

                            <th>Action</th>

                        </tr>

                    </thead>


                    <tbody>

                    <?php foreach (
                        $sizes as $index => $size
                    ): ?>

                        <tr>

                            <td>
                                <?= $index + 1; ?>
                            </td>

                            <td>
                                <?= htmlspecialchars(
                                    $size['name']
                                ); ?>
                            </td>

                            <td>

                                <div class="actions">

                                    <a
                                        href="add-size-color.php?edit=<?= intval($size['id']); ?>"
                                        class="btn btn-edit"
                                    >
                                        Edit
                                    </a>


                                    <a
                                        href="add-size-color.php?delete=<?= intval($size['id']); ?>"
                                        class="btn btn-delete"
                                        onclick="return confirm('Are you sure you want to delete this size?');"
                                    >
                                        Delete
                                    </a>

                                </div>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                    </tbody>

                </table>

            <?php else: ?>

                <div class="empty">
                    No sizes found.
                </div>

            <?php endif; ?>

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
