<?php include('menu.php') ?>

<div class="row">
  <div class="col-xl-12">
    <div class="card">
      <div class="card-header">
        <h4 class="header-title">Add Social Media</h4>
      </div>
      


<?php
include "config.php";

// Delete record
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    if ($conn->query("DELETE FROM social_links WHERE id=$id")) {
        // echo "<script>alert('Record deleted successfully'); window.location.href='".$_SERVER['PHP_SELF']."';</script>";
    } else {
        echo "<script>alert('Error deleting record');</script>";
    }
}


// Insert record
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $platform = $_POST['platform'];
    $link = $_POST['link'];
    $bg_color = $_POST['bg_color'];
    $icon = $_POST['icon'];

    $stmt = $conn->prepare("INSERT INTO social_links (platform, link, bg_color, icon) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $platform, $link, $bg_color, $icon);
    $stmt->execute();
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Social Media Links</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<style>
  .social-preview {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    padding: 10px;
    border-radius: 6px;
    color: #fff;
    margin-top: 15px;
  }
  .social-preview i {
    font-size: 24px;
  }
</style>
</head>
<body>

<div class="container">
  <h2 class="mb-4">Add Social Media Link</h2>
  
  <form method="POST">
    <div class="mb-3">
      <label class="form-label">Select Platform</label>
      <select class="form-select" id="platform" name="platform" required>
        <option value="">-- Select --</option>
        <option value="Facebook" data-color="#1877F2" data-icon="fa-brands fa-facebook-f">Facebook</option>
        <option value="Twitter" data-color="#1DA1F2" data-icon="fa-brands fa-twitter">Twitter</option>
        <option value="Instagram" data-color="instagram" data-icon="fa-brands fa-instagram">Instagram</option>
        <option value="LinkedIn" data-color="#0077B5" data-icon="fa-brands fa-linkedin-in">LinkedIn</option>
        <option value="YouTube" data-color="#FF0000" data-icon="fa-brands fa-youtube">YouTube</option>
        <option value="WhatsApp" data-color="#25D366" data-icon="fa-brands fa-whatsapp">WhatsApp</option> <!-- ✅ Added -->
      </select>
    </div>

    <div class="mb-3">
      <label class="form-label">Profile Link</label>
      <input type="url" class="form-control" name="link" placeholder="https://example.com" required>
    </div>

    <input type="hidden" name="bg_color" id="bg_color">
    <input type="hidden" name="icon" id="icon">

    <div id="preview" class="social-preview d-none">
      <i id="preview-icon"></i>
      <span id="preview-text"></span>
    </div>

    <button type="submit" class="btn btn-success mt-3">Save</button>
  </form>

  <hr class="my-5">

  <h3>Saved Social Links</h3>
  <table class="table table-bordered table-striped">
    <thead>
      <tr>
        
        <th>Platform</th>
        <th>Link</th>
        <th>Preview</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      <?php
      $result = $conn->query("SELECT * FROM social_links ORDER BY id DESC");
      while ($row = $result->fetch_assoc()) {
          // Handle Instagram gradient
          $bgStyle = $row['bg_color'] == "instagram" 
              ? "background:linear-gradient(45deg,#f09433 0%,#e6683c 25%,#dc2743 50%,#cc2366 75%,#bc1888 100%);" 
              : "background: {$row['bg_color']};";
          
          echo "<tr>
                  
                  <td>{$row['platform']}</td>
                  <td><a href='{$row['link']}' target='_blank'>{$row['link']}</a></td>
                  <td>
                    <div style='{$bgStyle} padding:8px; border-radius:6px; display:inline-block; color:#fff;'>
                      <i class='{$row['icon']}'></i>
                    </div>
                  </td>
                  <td>
                    <a href='?delete={$row['id']}' class='btn btn-sm btn-danger' onclick=\"return confirm('Are you sure you want to delete this record?');\">Delete</a>
                  </td>
                </tr>";
      }
      ?>
    </tbody>
  </table>
</div>

<script>
document.getElementById("platform").addEventListener("change", function() {
    let option = this.options[this.selectedIndex];
    let color = option.getAttribute("data-color");
    let icon = option.getAttribute("data-icon");
    let text = option.value;

    // Handle Instagram separately
    if (color === "instagram") {
        document.getElementById("bg_color").value = "instagram";
        document.getElementById("preview").style.background = "linear-gradient(45deg,#f09433 0%,#e6683c 25%,#dc2743 50%,#cc2366 75%,#bc1888 100%)";
    } else {
        document.getElementById("bg_color").value = color;
        document.getElementById("preview").style.background = color;
    }

    document.getElementById("icon").value = icon;

    let preview = document.getElementById("preview");
    preview.classList.remove("d-none");
    document.getElementById("preview-icon").className = icon;
    document.getElementById("preview-text").textContent = text;
});
</script>
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
