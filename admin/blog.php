
<?php include('menu.php') ?>

<div class="row">
  <div class="col-xl-12">
    <div class="card">
      <div class="card-header">
        <h4 class="header-title">All Enquiry Data</h4>
      </div>
      
                <?php
include 'config.php';

// Function to create a slug from the blog title
function createSlug($string) {
    return strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $string)));
}

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    // Get the data from the form
    $title = $_POST['title'];
    $subtitle = $_POST['subtitle'];
    $description = $_POST['description'];
    $meta_title = $_POST['meta_title'];
    $meta_description = $_POST['meta_description'];
    $meta_keywords = $_POST['meta_keywords'];
    $blog = createSlug($title);
    $image = $_FILES['image'];

    // Check if the image is uploaded without errors
    if (isset($image) && $image['error'] === UPLOAD_ERR_OK) {
        if ($image['size'] <= 50 * 1024 * 1024) { // Check for valid image size (<= 50MB)
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif']; // Allowed image types
            if (in_array($image['type'], $allowedTypes)) {
                $targetDir = "img/"; // Directory to save the uploaded images
                $imagePath = $targetDir . basename($image['name']);

                // Create directory if it does not exist
                if (!is_dir($targetDir)) mkdir($targetDir, 0755, true);

                // Move the uploaded image to the target directory
                if (move_uploaded_file($image['tmp_name'], $imagePath)) {
                    // Prepare the SQL statement to insert the data into the database
                    $stmt = $conn->prepare("INSERT INTO blogs (title, subtitle, description, image_path, blog, meta_description, meta_title, meta_keywords) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->bind_param("ssssssss", $title, $subtitle, $description, $imagePath, $blog, $meta_description, $meta_title, $meta_keywords);

                    // Execute the query and check for success
                    if ($stmt->execute()) {
                        error_reporting(0);
                        echo "<script>alert('Add Blog Successfully');</script>";
                        echo "<script>window.location.href = 'blog.php';</script>";
                        exit; // Stop further execution
                        
                    } else {
                        echo "<p class='text-danger'>Failed to add entry. Database error.</p>";
                    }

                    $stmt->close();
                } else {
                    echo "<p class='text-danger'>Failed to upload image to directory.</p>";
                }
            } else {
                echo "<p class='text-danger'>Invalid file type. Only JPG, PNG, and GIF are allowed.</p>";
            }
        } else {
            echo "<p class='text-danger'>Image file size exceeds 50MB.</p>";
        }
    } else {
        echo "<p class='text-danger'>Error uploading image.</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Entry</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .box { width: 100%; max-width: 600px; background-color: #f9f9f9; border: 1px solid #ccc; border-radius: 5px; padding: 16px; margin: 0 auto; }
        .ck-editor__editable[role="textbox"] { min-height: 350px; }
        .error { color: green; font-size: 20px; }
        
    </style>
</head>
<body>
    <div class="container mt-5">
        <h2>Add New Entry</h2>

        <!-- Form for adding blog post -->
        <form action="" method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="title">Title:</label>
                <input type="text" class="form-control" id="title" name="title" required>
            </div>
            <!-- <div class="form-group">
                <label for="subtitle">Subtitle:</label>
                <input type="text" class="form-control" id="subtitle" name="subtitle" required>
            </div> -->
            <div class="form-group">
                <label for="image">Image:</label>
                <input type="file" class="form-control" id="image" name="image" accept="image/*" required>
            </div>
            <div class="form-group">
                <label for="description">Description:</label>
                <textarea id="description" name="description" class="form-control"></textarea>
            </div>
            <div class="form-group">
                <label for="meta-title">Meta Title:</label>
                <input type="text" class="form-control" id="meta-title" name="meta_title" required>
            </div>
            <div class="form-group">
                <label for="meta-description">Meta Description:</label>
                <textarea id="meta-description" name="meta_description" class="form-control"></textarea>
            </div>
            <div class="form-group">
                <label for="meta-description">Meta Keyword:</label>
                <textarea id="meta-keyword" name="meta_keywords" class="form-control"></textarea>
            </div>
            <div class="form-group">
                <input type="submit" name="submit" value="Save" class="btn btn-primary mt-2">
            </div>
        </form>

        <!-- CKEditor for description -->
        <script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>
    <script>
        ClassicEditor
            .create(document.querySelector('#description'))
            .catch(error => {
                console.error(error);
            });
    </script>

        <!-- Display existing posts -->
        <h2 class="mt-5">Existing Posts</h2>
        <div class="row">
    <?php
    $result = $conn->query("SELECT id, title, description, image_path, blog, meta_description, meta_title, meta_keywords FROM blogs");

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo '<div class="col-md-4 mb-4">';
            echo '<div class="card">';
            // echo '<img src="' . htmlspecialchars($row['image_path']) . '" class="card-img-top" alt="' . htmlspecialchars($row['title']) . '">';
            echo '<div class="card-body">';
            echo '<h5 class="card-title">' . htmlspecialchars($row['title']) . '</h5>';
            // echo '<h6 class="card-subtitle mb-2 text-muted">"' . strip_tags($row['description']) . '"</h6>';
            // Pass the blog id to view.php
            // echo "<a href='view.php?id=" . $row['id'] . "' class='btn btn-primary'>Read More</a>";
            echo '<a href="edit-blog.php?id=' . htmlspecialchars($row['id']) . '" class="btn btn-warning">Edit</a>';
            echo '<a href="delete-blog.php?id=' . htmlspecialchars($row['id']) . '" class="btn btn-danger" onclick="return confirm(\'Are you sure you want to delete this entry?\')">Delete</a>';
            echo '</div></div></div>';
        }
    } else {
        echo "<p>No records found.</p>";
    }
    ?>
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




</body>

</html>