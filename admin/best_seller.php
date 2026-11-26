<?php include('menu.php') ?>

<div class="row">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-header">
                <h4 class="header-title">Add Products</h4>
            </div>






            <?php
            include "config.php";

            if (isset($_POST['update'])) {

                $conn->query("UPDATE products SET best_seller='No'");

                if (isset($_POST['best'])) {
                    foreach ($_POST['best'] as $id) {
                        $conn->query("UPDATE products SET best_seller='Yes' WHERE id='$id'");
                    }
                }

                echo "<script>alert('Best Seller Updated Successfully');</script>";
            }

            $products = $conn->query("SELECT * FROM products ORDER BY id DESC");
            ?>

            <!DOCTYPE html>
            <html>

            <head>

                <title>Best Seller Products</title>

                <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

                <style>
                    body {
                        background: #f5f5f5;
                    }

                    .table thead {
                        background: #000;
                        color: white;
                    }

                    .best-card {
                        border: none;
                        box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
                        transition: 0.3s;
                    }

                    .best-card:hover {
                        transform: translateY(-5px);
                    }
                </style>

            </head>

            <body>

                <div class="container mt-5">

                    <h3 class="mb-4">Select Best Seller Products</h3>

                    <form method="post">

                        <div class="card">
                            <div class="card-body">

                                <table class="table table-bordered table-hover">

                                    <thead>
                                        <tr>
                                            <th width="80" style="color: #fff;">Select</th>
                                            <th style="color: #fff;">Product Name</th>
                                            <th width="120" style="color: #fff;">Price</th>
                                        </tr>
                                    </thead>

                                    <tbody>

                                        <?php while ($row = $products->fetch_assoc()) { ?>

                                            <tr>

                                                <td class="text-center">
                                                    <input type="checkbox"
                                                        name="best[]"
                                                        value="<?= $row['id'] ?>"
                                                        <?php if ($row['best_seller'] == "Yes") {
                                                            echo "checked";
                                                        } ?>>
                                                </td>

                                                <td><?= $row['name'] ?></td>

                                                <td>₹<?= $row['price'] ?></td>

                                            </tr>

                                        <?php } ?>

                                    </tbody>

                                </table>

                                <button class="btn btn-dark" name="update">
                                    Update Best Seller
                                </button>

                            </div>
                        </div>

                    </form>


                    <hr class="my-5">


                    <h3 class="mb-4">Best Seller Products</h3>

                    <div class="row">

                        <?php
                        $best = $conn->query("SELECT * FROM products WHERE best_seller='Yes'");
                        ?>

                        <?php while ($row = $best->fetch_assoc()) { ?>

                            <div class="col-md-3 mb-4">

                                <div class="card best-card text-center">

                                    <div class="card-body">

                                        <h5><?= $row['name'] ?></h5>

                                        <p class="text-danger fw-bold">
                                            ₹<?= $row['price'] ?>
                                        </p>

                                        <span class="badge bg-success">
                                            Best Seller
                                        </span>

                                    </div>

                                </div>

                            </div>

                        <?php } ?>

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