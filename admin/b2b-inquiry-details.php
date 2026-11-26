<?php include('menu.php') ?>

<div class="row">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-header">
                <h4 class="header-title">B2B Inquiries</h4>
            </div>






            <?php
            include "config.php";

            $id = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : 0;

            if (!$id) {
                die("Invalid inquiry ID.");
            }

            $sql = mysqli_query($conn, "
SELECT
    b.*,
    b.size_requirement,
    b.product_remarks,

    p.name AS product_name,
    p.price AS product_base_price,

    s.subcategory_name,

    pv.variant_price,
    pv.variant_name,

(
    CASE
        WHEN b.variant_id > 0
        THEN pv.variant_price
        ELSE p.price
    END
) * 1.20 AS price,

(
    b.required_qty *
    (
        CASE
            WHEN b.variant_id > 0
            THEN pv.variant_price
            ELSE p.price
        END
    ) * 1.20
) AS total_amount

FROM b2b_inquiries b
LEFT JOIN products p ON p.id=b.product_id
LEFT JOIN subcategories s ON s.id=p.subcategory_id
LEFT JOIN product_variants pv ON pv.id=b.variant_id
WHERE b.id='$id'
LIMIT 1
");

            if (!$sql) {
                die(mysqli_error($conn));
            }

            if (mysqli_num_rows($sql) === 0) {
                die("Inquiry not found.");
            }

            $row = mysqli_fetch_assoc($sql);

            /* Helper to safely output a value, or empty string */
            function val($row, $key)
            {
                return isset($row[$key]) && $row[$key] !== '' && $row[$key] !== null
                    ? htmlspecialchars($row[$key])
                    : '';
            }

            /* Determine if billing address exists at all */
            $billing_fields = [
                'billing_company_name',
                'billing_gst_number',
                'billing_full_name',
                'billing_phone',
                'billing_state',
                'billing_city',
                'billing_region',
                'billing_address',
                'billing_pincode',
                'billing_address_type',
                'billing_business_type',
                'billing_delivery_date',
                'billing_remarks'
            ];

            $has_billing = false;
            foreach ($billing_fields as $f) {
                if (!empty($row[$f])) {
                    $has_billing = true;
                    break;
                }
            }

            $status = $row['status'];
            $badgeClass = 'badge-pending';
            if ($status == 'Accepted') {
                $badgeClass = 'badge-accepted';
            } elseif ($status == 'Shipping') {
                $badgeClass = 'badge-shipping';
            } elseif ($status == 'Cancelled') {
                $badgeClass = 'badge-cancelled';
            }
            ?>
            <!DOCTYPE html>
            <html lang="en">

            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Inquiry #<?= $id ?> — B2B Inquiry Details</title>
                <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
                <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
                <link rel="preconnect" href="https://fonts.googleapis.com">
                <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

                <style>
                    :root {
                        --primary: #4f46e5;
                        --primary-dark: #3730a3;
                        --primary-light: #eef2ff;
                        --success: #16a34a;
                        --info: #0891b2;
                        --warning: #d97706;
                        --danger: #dc2626;
                        --text-dark: #1e293b;
                        --text-muted: #64748b;
                        --bg-soft: #f8fafc;
                        --border-soft: #e2e8f0;
                        --radius: 14px;
                        --shadow-sm: 0 1px 3px rgba(15, 23, 42, .06), 0 1px 2px rgba(15, 23, 42, .04);
                        --shadow-md: 0 10px 25px -5px rgba(15, 23, 42, .08), 0 8px 10px -6px rgba(15, 23, 42, .04);
                    }

                    * {
                        font-family: 'Inter', sans-serif;
                    }

                    body {
                        background: var(--bg-soft);
                        color: var(--text-dark);
                    }

                    .page-header {
                        background: #000;
                        /* border-radius: var(--radius); */
                        padding: 15px 20px;
                        color: #fff;
                        margin-bottom: 24px;
                        box-shadow: var(--shadow-md);
                        display: flex;
                        justify-content: space-between;
                        align-items: center;
                        flex-wrap: wrap;
                        gap: 16px;
                    }

                    .page-header h4 {
                        font-weight: 800;
                        letter-spacing: -.02em;
                        margin: 0;
                    }

                    .page-header .sub {
                        opacity: .85;
                        font-size: .85rem;
                        margin-top: 4px;
                    }

                    .btn-back {
                        background: rgba(255, 255, 255, .15);
                        border: 1px solid rgba(255, 255, 255, .35);
                        color: #fff;
                        font-weight: 600;
                        border-radius: 10px;
                        padding: 9px 18px;
                        font-size: .88rem;
                        transition: all .15s ease;
                        text-decoration: none;
                        display: inline-flex;
                        align-items: center;
                        gap: 8px;
                    }

                    .btn-back:hover {
                        background: rgba(255, 255, 255, .28);
                        color: #fff;
                    }

                    .badge-status {
                        padding: 7px 16px;
                        border-radius: 20px;
                        font-weight: 700;
                        font-size: .8rem;
                        display: inline-flex;
                        align-items: center;
                        gap: 7px;
                    }

                    .badge-status i {
                        font-size: .6rem;
                    }

                    .badge-pending {
                        background: #fef3c7;
                        color: #92400e;
                    }

                    .badge-accepted {
                        background: #dcfce7;
                        color: #166534;
                    }

                    .badge-shipping {
                        background: #cffafe;
                        color: #155e75;
                    }

                    .badge-cancelled {
                        background: #fee2e2;
                        color: #991b1b;
                    }

                    .section-card {
                        background: #fff;
                        /* border-radius: var(--radius); */
                        box-shadow: var(--shadow-sm);
                        border: 1px solid var(--border-soft);
                        padding: 24px;
                        margin-bottom: 20px;
                        height: 100%;
                    }

                    .section-title {
                        display: flex;
                        align-items: center;
                        gap: 10px;
                        font-weight: 800;
                        font-size: 1rem;
                        margin-bottom: 18px;
                        color: var(--text-dark);
                    }

                    .section-title .icon-box {
                        width: 36px;
                        height: 36px;
                        border-radius: 10px;
                        background: var(--primary-light);
                        color: var(--primary);
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        font-size: .9rem;
                        flex-shrink: 0;
                    }

                    .section-title.success .icon-box {
                        background: #ecfdf5;
                        color: var(--success);
                    }

                    .section-title.info .icon-box {
                        background: #ecfeff;
                        color: var(--info);
                    }

                    .section-title.warn .icon-box {
                        background: #fffbeb;
                        color: var(--warning);
                    }

                    .info-row {
                        display: flex;
                        justify-content: space-between;
                        gap: 14px;
                        padding: 9px 0;
                        border-bottom: 1px dashed var(--border-soft);
                        font-size: .89rem;
                    }

                    .info-row:last-child {
                        border-bottom: none;
                    }

                    .info-row .label {
                        color: var(--text-muted);
                        font-weight: 600;
                        flex-shrink: 0;
                        min-width: 130px;
                    }

                    .info-row .value {
                        color: var(--text-dark);
                        font-weight: 600;
                        text-align: right;
                        word-break: break-word;
                    }

                    .remarks-box {
                        background: var(--bg-soft);
                        border: 1px solid var(--border-soft);
                        border-radius: 10px;
                        padding: 14px 16px;
                        font-size: .89rem;
                        line-height: 1.6;
                        color: var(--text-dark);
                        white-space: pre-wrap;
                    }

                    .summary-table {
                        width: 100%;
                        font-size: .9rem;
                    }

                    .summary-table td {
                        padding: 9px 0;
                        border-bottom: 1px dashed var(--border-soft);
                    }

                    .summary-table tr:last-child td {
                        border-bottom: none;
                    }

                    .summary-table .label {
                        color: var(--text-muted);
                        font-weight: 600;
                    }

                    .summary-table .value {
                        text-align: right;
                        font-weight: 700;
                    }

                    .grand-total-row td {
                        font-size: 1.05rem;
                        font-weight: 800;
                        color: var(--primary-dark);
                        padding-top: 14px;
                        border-top: 2px solid var(--border-soft);
                        border-bottom: none;
                    }

                    .status-timeline {
                        display: flex;
                        align-items: center;
                        gap: 8px;
                        flex-wrap: wrap;
                    }

                    .status-step {
                        display: flex;
                        align-items: center;
                        gap: 8px;
                        padding: 8px 14px;
                        border-radius: 10px;
                        background: var(--bg-soft);
                        border: 1px solid var(--border-soft);
                        font-size: .82rem;
                        font-weight: 600;
                        color: var(--text-muted);
                    }

                    .status-step.active {
                        background: var(--primary-light);
                        border-color: var(--primary);
                        color: var(--primary-dark);
                    }

                    .status-step.cancelled {
                        background: #fee2e2;
                        border-color: #fca5a5;
                        color: #991b1b;
                    }

                    .company-hero {
                        display: flex;
                        align-items: center;
                        gap: 16px;
                    }

                    .company-hero .avatar {
                        width: 56px;
                        height: 56px;
                        border-radius: 14px;
                        background: rgba(255, 255, 255, .18);
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        font-size: 1.4rem;
                        font-weight: 800;
                        flex-shrink: 0;
                    }

                    @media (max-width: 767px) {
                        .info-row {
                            flex-direction: column;
                            gap: 2px;
                        }

                        .info-row .value {
                            text-align: left;
                        }

                        .page-header {
                            padding: 22px 20px;
                        }
                    }
                </style>
            </head>

            <body>

                <div class="container-fluid py-4 px-3 px-md-4" style="max-width:1200px;">

                    <!-- Header -->
                    <div class="page-header">
                        <div class="company-hero">
                            <div class="avatar"><?= strtoupper(substr(val($row, 'company_name') ?: 'B', 0, 1)) ?></div>
                            <div>
                                <h4><?= val($row, 'company_name') ?: 'B2B Inquiry' ?></h4>
                                <div class="sub">
                                    <i class="fa-regular fa-clock me-1"></i>Inquiry #<?= $id ?>
                                    &nbsp;•&nbsp;
                                    Submitted on <?= val($row, 'created_at') ?>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <span class="badge-status <?= $badgeClass ?>">
                                <i class="fa-solid fa-circle"></i><?= htmlspecialchars($status) ?>
                            </span>
                            <a href="b2b-enquiry.php" class="btn-back">
                                <i class="fa-solid fa-arrow-left"></i> Back to List
                            </a>
                        </div>
                    </div>

                    <div class="row g-3">

                        <!-- Company Information -->
                        <div class="col-md-6">
                            <div class="section-card">
                                <div class="section-title">
                                    <span class="icon-box"><i class="fa-solid fa-building"></i></span>
                                    Company Information
                                </div>
                                <?php if (val($row, 'company_name')): ?>
                                    <div class="info-row"><span class="label">Company Name</span><span class="value"><?= val($row, 'company_name') ?></span></div>
                                <?php endif; ?>
                                <?php if (val($row, 'gst_number')): ?>
                                    <div class="info-row"><span class="label">GST Number</span><span class="value"><?= val($row, 'gst_number') ?></span></div>
                                <?php endif; ?>
                                <?php if (val($row, 'business_type')): ?>
                                    <div class="info-row"><span class="label">Business Type</span><span class="value"><?= val($row, 'business_type') ?></span></div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Contact Information -->
                        <div class="col-md-6">
                            <div class="section-card">
                                <div class="section-title info">
                                    <span class="icon-box"><i class="fa-solid fa-address-card"></i></span>
                                    Contact Information
                                </div>
                                <?php if (val($row, 'contact_person')): ?>
                                    <div class="info-row"><span class="label">Contact Person</span><span class="value"><?= val($row, 'contact_person') ?></span></div>
                                <?php endif; ?>
                                <?php if (val($row, 'email')): ?>
                                    <div class="info-row"><span class="label">Email</span><span class="value"><?= val($row, 'email') ?></span></div>
                                <?php endif; ?>
                                <?php if (val($row, 'phone')): ?>
                                    <div class="info-row"><span class="label">Phone</span><span class="value"><?= val($row, 'phone') ?></span></div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Shipping Address -->
                        <div class="col-md-6">
                            <div class="section-card">
                                <div class="section-title success">
                                    <span class="icon-box"><i class="fa-solid fa-truck-fast"></i></span>
                                    Shipping Address
                                </div>
                                <?php if (val($row, 'address')): ?>
                                    <div class="info-row"><span class="label">Address</span><span class="value"><?= val($row, 'address') ?></span></div>
                                <?php endif; ?>
                                <?php if (val($row, 'city')): ?>
                                    <div class="info-row"><span class="label">City</span><span class="value"><?= val($row, 'city') ?></span></div>
                                <?php endif; ?>
                                <?php if (val($row, 'state')): ?>
                                    <div class="info-row"><span class="label">State</span><span class="value"><?= val($row, 'state') ?></span></div>
                                <?php endif; ?>
                                <?php if (val($row, 'country')): ?>
                                    <div class="info-row"><span class="label">Country</span><span class="value"><?= val($row, 'country') ?></span></div>
                                <?php endif; ?>
                                <?php if (val($row, 'delivery_date')): ?>
                                    <div class="info-row"><span class="label">Delivery Date</span><span class="value"><?= val($row, 'delivery_date') ?></span></div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Billing Address (conditional) -->
                        <?php if ($has_billing): ?>
                            <div class="col-md-6">
                                <div class="section-card">
                                    <div class="section-title warn">
                                        <span class="icon-box"><i class="fa-solid fa-file-invoice-dollar"></i></span>
                                        Billing Address
                                    </div>
                                    <?php if (val($row, 'billing_company_name')): ?>
                                        <div class="info-row"><span class="label">Company</span><span class="value"><?= val($row, 'billing_company_name') ?></span></div>
                                    <?php endif; ?>
                                    <?php if (val($row, 'billing_gst_number')): ?>
                                        <div class="info-row"><span class="label">GST Number</span><span class="value"><?= val($row, 'billing_gst_number') ?></span></div>
                                    <?php endif; ?>
                                    <?php if (val($row, 'billing_full_name')): ?>
                                        <div class="info-row"><span class="label">Contact Person</span><span class="value"><?= val($row, 'billing_full_name') ?></span></div>
                                    <?php endif; ?>
                                    <?php if (val($row, 'billing_phone')): ?>
                                        <div class="info-row"><span class="label">Phone</span><span class="value"><?= val($row, 'billing_phone') ?></span></div>
                                    <?php endif; ?>
                                    <?php if (val($row, 'billing_address')): ?>
                                        <div class="info-row"><span class="label">Address</span><span class="value"><?= val($row, 'billing_address') ?></span></div>
                                    <?php endif; ?>
                                    <?php if (val($row, 'billing_city')): ?>
                                        <div class="info-row"><span class="label">City</span><span class="value"><?= val($row, 'billing_city') ?></span></div>
                                    <?php endif; ?>
                                    <?php if (val($row, 'billing_state')): ?>
                                        <div class="info-row"><span class="label">State</span><span class="value"><?= val($row, 'billing_state') ?></span></div>
                                    <?php endif; ?>
                                    <?php if (val($row, 'billing_region')): ?>
                                        <div class="info-row"><span class="label">Region</span><span class="value"><?= val($row, 'billing_region') ?></span></div>
                                    <?php endif; ?>
                                    <?php if (val($row, 'billing_pincode')): ?>
                                        <div class="info-row"><span class="label">Pincode</span><span class="value"><?= val($row, 'billing_pincode') ?></span></div>
                                    <?php endif; ?>
                                    <?php if (val($row, 'billing_address_type')): ?>
                                        <div class="info-row"><span class="label">Address Type</span><span class="value"><?= val($row, 'billing_address_type') ?></span></div>
                                    <?php endif; ?>
                                    <?php if (val($row, 'billing_business_type')): ?>
                                        <div class="info-row"><span class="label">Business Type</span><span class="value"><?= val($row, 'billing_business_type') ?></span></div>
                                    <?php endif; ?>
                                    <?php if (val($row, 'billing_delivery_date')): ?>
                                        <div class="info-row"><span class="label">Delivery Date</span><span class="value"><?= val($row, 'billing_delivery_date') ?></span></div>
                                    <?php endif; ?>
                                    <?php if (val($row, 'billing_remarks')): ?>
                                        <div class="mt-3">
                                            <div class="label mb-1" style="font-size:.8rem;color:var(--text-muted);font-weight:600;">Billing Remarks</div>
                                            <div class="remarks-box"><?= nl2br(val($row, 'billing_remarks')) ?></div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Product Details -->
                        <div class="col-md-6">
                            <div class="section-card">
                                <div class="section-title">
                                    <span class="icon-box"><i class="fa-solid fa-box-open"></i></span>
                                    Product Details
                                </div>
                                <?php if (val($row, 'product_name')): ?>
                                    <div class="info-row"><span class="label">Product</span><span class="value"><?= val($row, 'product_name') ?></span></div>
                                <?php endif; ?>
                                <?php if (val($row, 'variant_name')): ?>
                                    <div class="info-row"><span class="label">Variant</span><span class="value"><?= val($row, 'variant_name') ?></span></div>
                                <?php endif; ?>
                                <?php if (val($row, 'subcategory_name')): ?>
                                    <div class="info-row"><span class="label">Category</span><span class="value"><?= val($row, 'subcategory_name') ?></span></div>
                                <?php endif; ?>
                                <?php if (val($row, 'size')): ?>
<div class="info-row">
    <span class="label">Size</span>
    <span class="value"><?= val($row, 'size') ?></span>
</div>
<?php endif; ?>

<?php if (val($row, 'color')): ?>
<div class="info-row">
    <span class="label">Color</span>
    <span class="value"><?= val($row, 'color') ?></span>
</div>
<?php endif; ?>

                                 <?php if (val($row, 'size_requirement')): ?>
<div class="info-row">
    <span class="label">Size Requirement</span>
    <span class="value"><?= val($row, 'size_requirement') ?></span>
</div>
<?php endif; ?>
                                 <?php if (val($row, 'product_remarks')): ?>
<div class="info-row">
    <span class="label">Product Remarks</span>
    <span class="value"><?= nl2br(val($row, 'product_remarks')) ?></span>
</div>
<?php endif; ?>

                                <div class="info-row"><span class="label">Required Qty</span><span class="value"><?= (int)$row['required_qty'] ?></span></div>
                                <?php if ($row['price'] !== null): ?>
                                    <div class="info-row"><span class="label">Unit Price</span><span class="value">₹<?= number_format($row['price'], 2) ?></span></div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Order Summary -->
                        <div class="col-md-6">
                            <div class="section-card">
                                <div class="section-title success">
                                    <span class="icon-box"><i class="fa-solid fa-receipt"></i></span>
                                    Order Summary
                                </div>
                                <table class="summary-table">
                                    <tr>
                                        <td class="label">Quantity</td>
                                        <td class="value"><?= (int)$row['required_qty'] ?></td>
                                    </tr>
                                    <tr>
                                        <td class="label">Unit Price</td>
                                        <td class="value">₹<?= number_format($row['price'] ?? 0, 2) ?></td>
                                    </tr>
                                    <tr class="grand-total-row">
                                        <td>Grand Total</td>
                                        <td class="value">₹<?= number_format($row['total_amount'] ?? 0, 2) ?></td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <!-- Status -->
                        <div class="col-md-6">
                            <div class="section-card">
                                <div class="section-title warn">
                                    <span class="icon-box"><i class="fa-solid fa-list-check"></i></span>
                                    Status
                                </div>
                                <div class="status-timeline">
                                    <?php
                                    $steps = ['Pending', 'Accepted', 'Shipping'];
                                    if ($status == 'Cancelled') {
                                        foreach ($steps as $s): ?>
                                            <div class="status-step"><?= $s ?></div>
                                        <?php endforeach; ?>
                                        <div class="status-step cancelled"><i class="fa-solid fa-xmark"></i> Cancelled</div>
                                        <?php
                                    } else {
                                        $currentIndex = array_search($status, $steps);
                                        foreach ($steps as $i => $s):
                                            $isActive = ($i <= $currentIndex);
                                        ?>
                                            <div class="status-step <?= $isActive ? 'active' : '' ?>">
                                                <?php if ($isActive): ?><i class="fa-solid fa-check"></i><?php endif; ?>
                                                <?= $s ?>
                                            </div>
                                    <?php
                                        endforeach;
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>

                        <!-- Remarks -->
                        <?php if (val($row, 'remarks')): ?>
                            <div class="col-12">
                                <div class="section-card">
                                    <div class="section-title">
                                        <span class="icon-box"><i class="fa-solid fa-comment-dots"></i></span>
                                        Remarks
                                    </div>
                                    <div class="remarks-box"><?= nl2br(val($row, 'remarks')) ?></div>
                                </div>
                            </div>
                        <?php endif; ?>

                    </div>

                </div>

                <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
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