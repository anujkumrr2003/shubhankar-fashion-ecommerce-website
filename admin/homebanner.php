    

<?php include('menu.php') ?>




<!-- end page title -->



<div class="row">
  <div class="col-xl-12">
    <div class="card">
      <div class="card-header">
        <h4 class="header-title">Upload Home Image</h4>
      </div>
      

<?php
include "config.php";

// Upload multiple images
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!empty($_FILES['banner_home']['name'][0])) {

        $uploadDir = "img/";

        // Create folder if not exists
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        foreach ($_FILES['banner_home']['name'] as $key => $name) {

            $tmpName = $_FILES['banner_home']['tmp_name'][$key];
            $error = $_FILES['banner_home']['error'][$key];

            if ($error === UPLOAD_ERR_OK) {

                // Generate unique filename
                $newFile = $uploadDir . time() . "_" . rand(1000, 9999) . "_" . basename($name);

                // Move file to folder
                if (move_uploaded_file($tmpName, $newFile)) {

                    // Insert into DB
                    $stmt = $conn->prepare("INSERT INTO banner_home (image_path) VALUES (?)");
                    $stmt->bind_param("s", $newFile);
                    $stmt->execute();
                }
            }
        }

        echo "<script>alert('Images uploaded successfully');window.location='homebanner.php';</script>";
        exit;
    }
}

// Delete image
if (isset($_GET['delete'])) {

    $id = intval($_GET['delete']);

    $result = $conn->query("SELECT image_path FROM banner_home WHERE id = $id");

    if ($result->num_rows > 0) {

        $row = $result->fetch_assoc();
        $file = $row['image_path'];

        if (file_exists($file)) {
            unlink($file);
        }

        $conn->query("DELETE FROM banner_home WHERE id = $id");
    }

    echo "<script>alert('Image deleted successfully');window.location='homebanner.php';</script>";
    exit;
}

// Fetch images
$images = $conn->query("SELECT * FROM banner_home ORDER BY id DESC");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Banner Image Upload</title>

    <style>
        form {
            width: 90%;
            max-width: 500px;
            margin: 20px auto;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }

        .grid {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            padding: 20px;
        }

        .img-box {
            width: 200px;
            background: #fff;
            padding: 10px;
            border-radius: 8px;
            border: 1px solid #ddd;
            text-align: center;
        }

        .img-box img {
            width: 100%;
            height: 120px;
            object-fit: cover;
            border-radius: 5px;
        }

        .delete-btn {
            display: inline-block;
            margin-top: 10px;
            padding: 6px 12px;
            background: #d9534f;
            color: #fff;
            border-radius: 4px;
            text-decoration: none;
        }

        .delete-btn:hover {
            background: #c9302c;
        }
    </style>
</head>
<body>

<form method="post" enctype="multipart/form-data">
    <label>Upload Multiple Banner Images</label><br>
    <input type="file" name="banner_home[]" multiple required>
    <br><br>
    <button type="submit" style="background:#495f7e; color:#fff; padding:10px 20px; border:none; border-radius:5px;">
        Upload Images
    </button>
</form>

<h2 style="text-align:center;">Uploaded Banner Images</h2>

<div class="grid">
    <?php while ($row = $images->fetch_assoc()): ?>
        <div class="img-box">
            <img src="<?php echo $row['image_path']; ?>" alt="">
            <a class="delete-btn" href="?delete=<?php echo $row['id']; ?>" onclick="return confirm('Delete this image?');">
                Delete
            </a>
        </div>
    <?php endwhile; ?>
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




</body>

</html>