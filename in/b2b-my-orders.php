<?php
session_start();
include "config.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = (int) $_SESSION['user_id']; // cast to int since it's interpolated below

$sql = mysqli_query($conn, "
SELECT
    b.*,
    p.name AS product_name
FROM b2b_inquiries b
LEFT JOIN products p
ON p.id = b.product_id
WHERE b.user_id='$user_id'
ORDER BY b.id DESC
");

// Pull everything into an array once so we can render both the
// desktop table and the mobile card list from the same data.
$inquiries = [];
if ($sql) {
    while ($row = mysqli_fetch_assoc($sql)) {
        $inquiries[] = $row;
    }
}

// Central place to map a status to a badge color + icon so the
// table view and card view always stay in sync.
function b2b_status_meta($status) {
    switch ($status) {
        case "Pending":
            return ["color" => "warning", "icon" => "bi-hourglass-split"];
        case "Approved":
            return ["color" => "success", "icon" => "bi-check-circle-fill"];
        case "Rejected":
            return ["color" => "danger", "icon" => "bi-x-circle-fill"];
        case "Completed":
            return ["color" => "primary", "icon" => "bi-box-seam-fill"];
        default:
            return ["color" => "secondary", "icon" => "bi-dot"];
    }
}
?>




<!DOCTYPE html>
<html>

<head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>My B2B Orders | Shubhankar India</title>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<meta name="title" content="My B2B Orders | Shubhankar India">

<meta name="description" content="View and manage your B2B orders, quotations and bulk purchase history securely with your Shubhankar India account.">

<meta name="robots" content="noindex,nofollow,noarchive,nosnippet">

<link rel="canonical" href="https://shubhankarfashion.com/in/b2b-my-orders">

<meta name="author" content="Shubhankar">
<meta name="publisher" content="Shubhankar">

<!-- Open Graph -->
<meta property="og:type" content="website">
<meta property="og:site_name" content="Shubhankar India">
<meta property="og:title" content="My B2B Orders | Shubhankar India">
<meta property="og:description" content="View your B2B order history securely.">
<meta property="og:url" content="https://shubhankarfashion.com/in/b2b-my-orders">
<meta property="og:image" content="https://shubhankarfashion.com/img/logo-removebg-preview.png">
<meta property="og:image:secure_url" content="https://shubhankarfashion.com/img/logo-removebg-preview.png">
<meta property="og:image:type" content="image/png">
<meta property="og:image:alt" content="Shubhankar India Logo">

<!-- Twitter -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="My B2B Orders | Shubhankar India">
<meta name="twitter:description" content="View and manage your B2B orders securely.">
<meta name="twitter:image" content="https://shubhankarfashion.com/img/logo-removebg-preview.png">

<!-- Favicon -->
<link rel="icon" type="image/png" href="https://shubhankarfashion.com/img/logo-removebg-preview.png">
<link rel="shortcut icon" href="https://shubhankarfashion.com/img/logo-removebg-preview.png">
<link rel="apple-touch-icon" href="https://shubhankarfashion.com/img/logo-removebg-preview.png">

<meta name="theme-color" content="#000000">
<meta name="application-name" content="Shubhankar India">
<meta name="apple-mobile-web-app-title" content="Shubhankar India">

<script type="application/ld+json">
{
  "@context":"https://schema.org",
  "@type":"WebPage",
  "name":"My B2B Orders",
  "url":"https://shubhankarfashion.com/in/b2b-my-orders",
  "isPartOf":{
    "@type":"WebSite",
    "name":"Shubhankar India",
    "url":"https://shubhankarfashion.com/in/"
  }
}
</script>

    <!-- Bootstrap 5 -->
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

:root{
    --sb-forest:#2f4a3c;
    --sb-forest-dark:#233a2f;
    --sb-sage:#7c9070;
    --sb-ivory:#faf7f0;
    --sb-amber:#c9a05c;
    --sb-charcoal:#24302b;
    --sb-line:#e8e3d8;
}

body{
    background:var(--sb-ivory);
    color:var(--sb-charcoal);
    font-family:'Inter', sans-serif;
}

.page-head{
    display:flex;
    align-items:flex-start;
    justify-content:space-between;
    gap:16px;
    flex-wrap:wrap;
    margin-bottom:28px;
}

.page-head h2{
    font-family:'Fraunces', serif;
    font-weight:600;
    color:var(--sb-forest-dark);
    margin-bottom:4px;
}

.page-head p{
    color:#6b7a70;
    margin-bottom:0;
    font-size:0.95rem;
}

.page-head .count-pill{
    background:var(--sb-forest);
    color:#fff;
    border-radius:999px;
    padding:6px 16px;
    font-size:0.85rem;
    font-weight:600;
    white-space:nowrap;
}

.order-box{
    background:#fff;
    border:1px solid var(--sb-line);
    border-radius:14px;
    padding:24px;
    box-shadow:0 6px 20px rgba(47,74,60,0.05);
}

/* ---- Desktop table ---- */
.sb-table thead th{
    font-family:'Inter', sans-serif;
    font-size:0.78rem;
    letter-spacing:0.04em;
    text-transform:uppercase;
    color:#7a8a80;
    border-bottom:2px solid var(--sb-line);
    background:transparent;
    font-weight:600;
    padding:14px 12px;
}

.sb-table td{
    padding:14px 12px;
    vertical-align:middle;
    border-color:var(--sb-line);
    font-size:0.93rem;
}

.sb-table tbody tr{
    transition:background 0.15s ease;
}

.sb-table tbody tr:hover{
    background:rgba(124,144,112,0.07);
}

.product-cell{
    font-weight:600;
    color:var(--sb-forest-dark);
}

.badge-status{
    font-weight:600;
    font-size:0.78rem;
    padding:6px 12px;
    border-radius:999px;
    display:inline-flex;
    align-items:center;
    gap:6px;
}

.btn-view{
    background:var(--sb-forest);
    border:none;
    color:#fff;
    font-size:0.82rem;
    font-weight:600;
    border-radius:8px;
    padding:6px 14px;
    transition:background 0.15s ease;
}

.btn-view:hover{
    background:var(--sb-forest-dark);
    color:#fff;
}

/* ---- Mobile card list ---- */
.sb-card{
    border:1px solid var(--sb-line);
    border-radius:12px;
    padding:16px;
    margin-bottom:14px;
    background:#fffdf9;
}

.sb-card .sb-card-top{
    display:flex;
    justify-content:space-between;
    align-items:flex-start;
    gap:10px;
    margin-bottom:10px;
}

.sb-card .sb-card-product{
    font-family:'Fraunces', serif;
    font-weight:600;
    color:var(--sb-forest-dark);
    font-size:1.02rem;
    margin-bottom:2px;
}

.sb-card .sb-card-meta{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:8px 14px;
    font-size:0.86rem;
    color:#556259;
    margin-bottom:14px;
}

.sb-card .sb-card-meta strong{
    display:block;
    color:var(--sb-charcoal);
    font-weight:600;
    font-size:0.9rem;
}

.sb-card .btn-view{
    width:100%;
    text-align:center;
    padding:9px 14px;
}

/* ---- Empty state ---- */
.empty-state{
    text-align:center;
    padding:60px 20px;
}

.empty-state .empty-icon{
    width:72px;
    height:72px;
    border-radius:50%;
    background:rgba(124,144,112,0.15);
    color:var(--sb-forest);
    display:inline-flex;
    align-items:center;
    justify-content:center;
    font-size:1.8rem;
    margin-bottom:18px;
}

.empty-state h5{
    font-family:'Fraunces', serif;
    color:var(--sb-forest-dark);
    font-weight:600;
}

.empty-state p{
    color:#7a8a80;
    max-width:380px;
    margin:0 auto 20px;
}

@media (max-width: 767.98px){
    .order-box{
        padding:16px;
        border-radius:12px;
    }
    .page-head h2{
        font-size:1.4rem;
    }
}

</style>

</head>

<body>

<?php include 'header.php'; ?>

<div class="container py-5">

<div class="page-head">
    <div>
        <h2>My B2B Inquiries</h2>
        <p>Track the status of every wholesale enquiry you've raised with us.</p>
    </div>
    <?php if (count($inquiries) > 0) { ?>
    <span class="count-pill"><i class="bi bi-inboxes me-1"></i><?= count($inquiries); ?> total</span>
    <?php } ?>
</div>

<div class="order-box">

<?php if (count($inquiries) > 0) { ?>

<!-- Desktop / tablet table -->
<div class="table-responsive d-none d-md-block">

<table class="table sb-table align-middle mb-0">

<thead>
<tr>
<th>#</th>
<th>Product</th>
<th>Company</th>
<th>Required Qty</th>
<th>Date</th>
<th>Status</th>
<th class="text-end">Action</th>
</tr>
</thead>

<tbody>

<?php $i = 1; foreach ($inquiries as $row) {
    $meta = b2b_status_meta($row['status']);
?>

<tr>
<td class="text-muted"><?= $i++; ?></td>
<td class="product-cell"><?= htmlspecialchars($row['product_name']); ?></td>
<td><?= htmlspecialchars($row['company_name']); ?></td>
<td><?= htmlspecialchars($row['required_qty']); ?></td>
<td><?= date("d M Y", strtotime($row['created_at'])); ?></td>
<td>
<span class="badge-status bg-<?= $meta['color']; ?> text-white">
<i class="bi <?= $meta['icon']; ?>"></i>
<?= htmlspecialchars($row['status']); ?>
</span>
</td>
<td class="text-end">
<a href="b2b-order-details.php?id=<?= (int) $row['id']; ?>" class="btn btn-view text-white">
View <i class="bi bi-arrow-right ms-1"></i>
</a>
</td>
</tr>

<?php } ?>

</tbody>

</table>

</div>

<!-- Mobile card list -->
<div class="d-md-none">

<?php foreach ($inquiries as $row) {
    $meta = b2b_status_meta($row['status']);
?>

<div class="sb-card">
    <div class="sb-card-top">
        <div class="sb-card-product"><?= htmlspecialchars($row['product_name']); ?></div>
        <span class="badge-status bg-<?= $meta['color']; ?> text-white">
            <i class="bi <?= $meta['icon']; ?>"></i>
            <?= htmlspecialchars($row['status']); ?>
        </span>
    </div>
    <div class="sb-card-meta">
        <div>
            <strong><?= htmlspecialchars($row['company_name']); ?></strong>
            Company
        </div>
        <div>
            <strong><?= htmlspecialchars($row['required_qty']); ?></strong>
            Required Qty
        </div>
        <div>
            <strong><?= date("d M Y", strtotime($row['created_at'])); ?></strong>
            Date
        </div>
        <div>
            <strong>#<?= (int) $row['id']; ?></strong>
            Inquiry ID
        </div>
    </div>
    <a href="b2b-order-details.php?id=<?= (int) $row['id']; ?>" class="btn btn-view">
        View details <i class="bi bi-arrow-right ms-1"></i>
    </a>
</div>

<?php } ?>

</div>

<?php } else { ?>

<div class="empty-state">
    <div class="empty-icon"><i class="bi bi-inbox"></i></div>
    <h5>No B2B inquiry found</h5>
    <p>You have not submitted any B2B inquiry yet. Once you do, you'll be able to track its status here.</p>
    <a href="shop" class="btn btn-dark text-white">
        Continue shopping
    </a>
</div>

<?php } ?>

</div>

</div>

<?php include 'footer.php'; ?>

</body>
</html>