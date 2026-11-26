    

<?php include('menu.php') ?>




<!-- end page title -->



<div class="row">
  <div class="col-xl-12">
    <div class="card">
      <div class="card-header">
        <h4 class="header-title">Upload Home Banner for Desktop</h4>
      </div>
      


      <?php
include "config.php";

// ================= DELETE IMAGE =================
if (isset($_GET['delete'])) {

    $id = (int)$_GET['delete'];

    $res = $conn->query("SELECT home_image FROM logo1 WHERE id='$id'");

    if ($res && $res->num_rows > 0) {

        $row = $res->fetch_assoc();

        if (!empty($row['home_image']) && file_exists($row['home_image'])) {
            unlink($row['home_image']);
        }

        $conn->query("DELETE FROM logo1 WHERE id='$id'");
    }

    echo "<script>
            alert('Image deleted successfully.');
            window.location='" . $_SERVER['PHP_SELF'] . "';
          </script>";
    exit;
}

// ================= UPLOAD MULTIPLE IMAGES =================
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if (isset($_FILES['image'])) {

        $uploadDir = "img/";

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // Delete old images
        // $result = $conn->query("SELECT home_image FROM logo1");

        // while ($row = $result->fetch_assoc()) {
        //     if (!empty($row['home_image']) && file_exists($row['home_image'])) {
        //         unlink($row['home_image']);
        //     }
        // }

        // // Delete old records
        // $conn->query("TRUNCATE TABLE logo1");

        // Upload new images
        foreach ($_FILES['image']['tmp_name'] as $key => $tmp_name) {

            if ($_FILES['image']['error'][$key] == UPLOAD_ERR_OK) {

                $ext = strtolower(pathinfo($_FILES['image']['name'][$key], PATHINFO_EXTENSION));
                $fileName = time() . "_" . $key . "." . $ext;
                $imagePath = $uploadDir . $fileName;

                if (move_uploaded_file($tmp_name, $imagePath)) {

                    $stmt = $conn->prepare("INSERT INTO logo1(home_image) VALUES(?)");
                    $stmt->bind_param("s", $imagePath);
                    $stmt->execute();
                    $stmt->close();
                }
            }
        }

        echo "<script>
                alert('Images uploaded successfully.');
                window.location='" . $_SERVER['PHP_SELF'] . "';
              </script>";
        exit;
    }
}

// ================= FETCH IMAGES =================
$result = $conn->query("SELECT id, home_image FROM logo1 ORDER BY id DESC");
?>

            <!DOCTYPE html>
            <html lang="en">

            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Upload Home Banner for Desktop</title>

                <style>
                    body {
                        font-family: Arial, sans-serif;
                        background: #f5f5f5;
                    }

                    form {
                        margin: 20px auto;
                        padding: 20px;
                        max-width: 500px;
                        background: #fff;
                        border-radius: 8px;
                        box-shadow: 0 4px 8px rgba(0, 0, 0, .1);
                    }

                    label {
                        display: block;
                        margin-bottom: 10px;
                        font-size: 16px;
                    }

                    input[type=file] {
                        width: 100%;
                        padding: 10px;
                        margin-bottom: 15px;
                    }

                    button {
                        background: #70809a;
                        color: #fff;
                        border: none;
                        padding: 10px 20px;
                        border-radius: 5px;
                        cursor: pointer;
                    }

                    button:hover {
                        background: #55657c;
                    }

                    .image-display {
                        max-width: 900px;
                        margin: 20px auto;
                        padding: 20px;
                        background: #fff;
                        border-radius: 8px;
                        box-shadow: 0 4px 8px rgba(0, 0, 0, .1);
                    }

                    .gallery {
                        display: flex;
                        flex-wrap: wrap;
                        gap: 15px;
                    }

                    .gallery img {
                        width: 200px;
                        height: 140px;
                        object-fit: cover;
                        border: 1px solid #ddd;
                        border-radius: 8px;
                    }

                    @media(max-width:768px) {

                        .gallery {
                            justify-content: center;
                        }

                        .gallery img {
                            width: 150px;
                            height: 110px;
                        }

                    }
                </style>

            </head>

            <body>

                <form method="post" enctype="multipart/form-data">

                    <label>
                        Upload Home Banner for Desktop(Select Multiple Images) <br>
                        Rendered size:	1280 × 557 px
                    </label>

                    <input type="file" name="image[]" accept="image/*" multiple required>

                    <center>
                        <button type="submit">Upload / Update</button>
                    </center>

                </form>

               <?php if ($result && $result->num_rows > 0) { ?>

<div class="image-display">
    <h3>Current Images</h3>

    <div class="gallery">

        <?php while ($row = $result->fetch_assoc()) { ?>

            <div style="text-align:center;margin:10px;">

                <img src="<?php echo $row['home_image']; ?>" alt="Banner">

                <br><br>

                <a href="?delete=<?php echo $row['id']; ?>"
                   onclick="return confirm('Delete this image?')"
                   style="background:#dc3545;color:#fff;padding:8px 15px;text-decoration:none;border-radius:5px;">
                    Delete
                </a>

            </div>

        <?php } ?>

    </div>
</div>

<?php } ?>
<?php $conn->close(); ?>
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