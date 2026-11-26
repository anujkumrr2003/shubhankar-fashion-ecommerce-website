<?php include('menu.php') ?>

<div class="row">
    <div class="col-xl-12">
        <div class="card">
            <!-- <div class="card-header">
                <h4 class="header-title">Admin Panel</h4>
            </div> -->

           <section class="admin-hero">
    <div class="admin-content">
        <h2>Welcome to <span class="brand-highlight">Bizto</span>India</h2>
        <p>Your digital workspace designed to help you manage everything with clarity and control.</p>

        <div class="admin-stats">
<?php
include 'config.php';

// Total customers
$customerCount = $conn->query("SELECT COUNT(*) AS total FROM user")
                      ->fetch_assoc()['total'];

// Total products
$productCount = $conn->query("SELECT COUNT(*) AS total FROM products")
                     ->fetch_assoc()['total'];

// Total wishlist items
$wishlistCount = $conn->query("SELECT COUNT(*) AS total FROM orders")
                      ->fetch_assoc()['total'];
?>

           <div class="dashboard-stats">

    <a href="products.php" class="stat-box">
        <h3>Products</h3>
        <span><?= $productCount ?></span>
    </a>

    <a href="customers.php" class="stat-box">
        <h3>Customers</h3>
        <span><?= $customerCount ?></span>
    </a>

    <a href="admin_orders.php" class="stat-box">
        <h3>B2C Orders</h3>
        <span><?= $wishlistCount ?></span>
    </a>

</div>
<style>
    .dashboard-stats {
    display: flex;
    gap: 20px;
    margin: 20px;
    flex-wrap: wrap;
}

.stat-box {
    flex: 1;
    min-width: 220px;
    padding: 20px;
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 3px 12px rgba(0,0,0,0.1);
    text-align: center;
    text-decoration: none;
    color: #333;
    transition: 0.2s;
}

.stat-box h3 {
    margin: 0;
    font-size: 18px;
    font-weight: 600;
}

.stat-box span {
    display: block;
    margin-top: 10px;
    font-size: 28px;
    font-weight: bold;
    color: #c62b5a;
}

.stat-box:hover {
    transform: translateY(-5px);
    box-shadow: 0 6px 18px rgba(0,0,0,0.15);
}

</style>

           

        </div>
    </div>
</section>
<style>
.admin-hero {
    background: linear-gradient(120deg, #fff7f0, #e9f1ff);
    padding: 60px 20px;
    text-align: center;
}

.admin-content h2 {
    font-size: 38px;
    font-weight: 800;
    color: #003566;
    margin-bottom: 10px;
    animation: fadeUp 0.7s ease;
}

.brand-highlight {
    color: #fa8f2bff;
    font-weight: 800;
}

.admin-content p {
    font-size: 17px;
    color: #444;
    margin-bottom: 40px;
    animation: fadeUp 1s ease;
}

.admin-stats {
    display: grid;
    gap: 20px;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    padding: 10px;
}

.stat-box {
    background: #ffffff;
    padding: 25px;
    border-radius: 12px;
    border-bottom: 4px solid #ff7a00;
    box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.08);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    animation: fadeUp 1.4s ease;
}

.stat-box:hover {
    transform: translateY(-7px);
    box-shadow: 0px 6px 18px rgba(0, 0, 0, 0.15);
}

.stat-box h3 {
    font-size: 18px;
    margin-bottom: 5px;
    color: #003566;
}

.stat-box span {
    font-size: 32px;
    font-weight: bold;
    color: #ff7a00;
}

@keyframes fadeUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>



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