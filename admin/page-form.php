
<?php include('menu.php') ?>

<div class="row">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-header">
                <h4 class="header-title">Add Policy</h4>
            </div>


<?php
include 'config.php';
error_reporting(0);

$action = $_GET['action'] ?? '';
$id = $_GET['id'] ?? '';
$slug = $_GET['page'] ?? '';

//////////////////////////////////////////////////////////
// DELETE
//////////////////////////////////////////////////////////
if ($action == 'delete' && $id != '') {
    $conn->query("DELETE FROM pages WHERE id='$id'");
    echo "<script>
        alert('Page deleted successfully');
        window.location.href = 'page-form.php';
    </script>";
    exit;
}

//////////////////////////////////////////////////////////
// SAVE (INSERT / UPDATE)
//////////////////////////////////////////////////////////
if ($action == 'save') {

    $id = $_POST['id'];
    $title = $_POST['page_title'];
    $content = $_POST['page_content'];

    if ($id == "") {
        $conn->query("INSERT INTO pages(page_title, page_content)
                      VALUES('$title', '$content')");
    } else {
        $conn->query("UPDATE pages SET page_title='$title',
                      page_content='$content' WHERE id='$id'");
    }

   echo "<script>
        alert('Page deleted successfully');
        window.location.href = 'page-form.php';
    </script>";
    exit;
}

//////////////////////////////////////////////////////////
// EDIT PAGE
//////////////////////////////////////////////////////////
$page_title = '';
$page_content = '';

if ($action == 'edit' && $id != '') {
    $q = $conn->query("SELECT * FROM pages WHERE id='$id'");
    $data = $q->fetch_assoc();
    $page_title = $data['page_title'];
    $page_content = $data['page_content'];
}

//////////////////////////////////////////////////////////
// FRONTEND VIEW
//////////////////////////////////////////////////////////
if ($action == 'view' && $slug != '') {

    $map = [
        'offers' => 'Offers T&Cs',
        'help-faqs' => 'Help & FAQs',
        'legal-policies' => 'Legal Policies',
        'delivery-info' => 'Delivery Information',
        'international-shipping' => 'International Shipping',
        'returns' => 'Returns',
        'lifetime-exchange' => 'Lifetime Exchange Policy'
    ];

    $page_title = $map[$slug];

    $q = $conn->query("SELECT * FROM pages WHERE page_title='$page_title' LIMIT 1");
    $data = $q->fetch_assoc();

    echo "<h1>{$data['page_title']}</h1>";
    echo $data['page_content'];
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Pages</title>

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: #f7f7f7;
        }

        .container-box {
            max-width: 900px;
            margin: 20px auto;
        }

        .card {
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.06);
        }

        .table th, .table td {
            vertical-align: middle;
        }

        @media(max-width: 600px) {
            h3 {
                font-size: 22px;
            }
            .card-header {
                font-size: 16px;
            }
        }
    </style>

</head>

<body>

<!-- <div class="container container-box"> -->

    

    <!-- FORM -->
    <div class="card mb-4">
        <!-- <div class="card-header fw-semibold">Add / Edit Page</div> -->
        <div class="card-body">

            <form action="page-form.php?action=save" method="POST">
                <input type="hidden" name="id" value="<?= $id ?>">

                <div class="mb-3">
                    <label class="fw-semibold">Page Title</label>
                    <select name="page_title" class="form-control" required>
                        <option value="">Select Page</option>

                        <?php 
                        $titles = [
                            'Offers T&Cs',
                            'Help & FAQs',
                            'Legal Policies',
                            'Delivery Information',
                            'International Shipping',
                            'Returns',
                            'Lifetime Exchange Policy'
                        ];

                        foreach ($titles as $title) {
                            $sel = ($page_title == $title) ? 'selected' : '';
                            echo "<option $sel>$title</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="fw-semibold">Page Content</label>
                    <textarea name="page_content" id="editor" class="form-control" rows="10"><?= $page_content ?></textarea>
                </div>

                <button class="btn btn-primary px-4">Save</button>
            </form>

        </div>
    </div>

    <!-- LIST -->
    <div class="card">
        <div class="card-header fw-semibold">All Pages</div>
        <div class="card-body">

            <?php
            $q = $conn->query("SELECT * FROM pages ORDER BY id DESC");
            ?>

            <table class="table table-bordered table-striped">
                <thead class="table-light">
                <tr>
                    <!-- <th>ID</th> -->
                    <th>Page Title</th>
                    <th style="width: 160px;">Action</th>
                </tr>
                </thead>

                <tbody>
                <?php while ($row = $q->fetch_assoc()) { ?>
                    <tr>
                        <!-- <td><?= $row['id'] ?></td> -->
                        <td><?= $row['page_title'] ?></td>
                        <td>
                            <a href="page-form.php?action=edit&id=<?= $row['id'] ?>" class="btn btn-sm btn-primary">Edit</a>
                            <a href="page-form.php?action=delete&id=<?= $row['id'] ?>"
                               class="btn btn-sm btn-danger"
                               onclick="return confirm('Delete this page?')">Delete</a>
                        </td>
                    </tr>
                <?php } ?>
                </tbody>

            </table>

        </div>
    </div>

</div>

<script src="https://cdn.ckeditor.com/4.21.0/standard/ckeditor.js"></script>
<script>
    CKEDITOR.replace('editor');
</script>

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