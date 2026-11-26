
<?php include('menu.php') ?>

<div class="row">
  <div class="col-xl-12">
    <div class="card">
      <div class="card-header">
        <h4 class="header-title">All Enquiry Data</h4>
      </div>
      
                <?php
include 'config.php';

if (isset($_GET['id'])) {
    $postId = $_GET['id'];

    // Fetch existing post data
    $stmt = $conn->prepare("SELECT title, subtitle, description, image_path, blog, meta_description, meta_title, meta_keywords FROM blogs WHERE id = ?");
    $stmt->bind_param("i", $postId);
    $stmt->execute();
    $stmt->bind_result($title, $subtitle, $description, $imagePath, $blog, $meta_description, $meta_title, $meta_keywords);
    $stmt->fetch();
    $stmt->close();
}

function createSlug($string) {
    return strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $string)));
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $subtitle = $_POST['subtitle'];
    $description = $_POST['description'];
    $meta_description = $_POST['meta_description'];
    $meta_title = $_POST['meta_title'];
    $meta_keywords = $_POST['meta_keywords'];

    $image = $_FILES['image'];
    
    // Slug Update
    $blog = createSlug($title); // Create new slug

    // Image update logic
    if ($image['error'] === 0) {
        $targetDir = "img/";
        $imagePath = $targetDir . basename($image['name']);
        move_uploaded_file($image['tmp_name'], $imagePath);
    } else {
        // If no new image is uploaded, retain the old image path
        $imagePath = $imagePath; // Use the previous image path
    }

    // Update post details in the database
    $stmt = $conn->prepare("UPDATE blogs SET title = ?, subtitle = ?, description = ?, image_path = ?, blog = ?, meta_keywords = ?, meta_description = ?, meta_title = ? WHERE id = ?");
    $stmt->bind_param("ssssssssi", $title, $subtitle, $description, $imagePath, $blog, $meta_keywords, $meta_description, $meta_title, $postId);

    if ($stmt->execute()) {
        echo "<script>
        alert('Post Updated successfully.');
        window.location.href = 'blog.php'; // Redirect to the page you want
      </script>";
    } else {
        echo "<script>
                alert('Failed to update post.');
              </script>";
    }
    
    $stmt->close();
}

?>




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Update Post</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<?php 
error_reporting(0);
?>
<body>
    <div class="container mt-5">
        <h2>Update Post</h2>
        <form action="" method="post" enctype="multipart/form-data">
            <div class="form-group mb-3">
                <label for="title">Title</label>
                <input type="text" class="form-control" name="title" value="<?php echo htmlspecialchars($title); ?>" required placeholder="Title">
            </div>
            <!-- <div class="form-group mb-3">
                <label for="subtitle">Subtitle</label>
                <input type="text" class="form-control" name="subtitle" value="<?php //echo htmlspecialchars($subtitle); ?>" required placeholder="Subtitle">
            </div> -->
            
            <div class="form-group mb-3">
                <label for="description">Description</label>
                <textarea id="description" name="description" class="form-control" placeholder="Description"><?php echo htmlspecialchars($description); ?></textarea>
            </div>
            <div class="form-group mb-3">
                <label for="image">Upload New Image (optional)</label>
                <input type="file" class="form-control" name="image" accept="image/*">
            </div>
            <div class="form-group mb-3">
                <label for="meta-title">Meta-title</label>
                <input type="text" class="form-control" name="meta_title" value="<?php echo htmlspecialchars($meta_title); ?>" required placeholder="Meta Title">
            </div>
            <div class="form-group mb-3">
                <label for="meta-description">Meta Description</label>
                <textarea id="meta-description" name="meta_description" class="form-control" placeholder="Meta Description"><?php echo htmlspecialchars($meta_description); ?></textarea>
            </div>
            <div class="form-group mb-3">
                <label for="meta-keywords">Meta Keywords</label>
                <textarea id="meta-keywords" name="meta_keywords" class="form-control" placeholder="Meta Keywords"><?php echo htmlspecialchars($meta_keywords); ?></textarea>
            </div>
            
            <button type="submit" class="btn btn-primary" style="margin-bottom: 17px;">Update Post</button>
        </form>
    </div>

    <script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>
    <script>
        ClassicEditor
            .create(document.querySelector('#description'))
            .catch(error => {
                console.error(error);
            });
    </script>
</body>
</html>


<style>
   
    /* Form container styles */
    .form-container {
        width: 90%;
        /* max-width: 100%; */
        padding: 25px;
        background-color: #ffffff;
        border-radius: 15px;
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
        transition: transform 0.3s ease;
    }
    
    .form-container:hover {
        transform: translateY(-5px);
    }

    /* Form element styles */
    h2 {
        text-align: center;
        margin-bottom: 25px;
        color: #333;
        font-size: 1.8rem;
    }

    form {
        display: flex;
        flex-direction: column;
    }

    input[type="text"], textarea, input[type="file"] {
        width: 95%;
        margin-bottom: 20px;
        padding: 12px;
        border: 2px solid #e0e0e0;
        border-radius: 10px;
        font-size: 1rem;
        background-color: #fafafa;
        transition: border-color 0.3s;
    }

    input[type="text"]:focus, textarea:focus, input[type="file"]:focus {
        border-color: #6a5acd;
        outline: none;
    }

    textarea {
        resize: vertical;
        min-height: 120px;
    }

    button {
        padding: 12px;
        background-color: #6a5acd;
        color: #fff;
        border: none;
        border-radius: 10px;
        font-size: 1rem;
        cursor: pointer;
        transition: background-color 0.3s, transform 0.3s;
    }

    button:hover {
        background-color: #5b4fb0;
        transform: translateY(-2px);
    }

    /* Responsive design */
    @media (max-width: 768px) {
        .form-container {
            padding: 20px;
            max-width: 90%;
        }

        h2 {
            font-size: 1.6rem;
        }

        button {
            font-size: 0.95rem;
        }
    }
</style>
                  </div>
                  <!-- end table-responsive-->

                </div>
                <!-- end card body-->
              </div>
              <!-- end card -->
            </div>
            <!-- end col-->

          </div>
          <!-- end row -->




        </div>
        <!-- container -->

      </div>
      <!-- content -->

      <!-- Footer Start -->

      <?php //include('frame/footer.php') ?>

      <!-- end Footer -->

    </div>

    <!-- ============================================================== -->
    <!-- End Page content -->
    <!-- ============================================================== -->

  </div>
  <!-- END wrapper -->

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