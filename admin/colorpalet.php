

<?php include('menu.php') ?>

<div class="row">
  <div class="col-xl-12">
    <div class="card">
      <div class="card-header">
        <h4 class="header-title">Add API</h4>
      </div>
      



<?php
include "config.php"; // Database connection

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['submit'])) {
        $background_color = $_POST['background_color'];
        $heading_text_color = $_POST['heading_text_color'];
        $heading_background_color = $_POST['heading_background_color'];
        $body_background_color = $_POST['body_background_color'];
        $button_background_color = $_POST['button_background_color'];

        $sql = "INSERT INTO profile (background_color, heading_text_color, heading_background_color, body_background_color, button_background_color) 
                VALUES (?, ?, ?, ?, ?)";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("sssss", $background_color, $heading_text_color, $heading_background_color, $body_background_color, $button_background_color);
            $stmt->execute();
        } else {
            echo "Error: " . $conn->error;
        }
    } elseif (isset($_POST['update'])) {
        $id = $_POST['id'];
        $background_color = $_POST['background_color'];
        $heading_text_color = $_POST['heading_text_color'];
        $heading_background_color = $_POST['heading_background_color'];
        $body_background_color = $_POST['body_background_color'];
        $button_background_color = $_POST['button_background_color'];

        $sql = "UPDATE profile SET background_color=?, heading_text_color=?, heading_background_color=?, body_background_color=?, button_background_color=? WHERE id=?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("sssssi", $background_color, $heading_text_color, $heading_background_color, $body_background_color, $button_background_color, $id);
            $stmt->execute();
        } else {
            echo "Error: " . $conn->error;
        }
    } elseif (isset($_POST['delete'])) {
        $id = $_POST['id'];
        $sql = "DELETE FROM profile WHERE id=?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("i", $id);
            $stmt->execute();
        } else {
            echo "Error: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Profile Form</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
  <h3 class="text-center">Profile Form</h3>
  <form method="POST" action="">
    <div class="mb-3"><label class="form-label">Heading Text Color</label><input type="color" class="form-control" name="background_color" required></div>
    <div class="mb-3"><label class="form-label">Heading Background Color</label><input type="color" class="form-control" name="heading_text_color" required></div>
    <div class="mb-3"><label class="form-label">Body Background Color</label><input type="color" class="form-control" name="heading_background_color" required></div>
    <div class="mb-3"><label class="form-label">Button Text Color</label><input type="color" class="form-control" name="body_background_color" required></div>
    <div class="mb-3"><label class="form-label">Button Background Color</label><input type="color" class="form-control" name="button_background_color" required></div>
    <div class="text-center"><button type="submit" name="submit" class="btn btn-primary">Submit</button></div>
  </form>

  <hr>

  <h4 class="text-center mt-5">User Profiles</h4>
  <table class="table table-bordered text-center">
    <thead>
      <tr>
        <!--<th>ID</th>-->
        <th>Heading Text Color</th>
        <th>Heading Background Color</th>
        <th>Body Background Color</th>
        <th>Button Text Color</th>
        <th>Button Background Color</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
<?php
$sql = "SELECT * FROM profile";
$result = $conn->query($sql);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        echo "<tr>
            
              <td style='background:{$row['background_color']}; color:white;'>{$row['background_color']}</td>
              <td style='background:{$row['heading_text_color']};'>{$row['heading_text_color']}</td>
              <td style='background:{$row['heading_background_color']};'>{$row['heading_background_color']}</td>
              <td style='background:{$row['body_background_color']};'>{$row['body_background_color']}</td>
              <td style='background:{$row['button_background_color']};'>{$row['button_background_color']}</td>
              <td>
                <form method='POST' action='' style='display:inline-block;'>
                  <input type='hidden' name='id' value='{$row['id']}'>
                  <input type='hidden' name='background_color' value='{$row['background_color']}'>
                  <button type='button' class='btn btn-warning btn-sm' data-bs-toggle='modal' data-bs-target='#updateModal{$row['id']}'>Update</button>
                  <button type='submit' name='delete' class='btn btn-danger btn-sm'>Delete</button>
                </form>
              </td>
            </tr>";

        // Modal form
        echo "
        <div class='modal fade' id='updateModal{$row['id']}' tabindex='-1'>
          <div class='modal-dialog'>
            <div class='modal-content'>
              <form method='POST'>
                <div class='modal-header'><h5 class='modal-title'>Update Profile</h5><button type='button' class='btn-close' data-bs-dismiss='modal'></button></div>
                <div class='modal-body'>
                  <input type='hidden' name='id' value='{$row['id']}'>
                  <div class='mb-2'><label class='form-label'>Heading Text Color</label><input type='color' name='background_color' class='form-control' value='{$row['background_color']}' required></div>
                  <div class='mb-2'><label class='form-label'>Heading Background Color</label><input type='color' name='heading_text_color' class='form-control' value='{$row['heading_text_color']}' required></div>
                  <div class='mb-2'><label class='form-label'>Body Background Color</label><input type='color' name='heading_background_color' class='form-control' value='{$row['heading_background_color']}' required></div>
                  <div class='mb-2'><label class='form-label'>Button Text Color</label><input type='color' name='body_background_color' class='form-control' value='{$row['body_background_color']}' required></div>
                  <div class='mb-2'><label class='form-label'>Button Background Color</label><input type='color' name='button_background_color' class='form-control' value='{$row['button_background_color']}' required></div>
                </div>
                <div class='modal-footer'><button type='submit' name='update' class='btn btn-primary'>Update</button></div>
              </form>
            </div>
          </div>
        </div>";
    }
} else {
    echo "<tr><td colspan='7'>No data found.</td></tr>";
}
?>
    </tbody>
  </table>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
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
