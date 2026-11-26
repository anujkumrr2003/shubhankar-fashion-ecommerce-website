<?php include('menu.php') ?>

<div class="row">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-header">
                <!-- <h4 class="header-title">Promo Codes</h4> -->
            </div>

            <?php
            include "config.php";

            $action = $_GET['action'] ?? 'list';
            $id     = intval($_GET['id'] ?? 0);

            /* -----------------------------------------
   FETCH RECORD FOR EDIT
------------------------------------------ */
            $editData = [];
            if ($action === "edit" && $id > 0) {
                $q = $conn->query("SELECT * FROM promo_codes WHERE id=$id");
                $editData = $q->fetch_assoc();
            }

            /* -----------------------------------------
   DELETE
------------------------------------------ */
            if ($action === "delete" && $id > 0) {

                $conn->query("DELETE FROM promo_codes WHERE id=$id");

                echo "<script>
            alert('Promo code deleted successfully');
            window.location.href = 'promo_form.php?action=list';
          </script>";
                exit;
            }

            /* -----------------------------------------
   ADD / UPDATE
------------------------------------------ */
            if ($_SERVER['REQUEST_METHOD'] === "POST") {

                $id     = intval($_POST['id']);
                $code   = trim($_POST['code']);
                $type   = trim($_POST['discount_type']);
                $value  = trim($_POST['discount_value']);
                $start  = trim($_POST['start_date']);
                $end    = trim($_POST['end_date']);
                $status = trim($_POST['status']); // active / inactive
                $minAmt = trim($_POST['minimum_amount']);

                if ($id == 0) {
                    // INSERT
                    $sql = "INSERT INTO promo_codes 
                (code, discount_type, discount_value, start_date, end_date, status, minimum_amount)
                VALUES ('$code', '$type', '$value', '$start', '$end', '$status', '$minAmt')";
                    $conn->query($sql);
                } else {
                    // UPDATE
                    $sql = "UPDATE promo_codes SET 
                code='$code',
                discount_type='$type',
                discount_value='$value',
                start_date='$start',
                end_date='$end',
                status='$status',
                minimum_amount='$minAmt'
                WHERE id=$id";
                    $conn->query($sql);
                }

                echo "<script>
            alert('Promo saved successfully');
            window.location.href = 'promo_form.php?action=list';
          </script>";
                exit;
            }
            ?>

            <!DOCTYPE html>
            <html>

            <head>
                <title>Promo Codes</title>
                <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
            </head>

            <body>

                <?php if ($action === "add" || $action === "edit"): ?>
                    <?php $e = $editData; ?>

                    <!-- FORM PAGE -->
                    <div class="container" style="max-width: 1200px;">
                        <h3><?= ($action === "add") ? "Add Promo Code" : "Edit Promo Code" ?></h3>

                        <form method="POST">
                            <input type="hidden" name="id" value="<?= $e['id'] ?? 0 ?>">

                            <div class="row">
                                <!-- Promo Code -->
                                <div class="col-md-3 mb-3">
                                    <label>Promo Code</label>
                                    <input type="text" name="code" class="form-control" required
                                        value="<?= $e['code'] ?? '' ?>">
                                </div>

                                <!-- Discount Type -->
                                <div class="col-md-3 mb-3">
                                    <label>Discount Type</label>
                                    <select name="discount_type" class="form-control" required>
                                        <option value="percent" <?= (!empty($e['discount_type']) && $e['discount_type'] == "percent") ? 'selected' : '' ?>>Percent</option>
                                        <option value="flat" <?= (!empty($e['discount_type']) && $e['discount_type'] == "flat") ? 'selected' : '' ?>>Flat</option>
                                    </select>
                                </div>

                                <!-- Discount Value -->
                                <div class="col-md-3 mb-3">
                                    <label>Discount Value</label>
                                    <input type="number" name="discount_value" class="form-control" required
                                        value="<?= $e['discount_value'] ?? '' ?>">
                                </div>

                                <!-- Minimum Amount -->
                                <div class="col-md-3 mb-3">
                                    <label>Minimum Purchase Amount</label>
                                    <input type="number" name="minimum_amount" class="form-control" required
                                        value="<?= $e['minimum_amount'] ?? 0 ?>">
                                </div>
                            </div>

                            <div class="row">
                                <!-- Start Date -->
                                <div class="col-md-3 mb-3">
                                    <label>Start Date</label>
                                    <input type="date" name="start_date" class="form-control" required
                                        value="<?= $e['start_date'] ?? '' ?>">
                                </div>

                                <!-- End Date -->
                                <div class="col-md-3 mb-3">
                                    <label>End Date</label>
                                    <input type="date" name="end_date" class="form-control" required
                                        value="<?= $e['end_date'] ?? '' ?>">
                                </div>

                                <!-- Status -->
                                <div class="col-md-3 mb-3">
                                    <label>Status</label>
                                    <select name="status" class="form-control" required>
                                        <option value="">Select Status</option>
                                        <option value="1" <?= (isset($e['status']) && $e['status'] == 1) ? 'selected' : '' ?>>Active</option>
                                        <option value="0" <?= (isset($e['status']) && $e['status'] == 0) ? 'selected' : '' ?>>Inactive</option>
                                    </select>
                                </div>

                                <!-- Button Column -->
                                <div class="col-md-3 d-flex align-items-end mb-3">
                                    <div>
                                        <button class="btn btn-primary">Save</button>
                                        <a href="promo_form.php?action=list" class="btn btn-secondary">Cancel</a>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>

                <?php else: ?>

                    <!-- LIST PAGE -->
                    <div class="container">
                        <div class="d-flex justify-content-between mb-3 mt-2">
                            <h5>Promo Codes</h5>
                            <a href="promo_form.php?action=add" class="btn btn-success">Add New</a>
                        </div>

                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>

                                    <th>Code</th>
                                    <th>Discount</th>
                                    <th>Min Amount</th>
                                    <th>Start</th>
                                    <th>End</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php
                                $rows = $conn->query("SELECT * FROM promo_codes ORDER BY id DESC");
                                while ($r = $rows->fetch_assoc()):
                                ?>
                                    <tr>

                                        <td><?= $r['code'] ?></td>
                                        <td><?= $r['discount_value'] ?> (<?= $r['discount_type'] ?>)</td>
                                        <td><?= $r['minimum_amount'] ?></td>
                                        <td><?= $r['start_date'] ?></td>
                                        <td><?= $r['end_date'] ?></td>
                                        <td>
                                            <?php if ($r['status'] == 1): ?>
                                                <span class="badge bg-success">Active</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Inactive</span>
                                            <?php endif; ?>
                                        </td>


                                        <td>
                                            <a href="promo_form.php?action=edit&id=<?= $r['id'] ?>" class="btn btn-sm btn-primary">Edit</a>

                                            <a href="promo_form.php?action=delete&id=<?= $r['id'] ?>"
                                                class="btn btn-sm btn-danger"
                                                onclick="return confirm('Delete this promo code?')">
                                                Delete
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>

                <?php endif; ?>
                <!-- </body>
</html> -->



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