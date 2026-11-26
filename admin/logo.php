    

<?php include('menu.php') ?>




<!-- end page title -->



<div class="row">
  <div class="col-xl-12">
    <div class="card">
      <div class="card-header">
        <h4 class="header-title">Upload Home Image</h4>
      </div>
      



<?php
// Include the database configuration file
include "config.php";

// Handle form submission for uploading or updating the image
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
        $uploadDir = 'img/'; // Folder to store uploaded images
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true); // Create the folder if it doesn't exist
        }

        // Delete the previous image from the database and filesystem
        $result = $conn->query("SELECT home_image FROM logo1 ORDER BY id DESC LIMIT 1");
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $oldImage = $row['home_image']; // Fetch the previous image path
            if (file_exists($oldImage)) {
                unlink($oldImage); // Delete the old image file
            }
            $conn->query("DELETE FROM logo1 WHERE home_image='$oldImage'");
        }

        // Upload new image
        $imagePath = $uploadDir . basename($_FILES['image']['name']);
        if (move_uploaded_file($_FILES['image']['tmp_name'], $imagePath)) {
            // Insert new image into the database
            $stmt = $conn->prepare("INSERT INTO logo1 (home_image) VALUES (?)");
            $stmt->bind_param('s', $imagePath);
            if ($stmt->execute()) {
                echo "Image updated successfully!";
            } else {
                echo "Error updating image in the database!";
            }
        } else {
            echo "Error uploading file!";
        }
    }
}

// Fetch the latest image
$image = null;
$result = $conn->query("SELECT home_image FROM logo1 ORDER BY id DESC LIMIT 1");
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $image = $row['home_image']; // Correct field name for the image path
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> Update </title>
    <style>
         form {
            margin: 20px auto;
            padding: 20px;
            max-width: 500px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        label {
            display: block;
            font-size: 1rem;
            margin-bottom: 10px;
            color: #555;
        }

        input[type="file"] {
            display: block;
            width: 100%;
            margin-bottom: 15px;
            padding: 10px;
            font-size: 0.9rem;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #4CAF50;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #45a049;
        }

        /* Image Display Styling */
        .image-display {
            margin: 20px auto;
            padding: 20px;
            max-width: 500px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .image-display h3 {
            font-size: 1.2rem;
            color: #333;
            margin-bottom: 15px;
        }

        .image-display img {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            border: 1px solid #ddd;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            form, .image-display {
                width: 90%;
            }

            label {
                font-size: 0.9rem;
            }

            button {
                font-size: 0.9rem;
                padding: 8px 15px;
            }
        }

        @media (max-width: 480px) {
            body {
                font-size: 0.9rem;
            }

            input[type="file"] {
                font-size: 0.8rem;
            }

            button {
                font-size: 0.8rem;
            }

            .image-display h3 {
                font-size: 1rem;
            }
        }
       
    </style>
</head>
<body> <!-- Form for uploading or updating image -->
    <form action="" method="post" enctype="multipart/form-data">
        <label for="image">Upload Home Image(1MB-5MB) :</label>
        <input type="file" name="image" id="image" accept="image/*" required>
        <center><button type="submit" style="background-color: #70809a;">Upload/Update</button></center>
    </form>

    <!-- Display current image -->
    <?php if ($image): ?>
        <div class="image-display">
            <h3>Current Image:</h3>
            <img src="<?php echo $image; ?>" alt="Uploaded Image" style="width:200px !important;">
        </div>
    <?php endif; ?>
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