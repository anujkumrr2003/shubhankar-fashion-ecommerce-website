<?php
session_start();
include "config.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login");
    exit;
}

$user_id = (int)$_SESSION['user_id'];

/*--------------------------------------------------
COPY PROFILE ADDRESS TO user_addresses
ONLY IF NO ADDRESS EXISTS
---------------------------------------------------*/
$check = mysqli_query($conn, "
    SELECT id
    FROM b2b_user_addresses
    WHERE user_id='$user_id'
    LIMIT 1
");

if (mysqli_num_rows($check) == 0) {

    $userQuery = mysqli_query($conn, "
        SELECT
            full_name,
            phone,
            country,
            state,
            city,
            address,
            pincode
        FROM user
        WHERE id='$user_id'
        LIMIT 1
    ");

    if (mysqli_num_rows($userQuery) > 0) {

        $u = mysqli_fetch_assoc($userQuery);

        if (
            !empty(trim($u['full_name'])) &&
            !empty(trim($u['phone'])) &&
            !empty(trim($u['address']))
        ) {

            $stmt = mysqli_prepare($conn, "
INSERT INTO b2b_user_addresses
(
    user_id,

    company_name,
    gst_number,
    business_type,
    delivery_date,
    remarks,

    full_name,
    phone,
    state,
    city,
    address,
    pincode,
    address_type,
    region,

    billing_company_name,
    billing_gst_number,
    billing_business_type,
    billing_delivery_date,
    billing_remarks,

    billing_full_name,
    billing_phone,
    billing_state,
    billing_city,
    billing_address,
    billing_pincode,
    billing_address_type,
    billing_region,

    same_as_shipping,
    is_default,
    created_at
)
VALUES
(
    ?,

    ?,?,?,?,?,

    ?,?,?,?,?,?,?,?,

    ?,?,?,?,?,

    ?,?,?,?,?,?,?,?,

    ?,?,
    NOW()
)
");

            $company_name = "Individual";
            $gst_number = "";
            $business_type = "";
            $delivery_date = NULL;
            $remarks = "";

            $region = $u['state'];

            // $billing_company_name = $company_name;
            // $billing_gst_number = $gst_number;
            // $billing_business_type = $business_type;
            // $billing_delivery_date = NULL;
            // $billing_remarks = "";

            // $billing_full_name = $u['full_name'];
            // $billing_phone = $u['phone'];
            // $billing_state = $u['state'];
            // $billing_city = $u['city'];
            // $billing_address = $u['address'];
            // $billing_pincode = $u['pincode'];
            // $billing_address_type = "Home";
            // $billing_region = $u['state'];
            $billing_company_name = "";
            $billing_gst_number = "";
            $billing_business_type = "";
            $billing_delivery_date = NULL;
            $billing_remarks = "";

            $billing_full_name = "";
            $billing_phone = "";
            $billing_state = "";
            $billing_city = "";
            $billing_address = "";
            $billing_pincode = "";
            $billing_address_type = "";
            $billing_region = "";

            $same_as_shipping = 1;

            $same_as_shipping = 1;
            $address_type = "Home";
            $is_default = 1;

            mysqli_stmt_bind_param(
                $stmt,
                "issssssssssssssssssssssssssii",

                $user_id,

                $company_name,
                $gst_number,
                $business_type,
                $delivery_date,
                $remarks,

                $u['full_name'],
                $u['phone'],
                $u['state'],
                $u['city'],
                $u['address'],
                $u['pincode'],
                $address_type,
                $region,

                $billing_company_name,
                $billing_gst_number,
                $billing_business_type,
                $billing_delivery_date,
                $billing_remarks,

                $billing_full_name,
                $billing_phone,
                $billing_state,
                $billing_city,
                $billing_address,
                $billing_pincode,
                $billing_address_type,
                $billing_region,

                $same_as_shipping,
                $is_default
            );

            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }
}

/*--------------------------------------------------
GET USER PROFILE ADDRESS
---------------------------------------------------*/

$userProfile = mysqli_query($conn, "
    SELECT
        full_name,
        phone,
        country,
        state,
        city,
        address,
        pincode
    FROM user
    WHERE id='$user_id'
    LIMIT 1
");

$profile = mysqli_fetch_assoc($userProfile);

/*--------------------------------------------------
GET ALL SAVED ADDRESSES
---------------------------------------------------*/

$result = mysqli_query($conn, "
SELECT
    id,

    company_name,
    gst_number,
    business_type,
    delivery_date,
    remarks,

    full_name,
    phone,
    state,
    city,
    address,
    pincode,
    address_type,
    region,

    billing_company_name,
    billing_gst_number,
    billing_business_type,
    billing_delivery_date,
    billing_remarks,

    billing_full_name,
    billing_phone,
    billing_state,
    billing_city,
    billing_address,
    billing_pincode,
    billing_address_type,
    billing_region,

    same_as_shipping,
    is_default,
    created_at

FROM b2b_user_addresses

WHERE user_id='$user_id'

ORDER BY is_default DESC, created_at DESC
");
?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

   <title>My Addresses | Shubhankar India</title>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<meta name="title" content="My Addresses | Shubhankar India">

<meta name="description" content="Manage your saved shipping and billing addresses securely in your Shubhankar India account for faster checkout and order management.">

<meta name="robots" content="noindex,nofollow,noarchive,nosnippet">

<link rel="canonical" href="https://shubhankarfashion.com/in/my-address">

<meta name="author" content="Shubhankar">
<meta name="publisher" content="Shubhankar">

<!-- Open Graph -->
<meta property="og:type" content="website">
<meta property="og:site_name" content="Shubhankar India">
<meta property="og:title" content="My Addresses | Shubhankar India">
<meta property="og:description" content="Manage your saved delivery and billing addresses securely.">
<meta property="og:url" content="https://shubhankarfashion.com/in/my-address">
<meta property="og:image" content="https://shubhankarfashion.com/img/logo-removebg-preview.png">
<meta property="og:image:secure_url" content="https://shubhankarfashion.com/img/logo-removebg-preview.png">
<meta property="og:image:type" content="image/png">
<meta property="og:image:alt" content="Shubhankar India Logo">

<!-- Twitter -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="My Addresses | Shubhankar India">
<meta name="twitter:description" content="Manage your saved addresses securely in your Shubhankar India account.">
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
  "name":"My Addresses",
  "url":"https://shubhankarfashion.com/in/my-address",
  "isPartOf":{
    "@type":"WebSite",
    "name":"Shubhankar India",
    "url":"https://shubhankarfashion.com/in/"
  }
}
</script>


    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">

    <style>
        /* ============ My Addresses — Page Styling ============ */

        body {
            font-family: 'Inter', sans-serif;
            background: #f7f8fa;
        }

        .addresses-heading h3 {
            font-family: 'Fraunces', serif;
            font-weight: 600;
            color: #1f2530;
            margin-bottom: 4px;
        }

        .addresses-heading p {
            color: #8a92a3;
            font-size: 0.92rem;
            margin-bottom: 0;
        }

        .btn-add-address {
            background: #1f8a4c;
            border: none;
            color: #fff;
            font-weight: 500;
            padding: 10px 22px;
            border-radius: 50px;
            box-shadow: 0 4px 14px rgba(31, 138, 76, 0.28);
            transition: all 0.2s ease;
        }

        .btn-add-address:hover {
            background: #17703d;
            color: #fff;
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(31, 138, 76, 0.35);
        }

        .alert-success {
            border: none;
            background: #e9f9ee;
            color: #1f8a4c;
            border-radius: 14px;
            font-weight: 500;
            padding: 14px 20px;
        }

        /* ---- Address Card ---- */
        .addr-card {
            border: 1px solid #eceff3;
            /* border-radius: 18px; */
            background: #fff;
            box-shadow: 0 2px 10px rgba(20, 20, 43, 0.04);
            transition: all 0.25s ease;
            overflow: hidden;
            position: relative;
        }

        .addr-card:hover {
            box-shadow: 0 10px 26px rgba(20, 20, 43, 0.08);
            transform: translateY(-3px);
            border-color: #dfe4ea;
        }

        .addr-card.is-default {
            border-color: #b7e4c7;
            background: linear-gradient(180deg, #f6fdf9 0%, #ffffff 60%);
        }

        .addr-card .card-body {
            padding: 24px 26px;
        }

        .addr-name {
            font-size: 1.05rem;
            font-weight: 700;
            color: #1f2530;
            margin-bottom: 6px;
        }

        .addr-phone {
            color: #8a92a3;
            font-size: 0.88rem;
        }

        .addr-phone i {
            margin-right: 5px;
        }

        .addr-text {
            color: #545a68;
            font-size: 0.92rem;
            line-height: 1.65;
            margin: 14px 0 16px;
        }

        .addr-text i {
            color: #b0b5c1;
            margin-right: 8px;
        }

        /* Badges */
        .badge-pill {
            font-size: 0.72rem;
            font-weight: 600;
            padding: 6px 12px;
            border-radius: 50px;
            letter-spacing: 0.2px;
        }

        .badge-default {
            background: #e9f9ee;
            color: #1f8a4c;
        }

        .badge-profile {
            background: #fff6e0;
            color: #a3720a;
        }

        .badge-type {
            background: #eef1f6;
            color: #5a6272;
            border: 1px solid #e2e6ec;
        }

        /* Action buttons */
        .addr-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 16px;
            padding-top: 16px;
            border-top: 1px dashed #edeff3;
        }

        .btn-chip {
            font-size: 0.82rem;
            font-weight: 500;
            padding: 7px 16px;
            border-radius: 50px;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            border: 1px solid transparent;
            text-decoration: none;
        }

        .btn-chip-edit {
            background: #eaf1ff;
            color: #2d6cdf;
        }

        .btn-chip-edit:hover {
            background: #2d6cdf;
            color: #fff;
        }

        .btn-chip-delete {
            background: #fdeeee;
            color: #d9534f;
        }

        .btn-chip-delete:hover {
            background: #d9534f;
            color: #fff;
        }

        .btn-chip-profile {
            background: #fff6e0;
            color: #a3720a;
        }

        .btn-chip-profile:hover {
            background: #f0a800;
            color: #fff;
        }

        .btn-chip-default {
            background: transparent;
            color: #1f8a4c;
            border: 1px solid #b7e4c7;
        }

        .btn-chip-default:hover {
            background: #1f8a4c;
            color: #fff;
        }

        /* Default ribbon */
        .default-ribbon {
            position: absolute;
            top: 18px;
            right: 18px;
            color: #1f8a4c;
            font-size: 1.2rem;
        }

        /* Empty state */
        .empty-state {
            text-align: center;
            padding: 70px 20px;
            background: #fff;
            border-radius: 20px;
            border: 1px dashed #e2e6ec;
        }

        .empty-state i {
            font-size: 3rem;
            color: #cbd2de;
            margin-bottom: 14px;
            display: block;
        }

        .empty-state h5 {
            color: #1f2530;
            font-weight: 600;
        }

        .empty-state p {
            color: #8a92a3;
            font-size: 0.92rem;
        }

        @media (max-width: 576px) {
            .addr-card .card-body {
                padding: 20px;
            }

            .addr-actions {
                gap: 6px;
            }
        }
    </style>

</head>

<body>

    <?php include 'header.php'; ?>

    <section class="py-5">

        <div class="container">

            <div class="d-flex justify-content-between align-items-center mb-4 addresses-heading flex-wrap gap-3">

                <div>
                    <h3>My B2B Addresses</h3>
                    <p>Manage your shipping and billing addresses</p>
                </div>

                <a href="add-address.php" class="btn-add-address text-white">
                    <i class="bi bi-plus-lg me-1"></i> Add New Address
                </a>

            </div>

            <?php if (isset($_SESSION['success'])) { ?>
                <div class="alert alert-success d-flex align-items-center gap-2">
                    <i class="bi bi-check-circle-fill"></i>
                    <?= $_SESSION['success']; ?>
                </div>
            <?php unset($_SESSION['success']);
            } ?>

            <?php if (mysqli_num_rows($result) > 0) { ?>

                <div class="row g-4">

                    <?php while ($row = mysqli_fetch_assoc($result)) { ?>

                        <div class="col-lg-6">

                            <div class="addr-card <?= ($row['is_default']) ? 'is-default' : ''; ?>">

                                <?php if ($row['is_default']) { ?>
                                    <span class="default-ribbon">
                                        <i class="bi bi-patch-check-fill"></i>
                                    </span>
                                <?php } ?>

                                <div class="card-body">

                                    <div class="d-flex justify-content-between align-items-start">

                                        <div>

                                            <h5 class="addr-name mb-1">
                                                <?= htmlspecialchars($row['full_name']); ?>
                                            </h5>

                                            <div class="addr-phone">
                                                <i class="bi bi-telephone-fill"></i>
                                                <?= htmlspecialchars($row['phone']); ?>
                                            </div>

                                        </div>

                                        <div class="text-end">

                                            <?php if ($row['is_default']) { ?>
                                                <span class="badge-pill badge-default">Default</span>
                                            <?php } ?>

                                            <span class="badge-pill badge-type">
                                                <?= htmlspecialchars($row['address_type']); ?>
                                            </span>

                                        </div>

                                    </div>

                                    <hr>

                                    <h6 class="fw-bold mb-2">
                                        <i class="bi bi-building me-1"></i>
                                        Business Details
                                    </h6>

                                    <table class="table table-sm table-borderless mb-3">

                                        <tr>
                                            <th width="40%">Company :</th>
                                            <td><?= htmlspecialchars($row['company_name']); ?></td>
                                        </tr>

                                       
<tr>
    <th>GST Number :</th>
    <td>
        <?= !empty($row['gst_number'])
            ? htmlspecialchars($row['gst_number'])
            : 'NA'; ?>
    </td>
</tr>
 


                                        <tr>
                                            <th>Business Type :</th>
                                            <td><?= htmlspecialchars($row['business_type']); ?></td>
                                        </tr>

                                        <tr>
                                            <th>Delivery Date :</th>
                                            <td>
                                                <?= !empty($row['delivery_date']) ? date('d M Y', strtotime($row['delivery_date'])) : '-'; ?>
                                            </td>
                                        </tr>

                                      <tr>
    <th>Remarks :</th>
    <td>
        <div class="remarks-table">
            
                    <?= !empty($row['remarks'])
            ? htmlspecialchars($row['remarks'])
            : 'NA'; ?>
        </div>
    </td>
</tr>
<style>

    .remarks-table{
    white-space: pre-line;
    word-break: break-word;
    line-height: 1.8;
    color: #555;
}
</style>

                                    </table>

                                    <h6 class="fw-bold mb-2">
                                        <i class="bi bi-truck me-1"></i>
                                        Shipping Address
                                    </h6>

                                    <div class="addr-text">

                                        <?= htmlspecialchars($row['address']); ?><br>

                                        <?= htmlspecialchars($row['city']); ?>,
                                        <?= htmlspecialchars($row['state']); ?><br>

                                        <?= htmlspecialchars($row['region']); ?>
                                        -
                                        <?= htmlspecialchars($row['pincode']); ?>

                                    </div>

                                    <?php
                                    if (
                                        !empty($row['billing_full_name']) ||
                                        !empty($row['billing_address']) ||
                                        !empty($row['billing_company_name'])
                                    ) {
                                    ?>

                                        <hr>

                                        <h6 class="fw-bold mb-2">
                                            <i class="bi bi-receipt me-1"></i>
                                            Billing Address
                                        </h6>

                                        <table class="table table-sm table-borderless">

                                            <tr>
                                                <th width="40%">Company</th>
                                                <td><?= htmlspecialchars($row['billing_company_name']); ?></td>
                                            </tr>

                                            <tr>
                                                <th>GST</th>
                                                <td><?= htmlspecialchars($row['billing_gst_number']); ?></td>
                                            </tr>

                                            <tr>
                                                <th>Name</th>
                                                <td><?= htmlspecialchars($row['billing_full_name']); ?></td>
                                            </tr>

                                            <tr>
                                                <th>Phone</th>
                                                <td><?= htmlspecialchars($row['billing_phone']); ?></td>
                                            </tr>

                                            <tr>
                                                <th>Address</th>
                                                <td>
                                                    <?= htmlspecialchars($row['billing_address']); ?><br>

                                                    <?= htmlspecialchars($row['billing_city']); ?>,
                                                    <?= htmlspecialchars($row['billing_state']); ?><br>

                                                    <?= htmlspecialchars($row['billing_region']); ?>
                                                    -
                                                    <?= htmlspecialchars($row['billing_pincode']); ?>
                                                </td>
                                            </tr>

                                        </table>

                                    <?php } ?>

                                    <div class="addr-actions">

                                        <a href="edit-address.php?id=<?= $row['id']; ?>" class="btn-chip btn-chip-edit">
                                            <i class="bi bi-pencil-fill"></i>
                                            Edit
                                        </a>

                                        <a href="delete-address.php?id=<?= $row['id']; ?>"
                                            class="btn-chip btn-chip-delete"
                                            onclick="return confirm('Delete this address?')">

                                            <i class="bi bi-trash-fill"></i>
                                            Delete

                                        </a>

                                        <?php if (!$row['is_default']) { ?>

                                            <a href="set-default-address.php?id=<?= $row['id']; ?>" class="btn-chip btn-chip-default">

                                                <i class="bi bi-star-fill"></i>
                                                Set Default

                                            </a>

                                        <?php } ?>

                                    </div>

                                </div>

                            </div>

                        </div>

                    <?php } ?>

                </div>

            <?php } else { ?>

                <div class="empty-state">

                    <i class="bi bi-geo-alt"></i>

                    <h5>No Address Found</h5>

                    <p>You have not added any B2B address yet.</p>

                    <!-- <a href="add-address.php" class="btn-add-address">

                        <i class="bi bi-plus-lg me-1"></i>

                        Add New 

                    </a> -->

                </div>

            <?php } ?>

        </div>

    </section>

    <?php include 'footer.php'; ?>

</body>

</html>