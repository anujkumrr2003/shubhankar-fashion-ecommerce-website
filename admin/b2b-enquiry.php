<?php include('menu.php') ?>

<div class="row">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-header">
                <!-- <h4 class="header-title">B2B Inquiries</h4> -->
            </div>


<?php
include "config.php";

$total_qty = 0;
$total_amount = 0;

/* ---------------- Pagination ---------------- */

$limit = isset($_GET['limit']) && in_array($_GET['limit'], ['10','20','30','50','all'])
    ? $_GET['limit']
    : '10';

$page = isset($_GET['page']) && is_numeric($_GET['page'])
    ? (int)$_GET['page']
    : 1;

$per_page = ($limit == "all") ? 999999 : (int)$limit;
$offset   = ($page - 1) * $per_page;

/* ---------------- Total Records ---------------- */

$count = mysqli_query($conn,"
SELECT COUNT(*) AS total
FROM b2b_inquiries
");

$total_records = mysqli_fetch_assoc($count)['total'];

$total_pages = ($limit == "all")
    ? 1
    : ceil($total_records / $per_page);

/* ---------------- Main Query ---------------- */

$sql = mysqli_query($conn,"
SELECT
    b.*,

    p.name AS product_name,

    s.subcategory_name,

    CASE
        WHEN b.variant_id > 0
        THEN pv.variant_price
        ELSE p.price
    END AS price,

    (b.required_qty *
        CASE
            WHEN b.variant_id > 0
            THEN pv.variant_price
            ELSE p.price
        END
    ) AS total_amount

FROM b2b_inquiries b

LEFT JOIN products p
ON p.id = b.product_id

LEFT JOIN subcategories s
ON s.id = p.subcategory_id

LEFT JOIN product_variants pv
ON pv.id = b.variant_id

ORDER BY b.id DESC

LIMIT $offset,$per_page
");

if(!$sql){
    die(mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>B2B Inquiries</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        table th { background-color: #f8f9fa; font-weight: 600; }
        .table td, .table th { vertical-align: middle; }
        .modal-body dt { font-weight: 600; color: #495057; }
    </style>
</head>
<body  >

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4 mt-2">
        <h4>B2B Inquiries</h4>
        
        <div class="d-flex align-items-center gap-2">
            <label for="limit" class="form-label mb-0">Show:</label>
            <select id="limit" class="form-select" style="width: auto;" onchange="changeLimit(this.value)">
                <option value="10"  <?= $limit=='10' ? 'selected' : '' ?>>10</option>
                <option value="20"  <?= $limit=='20' ? 'selected' : '' ?>>20</option>
                <option value="30"  <?= $limit=='30' ? 'selected' : '' ?>>30</option>
                <option value="50"  <?= $limit=='50' ? 'selected' : '' ?>>50</option>
                <option value="all" <?= $limit=='all' ? 'selected' : '' ?>>All</option>
            </select>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-hover table-striped">
            <thead class="table-light">
                <tr>
                    <th>Si. No</th>
                    <th>Company</th>
                    <th>Product</th>
                    <th>Category</th>
                    <th>Size</th>
<th>Color</th>
                    <th>Qty</th>
                    <th>Contact Person</th>
                    <th>Phone</th>
                    <th>Status</th>
                    <th>Action</th>

                    <th>View</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $serial = $offset + 1;
                while($row=mysqli_fetch_assoc($sql)) { 
                     $total_qty += $row['required_qty'];
    $total_amount += $row['total_amount'];
                ?>
                
                    <tr>
                        <td><?= $serial++ ?></td>
                        <td><?= htmlspecialchars($row['company_name']) ?></td>
                        <td><?= htmlspecialchars($row['product_name']) ?></td>
                        <td><?= htmlspecialchars($row['subcategory_name'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($row['size']) ?></td>
<td><?= htmlspecialchars($row['color']) ?></td>
                        <td><?= $row['required_qty'] ?></td>
                        <td><?= htmlspecialchars($row['contact_person']) ?></td>
                        <td><?= htmlspecialchars($row['phone']) ?></td>
                        <td>
                            <span class="badge <?= $row['status'] == 'Accepted' ? 'bg-success' : 
                                ($row['status'] == 'Shipping' ? 'bg-info' : 
                                ($row['status'] == 'Cancelled' ? 'bg-danger' : 'bg-warning')) ?>">
                                <?= $row['status'] ?>
                            </span>
                        </td>
                        <td>
                            
                            
                            <form action="update-b2b-status.php" method="post" style="display:inline;">
                                <input type="hidden" name="id" value="<?= $row['id']; ?>">
                                <select name="status" class="form-select form-select-sm d-inline w-auto">
                                    <option value="Pending" <?= ($row['status']=='Pending')?'selected':'' ?>>Pending</option>
                                    <option value="Accepted" <?= ($row['status']=='Accepted')?'selected':'' ?>>Accepted</option>
                                    <option value="Shipping" <?= ($row['status']=='Shipping')?'selected':'' ?>>Shipping</option>
                                    <option value="Cancelled" <?= ($row['status']=='Cancelled')?'selected':'' ?>>Cancelled</option>
                                </select>
                                <button type="submit" class="btn btn-primary btn-sm mt-1">Update</button>
                            </form>
                        </td>
                        <!-- <td><button type="button" class="btn btn-info btn-sm me-2" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#viewModal"
                                    onclick="showDetails(<?= htmlspecialchars(json_encode($row)) ?>)">
                                <i class="fa-solid fa-eye"></i>
                            </button></td> -->
                            <td> <a href="b2b-inquiry-details.php?id=<?= (int)$row['id'] ?>" class="btn-view-sm" title="View Details" style="background-color:#bd4871; padding:5px; color:#fff; border-radius:5px;">
                                <i class="fa-solid fa-eye"></i>
                            </a></td>
                            
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($limit !== 'all' && $total_pages > 1): ?>
    <nav aria-label="Page navigation">
        <ul class="pagination justify-content-center mt-4">
            <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                <a class="page-link" href="?page=<?= $page-1 ?>&limit=<?= $limit ?>">Previous</a>
            </li>
            <?php for($i=1; $i<=$total_pages; $i++): ?>
                <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                    <a class="page-link" href="?page=<?= $i ?>&limit=<?= $limit ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>
            <li class="page-item <?= $page >= $total_pages ? 'disabled' : '' ?>">
                <a class="page-link" href="?page=<?= $page+1 ?>&limit=<?= $limit ?>">Next</a>
            </li>
        </ul>
    </nav>
    <?php endif; ?>
</div>

<!-- ==================== VIEW MODAL ==================== -->
<div class="modal fade" id="viewModal" tabindex="-1" aria-labelledby="viewModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="viewModalLabel">B2B Inquiry Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="modalBody">
        <!-- Details will be loaded here by JavaScript -->
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Show Details in Modal
function showDetails(row) {

    let html = `
        <div class="row">

            <div class="col-md-6">

                <h5 class="text-primary mb-3">Shipping Details</h5>

                <table class="table table-bordered table-sm">

                    <tr><th>Company</th><td>${row.company_name || ''}</td></tr>
                    <tr><th>GST Number</th><td>${row.gst_number || ''}</td></tr>
                    <tr><th>Contact Person</th><td>${row.contact_person || ''}</td></tr>
                    <tr><th>Email</th><td>${row.email || ''}</td></tr>
                    <tr><th>Phone</th><td>${row.phone || ''}</td></tr>
                    <tr><th>Country</th><td>${row.country || ''}</td></tr>
                    <tr><th>State</th><td>${row.state || ''}</td></tr>
                    <tr><th>City</th><td>${row.city || ''}</td></tr>
                    <tr><th>Address</th><td>${row.address || ''}</td></tr>
                    <tr><th>Business Type</th><td>${row.business_type || ''}</td></tr>
                    <tr><th>Delivery Date</th><td>${row.delivery_date || ''}</td></tr>
                    <tr><th>Remarks</th><td>${row.remarks ? row.remarks.replace(/\n/g,'<br>') : ''}</td></tr>

                </table>

            </div>

            ${row.billing_full_name ? `

            <div class="col-md-6">

                <h5 class="text-success mb-3">Billing Details</h5>

                <table class="table table-bordered table-sm">

                    <tr><th>Company</th><td>${row.billing_company_name || ''}</td></tr>
                    <tr><th>GST Number</th><td>${row.billing_gst_number || ''}</td></tr>
                    <tr><th>Contact Person</th><td>${row.billing_full_name || ''}</td></tr>
                    <tr><th>Phone</th><td>${row.billing_phone || ''}</td></tr>
                    <tr><th>State</th><td>${row.billing_state || ''}</td></tr>
                    <tr><th>City</th><td>${row.billing_city || ''}</td></tr>
                    <tr><th>Region</th><td>${row.billing_region || ''}</td></tr>
                    <tr><th>Address</th><td>${row.billing_address || ''}</td></tr>
                    <tr><th>Pincode</th><td>${row.billing_pincode || ''}</td></tr>
                    <tr><th>Address Type</th><td>${row.billing_address_type || ''}</td></tr>
                    <tr><th>Business Type</th><td>${row.billing_business_type || ''}</td></tr>
                    <tr><th>Delivery Date</th><td>${row.billing_delivery_date || ''}</td></tr>
                    <tr><th>Remarks</th><td>${row.billing_remarks ? row.billing_remarks.replace(/\n/g,'<br>') : ''}</td></tr>

                </table>

            </div>

            ` : ''}

        </div>

        <hr>

        <table class="table table-bordered">

            <tr>
                <th width="30%">Product</th>
                <td>${row.product_name || ''}</td>
            </tr>

            <tr>
                <th>Category</th>
                <td>${row.subcategory_name || ''}</td>
            </tr>

            <tr>
                <th>Quantity</th>
                <td>${row.required_qty}</td>
            </tr>

            <tr>
                <th>Status</th>
                <td>${row.status}</td>
            </tr>

            <tr>
                <th>Created At</th>
                <td>${row.created_at}</td>
            </tr>

        </table>
        <tfoot>

<tr class="table-secondary">

    <th colspan="4" class="text-end">
        Grand Total
    </th>

    <th>
        <?= $total_qty ?>
    </th>

    <th colspan="4"></th>

    <th>
        ₹<?= number_format($total_amount,2) ?>
    </th>

</tr>

</tfoot>
    `;

    document.getElementById("modalBody").innerHTML = html;
}

// Change pagination limit
function changeLimit(limit) {
    window.location.href = `?page=1&limit=${limit}`;
}
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