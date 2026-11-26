<?php
session_start();
include "config.php";

if (!isset($_SESSION['user_id'])) {
    header("Location:login.php");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location:b2b-my-orders.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$id = (int)$_GET['id'];

/* ===========================
   Load the requested inquiry row
   (used to identify which "order" this belongs to)
=========================== */

$stmt = mysqli_prepare($conn, "
SELECT *
FROM b2b_inquiries
WHERE id=?
AND user_id=?
LIMIT 1
");
mysqli_stmt_bind_param($stmt, "ii", $id, $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {
    die("Inquiry not found.");
}

$anchor = mysqli_fetch_assoc($result);

/* ===========================
   All products submitted together share the same user_id
   and the same created_at timestamp (they're inserted in a
   single loop during checkout), so we use that as the "order"
   grouping key.

   NOTE: for a fully reliable grouping across orders placed in
   the same second, consider adding a dedicated order_no /
   group_id column to b2b_inquiries and filtering by that
   instead — this timestamp match is a practical stand-in.
=========================== */

$stmt = mysqli_prepare($conn, "
SELECT
    b.*,
    p.name,
    p.image AS product_image,
    p.price AS product_price,
    pv.variant_name,
    pv.images AS variant_images,
    pv.variant_price
FROM b2b_inquiries b
LEFT JOIN products p
    ON p.id = b.product_id
LEFT JOIN product_variants pv
    ON pv.id = b.variant_id
WHERE b.user_id = ?
AND b.created_at = ?
ORDER BY b.id ASC
");
mysqli_stmt_bind_param($stmt, "is", $user_id, $anchor['created_at']);
mysqli_stmt_execute($stmt);
$orderResult = mysqli_stmt_get_result($stmt);

$items = [];
$total_products = 0;
$total_qty      = 0;
$total_amount   = 0;

// while ($item = mysqli_fetch_assoc($orderResult)) {

//     $price = $item['product_price'];

//     if ($item['variant_id'] > 0 && $item['variant_price'] > 0) {
//         $price = $item['variant_price'];
//     }

//     $image = "admin/uploads/" . $item['product_image'];

//     if (!empty($item['variant_images'])) {
//         $img = explode(",", $item['variant_images']);
//         if (!empty($img[0])) {
//             $image = "admin/img/" . trim($img[0]);
//         }
//     }

//     $line_total = $price * $item['required_qty'];

//     $total_products++;
//     $total_qty    += (int) $item['required_qty'];
//     $total_amount += $line_total;

//     $item['_image']      = $image;
//     $item['_price']      = $price;
//     $item['_line_total']  = $line_total;

//     $items[] = $item;
// }

while ($item = mysqli_fetch_assoc($orderResult)) {

    /* =========================================================
       GET ORIGINAL DATABASE PRICE
       Variant price ko priority milegi
    ========================================================= */

    $original_price = (float)$item['product_price'];

    if (
        (int)$item['variant_id'] > 0 &&
        isset($item['variant_price']) &&
        (float)$item['variant_price'] > 0
    ) {
        $original_price = (float)$item['variant_price'];
    }


    /* =========================================================
       B2B PRICE = DATABASE PRICE + 20%
       
       Example:
       DB ₹100  -> B2B ₹120
       DB ₹200  -> B2B ₹240
       DB ₹500  -> B2B ₹600
    ========================================================= */

    $price = round($original_price * 1.20, 2);


    /* =========================================================
       IMAGE
    ========================================================= */

    $image = "admin/uploads/" . ($item['product_image'] ?? '');

    if (!empty($item['variant_images'])) {

        $img = explode(",", $item['variant_images']);

        if (!empty($img[0])) {
            $image = "admin/img/" . trim($img[0]);
        }
    }


    /* =========================================================
       LINE TOTAL
    ========================================================= */

    $required_qty = (int)$item['required_qty'];

    $line_total = round($price * $required_qty, 2);


    /* =========================================================
       TOTALS
    ========================================================= */

    $total_products++;

    $total_qty += $required_qty;

    $total_amount = round(
        $total_amount + $line_total,
        2
    );


    /* =========================================================
       EMPTY VALUES => NA
    ========================================================= */

    $item['name'] = !empty(trim((string)$item['name']))
        ? $item['name']
        : 'NA';

    $item['variant_name'] = !empty(trim((string)$item['variant_name']))
        ? $item['variant_name']
        : 'NA';

    $item['_image'] = $image;

    $item['_price'] = $price;

    $item['_line_total'] = $line_total;


    $items[] = $item;
}

// The row used for the company / delivery / remarks details section
$row = $anchor;

/* =========================================================
   EMPTY VALUES => NA
========================================================= */

function displayNA($value)
{
    $value = trim((string)$value);

    return $value === '' ? 'NA' : $value;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>B2B Inquiry Details</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<!-- Fonts -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<!-- Site styles -->
<link rel="stylesheet" href="../css/style.css">

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flag-icons/css/flag-icons.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

<style>

    body {
        font-family: 'Inter', sans-serif;
        background: #f7f8fa;
        font-size: 0.9rem;
    }

    .back-link {
        font-size: 0.85rem;
        font-weight: 600;
        color: #545a68;
        border: 1px solid #e2e6ec;
        border-radius: 50px;
        padding: 8px 18px;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .back-link:hover {
        background: #1f2530;
        color: #fff !important;
        border-color: #1f2530;
    }

    .page-title {
        font-family: 'Fraunces', serif;
        font-weight: 600;
        color: #1f2530;
        font-size: 1.4rem;
    }

    .status-pill {
        font-size: 0.75rem;
        font-weight: 600;
        padding: 5px 14px;
        border-radius: 50px;
        text-transform: capitalize;
    }

    .status-pending   { background:#fff6e0; color:#a3720a; }
    .status-confirmed,
    .status-approved  { background:#e9f9ee; color:#1f8a4c; }
    .status-rejected,
    .status-cancelled { background:#fdeeee; color:#d9534f; }
    .status-default   { background:#eef1f6; color:#5a6272; }

    /* ---- Cards ---- */
    .b2b-card {
        border: 1px solid #eceff3;
        border-radius: 16px;
        background: #fff;
        box-shadow: 0 2px 10px rgba(20, 20, 43, 0.04);
        overflow: hidden;
        margin-bottom: 22px;
    }

    .b2b-card .card-header {
        background: #fff;
        border-bottom: 1px solid #f0f2f6;
        font-weight: 600;
        font-size: 0.95rem;
        color: #1f2530;
        padding: 15px 20px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .b2b-card .card-header i {
        color: #1f8a4c;
    }

    .b2b-card .card-body {
        padding: 20px 22px;
    }

    /* ---- Info grid ---- */
    .info-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 14px 24px;
    }

    .info-item .label {
        font-size: 0.74rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        color: #9aa1ae;
        margin-bottom: 3px;
    }

    .info-item .value {
        font-size: 0.9rem;
        font-weight: 600;
        color: #1f2530;
    }

    /* ---- Stat cards (totals) ---- */
    .stats-row {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 16px;
        margin-bottom: 22px;
    }

    .stat-card {
        background: #fff;
        border: 1px solid #eceff3;
        border-radius: 16px;
        padding: 18px 20px;
        box-shadow: 0 2px 10px rgba(20, 20, 43, 0.04);
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .stat-icon {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        flex-shrink: 0;
    }

    .stat-icon.products { background:#eaf1ff; color:#2d6cdf; }
    .stat-icon.qty      { background:#fff6e0; color:#a3720a; }
    .stat-icon.amount   { background:#e9f9ee; color:#1f8a4c; }

    .stat-label {
        font-size: 0.74rem;
        color: #9aa1ae;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.4px;
    }

    .stat-value {
        font-size: 1.15rem;
        font-weight: 700;
        color: #1f2530;
    }

    /* ---- Product table ---- */
    .product-row {
        display: flex;
        gap: 14px;
        padding: 16px 0;
        border-bottom: 1px solid #f0f2f6;
    }

    .product-row:last-child {
        border-bottom: none;
    }

    .product-img {
        width: 68px;
        height: 68px;
        object-fit: cover;
        border-radius: 10px;
        border: 1px solid #eceff3;
        flex-shrink: 0;
    }

    .product-name {
        font-size: 0.9rem;
        font-weight: 700;
        color: #1f2530;
    }

    .product-variant {
        font-size: 0.78rem;
        color: #8a92a3;
    }

    .product-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 14px;
        margin-top: 6px;
        font-size: 0.8rem;
        color: #545a68;
    }

    .product-meta b {
        color: #1f2530;
        font-weight: 700;
    }

    .product-line-total {
        margin-left: auto;
        text-align: right;
        font-size: 0.92rem;
        font-weight: 700;
        color: #1f8a4c;
        white-space: nowrap;
    }

    /* ---- Remarks box ---- */
    .remarks-text {
        color: #545a68;
        font-size: 0.88rem;
        line-height: 1.6;
    }

    /* ---- Responsive ---- */
    @media (max-width: 767.98px) {
        .info-grid {
            grid-template-columns: 1fr;
        }

        .stats-row {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 575.98px) {
        .page-title {
            font-size: 1.2rem;
        }

        .b2b-card .card-body {
            padding: 16px;
        }

        .product-row {
            flex-wrap: wrap;
        }

        .product-line-total {
            margin-left: 0;
            width: 100%;
            text-align: left;
            margin-top: 6px;
        }
    }

</style>

</head>

<body>

<?php include "header.php"; ?>

<div class="container py-5">

    <a href="b2b-my-orders.php" class="back-link mb-4">
        <i class="bi bi-arrow-left"></i> Back to Orders
    </a>

    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-4 mb-4">
        <h3 class="page-title mb-0">B2B Inquiry #<?= (int) $row['id']; ?></h3>

        <?php
            $status = strtolower(trim($row['status']));
            $status_class = match (true) {
                $status === 'pending' => 'status-pending',
                in_array($status, ['confirmed', 'approved']) => 'status-confirmed',
                in_array($status, ['rejected', 'cancelled']) => 'status-rejected',
                default => 'status-default',
            };
        ?>
        <span class="status-pill <?= $status_class; ?>">
            <?= htmlspecialchars($row['status']); ?>
        </span>
    </div>

    <!-- ===== Summary Stat Cards ===== -->
    <div class="stats-row">

        <div class="stat-card">
            <div class="stat-icon products">
                <i class="bi bi-box-seam-fill"></i>
            </div>
            <div>
                <div class="stat-label">Total Products</div>
                <div class="stat-value"><?= $total_products; ?></div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon qty">
                <i class="bi bi-stack"></i>
            </div>
            <div>
                <div class="stat-label">Total Quantity</div>
                <div class="stat-value"><?= $total_qty; ?></div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon amount">
                <i class="bi bi-currency-rupee"></i>
            </div>
            <div>
                <div class="stat-label">Total Amount</div>
                <div class="stat-value">₹<?= number_format($total_amount, 2); ?></div>
            </div>
        </div>

    </div>

    <div class="row">

        <div class="col-lg-8">

            <!-- ===== Company / Contact Details ===== -->
            <div class="b2b-card">
                <div class="card-header">
                    <i class="bi bi-building"></i> Company &amp; Contact Details
                </div>
                <div class="card-body">
                    <div class="info-grid">

                        <div class="info-item">
                            <div class="label">Date</div>
                            <div class="value"><?= date("d M Y, h:i A", strtotime($row['created_at'])); ?></div>
                        </div>

                        <div class="info-item">
                            <div class="label">Business Type</div>
                            <div class="value"><?= htmlspecialchars(displayNA($row['business_type'])); ?></div>
                        </div>

                        <div class="info-item">
                            <div class="label">Company</div>
                            <div class="value"><?= htmlspecialchars(displayNA($row['company_name'])); ?></div>
                        </div>

                        <div class="info-item">
                            <div class="label">GST Number</div>
                            <div class="value"><?= htmlspecialchars(displayNA($row['gst_number'])); ?></div>
                        </div>

                        <div class="info-item">
                            <div class="label">Contact Person</div>
                            <div class="value"><?= htmlspecialchars(displayNA($row['contact_person'])); ?></div>
                        </div>

                        <div class="info-item">
                            <div class="label">Email</div>
                            <div class="value"><?= htmlspecialchars(displayNA($row['email'])); ?></div>
                        </div>

                        <div class="info-item">
                            <div class="label">Phone</div>
                            <div class="value"><?= htmlspecialchars(displayNA($row['phone'])); ?></div>
                        </div>

                    </div>
                </div>
            </div>

            <!-- ===== Products ===== -->
            <div class="b2b-card">
                <div class="card-header">
                    <i class="bi bi-bag-check-fill"></i> Product Details
                </div>
                <div class="card-body">

                    <?php foreach ($items as $item) { ?>

                        <div class="product-row">

                            <img src="../<?= $item['_image']; ?>" class="product-img" alt="<?= htmlspecialchars($item['name']); ?>">

                            <div class="flex-grow-1">
                                <div class="product-name"><?= htmlspecialchars($item['name']); ?></div>

                                <?php if (!empty($item['variant_name'])) { ?>
                                    <div class="product-variant"><?= htmlspecialchars($item['variant_name']); ?></div>
                                <?php } ?>

                                <div class="product-meta">
                                    <span><b>Qty:</b> <?= (int) $item['required_qty']; ?></span>
                                    <span><b>Price:</b> ₹<?= number_format($item['_price'], 2); ?></span>
                                </div>
                            </div>

                            <div class="product-line-total">
                                ₹<?= number_format($item['_line_total'], 2); ?>
                            </div>

                        </div>

                    <?php } ?>

                </div>
            </div>

        </div>

        <div class="col-lg-4">

            <!-- ===== Delivery Details ===== -->
            <div class="b2b-card">
                <div class="card-header">
                    <i class="bi bi-truck"></i> Delivery Details
                </div>
                <div class="card-body">

                    <div class="info-item mb-3">
                        <div class="label">Delivery Date</div>
                        <div class="value">
                            <?= htmlspecialchars(displayNA($row['delivery_date'])); ?>
                           
                        </div>
                    </div>

                    <div class="info-item mb-3">
                        <div class="label">Country</div>
                        <div class="value"><?= htmlspecialchars(displayNA($row['country'])); ?></div>
                    </div>

                    <div class="info-item mb-3">
                        <div class="label">State</div>
                        <div class="value"><?= htmlspecialchars(displayNA($row['state'])); ?></div>
                    </div>

                    <div class="info-item mb-3">
                        <div class="label">City</div>
                        <div class="value"><?= htmlspecialchars(displayNA($row['city'])); ?></div>
                    </div>

                    <div class="info-item">
                        <div class="label">Address</div>
                        <div class="value" style="font-weight:500;">
                            <?= nl2br(htmlspecialchars(displayNA($row['address']))); ?>
                        </div>
                    </div>

                </div>
            </div>

            <!-- ===== Remarks ===== -->
            <div class="b2b-card">
                <div class="card-header">
                    <i class="bi bi-chat-left-text-fill"></i> Remarks
                </div>
                <div class="card-body">
                    <p class="remarks-text mb-0">
                        <?= !empty($row['remarks']) ? nl2br(htmlspecialchars($row['remarks'])) : "No remarks."; ?>
                    </p>
                </div>
            </div>

        </div>

    </div>

</div>

<?php include "../footer.php"; ?>

</body>
</html>