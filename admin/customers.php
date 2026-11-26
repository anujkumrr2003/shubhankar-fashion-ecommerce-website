
<?php include('menu.php') ?>

<div class="row">
  <div class="col-xl-12">
    <div class="card">
      <div class="card-header">
        <!-- <h4 class="header-title">Add Social Media</h4> -->
      </div>


<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">



<?php
include 'config.php';


// Fetch all customers
$result = $conn->query("SELECT * FROM user ORDER BY id DESC");
?>

<div class="container mt-3">
    <h3 class="mb-3">All Customers</h3>

    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            
            <thead class="table-dark">
                <tr>
                    <!-- <th>ID</th> -->
                    <th>Profile</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Joined On</th>
                </tr>
            </thead>

            <tbody>
                <?php while ($row = $result->fetch_assoc()) { ?>
                <tr>
                    <!-- <td><?= $row['id'] ?></td> -->

                   <td style="padding: 10px;">
    <?php if (!empty($row['profile_image'])) { ?>
        <img src="http://localhost/miaby/uploads/<?= $row['profile_image'] ?>"
             style="width:40px; height:40px; border-radius:50%; object-fit:cover;">
    <?php } else { ?>
        <i class="fa-solid fa-user"
           style="font-size:32px; color:#555; width:40px; height:40px; display:flex; align-items:center; justify-content:center; border:1px solid #000; border-radius:50%; padding:10px;" ></i>
    <?php } ?>
</td>


                    <td><?= htmlspecialchars($row['full_name']) ?></td>

                    <td><?= htmlspecialchars($row['email']) ?></td>

                    <td><?= date("d M Y", strtotime($row['created_at'])) ?></td>
                </tr>
                <?php } ?>
            </tbody>

        </table>
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