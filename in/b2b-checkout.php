<?php
session_start();
include "config.php";

if (!isset($_SESSION['user_id'])) {
    header("Location:login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

$user = [];
$addresses = [];

/* ===========================
   USER DETAILS
=========================== */

$stmt = mysqli_prepare($conn, "
SELECT full_name,phone,email,address,city,state,country,pincode
FROM user
WHERE id=?
LIMIT 1
");

mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

if ($result && mysqli_num_rows($result) > 0) {
    $user = mysqli_fetch_assoc($result);
}

/* ===========================
   SAVED ADDRESSES
=========================== */

$stmt = mysqli_prepare($conn, "
SELECT *
FROM b2b_user_addresses
WHERE user_id=?
ORDER BY is_default DESC,id DESC
");

mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);

$resultAddress = mysqli_stmt_get_result($stmt);

while ($row = mysqli_fetch_assoc($resultAddress)) {
    $addresses[] = $row;
}

/* ===========================
   IF NO ADDRESS FOUND
=========================== */

// if (count($addresses) == 0) {

//     if (
//         !empty($user['address']) ||
//         !empty($user['city']) ||
//         !empty($user['state']) ||
//         !empty($user['country'])
//     ) {

//         $addresses[] = [
//             "id" => 0,
//             "full_name" => $user['full_name'],
//             "phone" => $user['phone'],
//             "address" => $user['address'],
//             "city" => $user['city'],
//             "state" => $user['state'],
//             "country" => $user['country'],
//             "pincode" => $user['pincode'],
//             "address_type" => "Profile",
//             "is_default" => 1
//         ];
//     }
// }

$sql = mysqli_query($conn, "
SELECT
    c.*,
    p.name,
    p.image,
    p.price,
    pv.variant_name,
    pv.variant_price,
    pv.images
FROM cart c
INNER JOIN products p
ON p.id=c.product_id
LEFT JOIN product_variants pv
ON pv.id=c.variant_id
WHERE c.user_id='$user_id'
ORDER BY c.id DESC
");

if (mysqli_num_rows($sql) == 0) {
    header("Location:b2b-cart");
    exit;
}

$totalQty = 0;
$grandTotal = 0;
?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>B2B Checkout</title>


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
        /* ============ B2B Checkout — Page Styling ============ */

        body {
            font-family: 'Inter', sans-serif;
            background: #f7f8fa;
            font-size: 0.9rem;
        }

        .b2b-title {
            font-family: 'Fraunces', serif;
            font-weight: 600;
            color: #1f2530;
            font-size: 1.5rem;
            margin-bottom: 4px;
        }

        .b2b-subtitle {
            color: #8a92a3;
            font-size: 0.84rem;
            margin-bottom: 1.75rem;
        }

        /* ---- Cards ---- */
        .b2b-card {
            border: 1px solid #eceff3;
            border-radius: 16px;
            background: #fff;
            box-shadow: 0 2px 10px rgba(20, 20, 43, 0.04);
            overflow: hidden;
        }

        .b2b-card .card-header {
            background: #fff;
            border-bottom: 1px solid #f0f2f6;
            font-weight: 600;
            font-size: 0.94rem;
            color: #1f2530;
            padding: 15px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
        }

        .b2b-card .card-header .header-title {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .b2b-card .card-header i {
            color: #1f8a4c;
        }

        .b2b-card .card-body {
            padding: 20px;
        }

        .btn-add-address {
            background: #eaf6ee;
            color: #1f8a4c;
            border: none;
            font-weight: 600;
            font-size: 0.78rem;
            padding: 7px 14px;
            border-radius: 50px;
            transition: all 0.2s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .btn-add-address:hover {
            background: #1f8a4c;
            color: #fff;
        }

        /* ---- Saved address selector cards ---- */
        .saved-address-label {
            display: block;
            cursor: pointer;
            margin-bottom: 14px;
        }

        .saved-address-label input[type="radio"] {
            position: absolute;
            opacity: 0;
            width: 0;
            height: 0;
        }

        .saved-address-box {
            position: relative;
            border: 1px solid #e9ecef;
            border-radius: 0px;
            padding: 16px 18px;
            background: #fafbfc;
            transition: all 0.2s ease;
            margin-bottom: 5px;
        }

        .saved-address-label:hover .saved-address-box {
            border-color: #c8d6e5;
            background: #fff;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        .saved-address-label input:checked+.saved-address-box {
            border-color: #1f8a4c;
            background: #f6fdf9;
            box-shadow: 0 4px 14px rgba(31, 138, 76, 0.12);
        }

        .saved-address-label input:checked+.saved-address-box::before {
            content: "\f26b";
            font-family: "bootstrap-icons";
            position: absolute;
            top: 14px;
            right: 14px;
            width: 22px;
            height: 22px;
            background: #1f8a4c;
            color: #fff;
            border-radius: 50%;
            font-size: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .saved-address-box h6 {
            font-size: 0.92rem;
            font-weight: 700;
            color: #1f2530;
            margin-bottom: 6px;
        }

        .saved-address-box .badge-pill {
            font-size: 0.68rem;
            font-weight: 600;
            padding: 4px 10px;
            border-radius: 50px;
            background: #e9f9ee;
            color: #1f8a4c;
        }

        .saved-address-meta {
            font-size: 0.8rem;
            color: #545a68;
            line-height: 1.6;
            margin-top: 6px;
        }

        .saved-address-meta b {
            color: #1f2530;
            font-weight: 600;
        }

        .saved-address-actions {
            margin-top: 12px;
            display: flex;
            gap: 8px;
        }

        .btn-chip {
            font-size: 0.76rem;
            font-weight: 500;
            padding: 5px 13px;
            border-radius: 50px;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 5px;
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

        .empty-address-note {
            background: #fff8ea;
            border: 1px dashed #f0d998;
            color: #8a6d1f;
            border-radius: 12px;
            padding: 14px 16px;
            font-size: 0.85rem;
        }

        .empty-address-note a {
            font-weight: 600;
        }

        /* ---- Form fields ---- */
        .b2b-card label {
            font-size: 0.78rem;
            font-weight: 600;
            color: #545a68;
            margin-bottom: 6px;
        }

        .b2b-card .form-control,
        .b2b-card .form-select {
            font-size: 0.85rem;
            border-radius: 10px;
            border: 1px solid #e2e6ec;
            padding: 9px 12px;
            transition: all 0.15s ease;
        }

        .b2b-card .form-control:focus,
        .b2b-card .form-select:focus {
            border-color: #1f8a4c;
            box-shadow: 0 0 0 3px rgba(31, 138, 76, 0.12);
        }

        .b2b-card textarea.form-control {
            resize: vertical;
        }

        /* ---- Submit button ---- */
        .btn-submit-inquiry {
            background: #1f2530;
            border: none;
            color: #fff;
            font-weight: 600;
            font-size: 0.9rem;
            padding: 12px 32px;
            border-radius: 50px;
            transition: all 0.2s ease;
            box-shadow: 0 4px 14px rgba(31, 37, 48, 0.2);
        }

        .btn-submit-inquiry:hover {
            background: #1f8a4c;
            color: #fff;
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(31, 138, 76, 0.28);
        }

        /* ---- Order summary ---- */
        .summary-sticky {
            position: sticky;
            top: 20px;
        }

        .cart-item {
            display: flex;
            gap: 12px;
            padding: 14px 0;
            border-bottom: 1px solid #f0f2f6;
        }

        .cart-item:last-of-type {
            border-bottom: none;
        }

        .cart-item img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 10px;
            border: 1px solid #eceff3;
            flex-shrink: 0;
        }

        .cart-item-name {
            font-size: 0.85rem;
            font-weight: 700;
            color: #1f2530 !important;
            line-height: 1.3;
        }

        .cart-item-variant {
            font-size: 0.75rem;
            color: #8a92a3;
        }

        .cart-item-meta {
            font-size: 0.77rem;
            color: #545a68;
            margin-top: 4px;
            line-height: 1.6;
        }

        .cart-item-meta b {
            color: #1f2530;
            font-weight: 600;
        }

        .cart-item-price {
            font-size: 0.79rem;
            color: #1f8a4c;
            font-weight: 700;
        }

        .summary-totals {
            padding-top: 14px;
            font-size: 0.85rem;
        }

        .summary-totals .row-line {
            display: flex;
            justify-content: space-between;
            padding: 6px 0;
            color: #545a68;
        }

        .summary-totals .grand-total {
            display: flex;
            justify-content: space-between;
            padding-top: 12px;
            margin-top: 6px;
            border-top: 1px dashed #e2e6ec;
            font-size: 1rem;
            font-weight: 700;
            color: #1f2530;
        }

        .grand-total span:last-child {
            color: #1f8a4c;
        }

        /* ---- Responsive tweaks ---- */
        @media (max-width: 991.98px) {
            .summary-sticky {
                position: static;
                margin-top: 24px;
            }
        }

        @media (max-width: 575.98px) {
            .b2b-title {
                font-size: 1.25rem;
            }

            .b2b-card .card-body {
                padding: 15px;
            }

            .b2b-card .card-header {
                flex-wrap: wrap;
            }

            .btn-submit-inquiry {
                width: 100%;
                text-align: center;
            }

            .cart-item img {
                width: 50px;
                height: 50px;
            }
        }
    </style>

</head>

<body>

    <?php include "header.php"; ?>

    <div class="container py-5">

        <h2 class="b2b-title">B2B Inquiry Checkout</h2>
        <p class="b2b-subtitle">Select a saved address or enter new company details to submit your inquiry</p>

        <form action="b2b-place-order.php" method="post">

            <div class="row g-4">

                <div class="col-lg-8">

                    <!-- ===== Saved Addresses ===== -->
                    <div class="b2b-card mb-4">

                        <div class="card-header">
                            <span class="header-title">
                                <i class="bi bi-geo-alt-fill"></i> Saved Addresses
                            </span>

                            <a href="add-address.php?return=b2b-checkout" class="btn-add-address">
                                <i class="bi bi-plus-lg"></i> Add New
                            </a>
                        </div>

                        <div class="card-body">

                            <?php if (!empty($addresses)) { ?>

                                <?php foreach ($addresses as $a) { ?>

                                    <label class="saved-address-label">

                                        <input
                                            type="radio"
                                            name="saved_address"
                                            value="<?= $a['id']; ?>"
                                            class="savedAddress"

                                            data-id="<?= $a['id']; ?>"

                                            data-company="<?= htmlspecialchars($a['company_name']); ?>"
                                            data-gst="<?= htmlspecialchars($a['gst_number']); ?>"
                                            data-business="<?= htmlspecialchars($a['business_type']); ?>"
                                            data-delivery="<?= htmlspecialchars($a['delivery_date']); ?>"
                                            data-remarks="<?= htmlspecialchars($a['remarks']); ?>"

                                            data-name="<?= htmlspecialchars($a['full_name']); ?>"
                                            data-phone="<?= htmlspecialchars($a['phone']); ?>"
                                            data-address="<?= htmlspecialchars($a['address']); ?>"
                                            data-city="<?= htmlspecialchars($a['city']); ?>"
                                            data-state="<?= htmlspecialchars($a['state']); ?>"
                                            data-region="<?= htmlspecialchars($a['region']); ?>"
                                            data-pincode="<?= htmlspecialchars($a['pincode']); ?>"

                                            <?= !empty($a['is_default']) ? 'checked' : ''; ?>>

                                        <div class="saved-address-box">

                                            <h6>
                                                <?= htmlspecialchars($a['full_name']); ?>

                                                <?php if (!empty($a['is_default'])) { ?>
                                                    <span class="badge-pill ms-1">Default</span>
                                                <?php } ?>
                                            </h6>

                                            <div class="saved-address-meta">

                                                <?php if (!empty($a['company_name'])) { ?>
                                                    <b>Company:</b> <?= htmlspecialchars($a['company_name']); ?><br>
                                                <?php } ?>

                                                <?php if (!empty($a['gst_number'])) { ?>
                                                    <b>GST:</b> <?= htmlspecialchars($a['gst_number']); ?><br>
                                                <?php } ?>

                                                <?php if (!empty($a['business_type'])) { ?>
                                                    <b>Business Type:</b> <?= htmlspecialchars($a['business_type']); ?><br>
                                                <?php } ?>

                                                <b>Phone:</b> <?= htmlspecialchars($a['phone']); ?><br>

                                                <?= nl2br(htmlspecialchars($a['address'])); ?><br>
                                                <?= htmlspecialchars($a['city']); ?>,
                                                <?= htmlspecialchars($a['state']); ?>,
                                                <?= htmlspecialchars($a['region']); ?>
                                                - <?= htmlspecialchars($a['pincode']); ?>

                                                <?php if (!empty($a['delivery_date'])) { ?>
                                                    <br><b>Delivery Date:</b> <?= date("d M Y", strtotime($a['delivery_date'])); ?>
                                                <?php } ?>

                                             <?php if (!empty($a['remarks'])) { ?>

<div class="remarks-box mt-2">

    <span class="remarks-title">
        <i class="bi bi-chat-left-text"></i>
        Remarks
    </span>

    <p class="remarks-text mb-0">
        <?= htmlspecialchars($a['remarks']); ?>
    </p>

</div>

<?php } ?>



<style>
    .remarks-box{
    background:#f8f9fa;
    border:1px solid #e9ecef;
    border-left:4px solid #288218ab;
    border-radius:8px;
    padding:12px 15px;
}

.remarks-title{
    display:block;
    font-weight:600;
    color:#c50000;
    margin-bottom:8px;
}

.remarks-text{
    white-space:pre-line;
    word-break:break-word;
    line-height:1.8;
    color:#555;
    font-size:14px;
    margin-top: -35px;
}
</style>

                                            </div>

                                            <div class="saved-address-actions">

                                                <a href="edit-address.php?id=<?= $a['id']; ?>" class="btn-chip btn-chip-edit">
                                                    <i class="bi bi-pencil-fill"></i> Edit
                                                </a>

                                                <a href="delete-address.php?id=<?= $a['id']; ?>"
                                                    class="btn-chip btn-chip-delete"
                                                    onclick="return confirm('Delete this address?');">
                                                    <i class="bi bi-trash-fill"></i> Delete
                                                </a>

                                            </div>

                                        </div>

                                    </label>

                                <?php } ?>

                            <?php } else { ?>

                                <div class="empty-address-note">
                                    <i class="bi bi-info-circle-fill me-1"></i>
                                    No B2B address found.
                                    <a href="add-address.php?return=b2b-checkout">Add your first address</a>.
                                </div>

                            <?php } ?>

                        </div>

                    </div>

                    <input type="hidden" name="company_name" id="company_name">
                    <input type="hidden" name="gst_number" id="gst_number">
                    <input type="hidden" name="contact_person" id="contact_person">
                    <input type="hidden" name="email" id="email" value="<?= htmlspecialchars($user['email']); ?>">
                    <input type="hidden" name="phone" id="phone">
                    <input type="hidden" name="country" id="country">
                    <input type="hidden" name="state" id="state">
                    <input type="hidden" name="city" id="city">
                    <input type="hidden" name="address" id="address">
                    <input type="hidden" name="business_type" id="business_type">
                    <input type="hidden" name="delivery_date" id="delivery_date">
                    <input type="hidden" name="remarks" id="remarks">

                    <button type="submit" class="btn-submit-inquiry">
                        <i class="bi bi-send-fill me-1"></i> Submit Inquiry
                    </button>

                </div>

                <div class="col-lg-4">

                    <div class="b2b-card summary-sticky">

                        <div class="card-header">
                            <span class="header-title">
                                <i class="bi bi-bag-check-fill"></i> Order Summary
                            </span>
                        </div>

                        <div class="card-body">

                            <?php while ($row = mysqli_fetch_assoc($sql)) { ?>

                                <?php

                                // $price = $row['price'];

                                // if ($row['variant_id'] > 0 && $row['variant_price'] > 0) {
                                //     $price = $row['variant_price'];
                                // }

                                // $image = "../admin/uploads/" . $row['image'];
                                
                                $price = (float)$row['price'];

if ($row['variant_id'] > 0 && $row['variant_price'] > 0) {
    $price = (float)$row['variant_price'];
}

/*
|--------------------------------------------------------------------------
| 20% Markup
|--------------------------------------------------------------------------
| Database ki original price same rahegi.
| Checkout par product/variant price me 20% add hoga.
|
| Example:
| DB price ₹100  → Checkout ₹120
| DB price ₹200  → Checkout ₹240
|--------------------------------------------------------------------------
*/
$price = $price * 1.20;

$image = "../admin/uploads/" . $row['image'];

                                if (!empty($row['images'])) {
                                    $img = explode(",", $row['images']);
                                    if (!empty($img[0])) {
                                        $image = "../admin/img/" . trim($img[0]);
                                    }
                                }

                                $totalQty += $row['qty'];
                                $lineTotal = $price * $row['qty'];
                                $grandTotal += $lineTotal;
                                ?>

                                <input type="hidden" name="product_id[]" value="<?= $row['product_id']; ?>">
                                <input type="hidden" name="variant_id[]" value="<?= $row['variant_id']; ?>">
                                <input type="hidden" name="qty[]" value="<?= $row['qty']; ?>">
                                <input type="hidden" name="size[]" value="<?= htmlspecialchars($row['size']); ?>">
                                <input type="hidden" name="color[]" value="<?= htmlspecialchars($row['color']); ?>">

                                <div class="cart-item">

                                    <a href="b2b-product/<?= urlencode($row['name']) ?>">
                                        <img src="<?= $image; ?>" alt="<?= htmlspecialchars($row['name']); ?>">
                                    </a>

                                    <div class="flex-grow-1">

                                        <a href="b2b-product/<?= urlencode($row['name']) ?>"
                                            class="text-decoration-none cart-item-name d-block">
                                            <?= htmlspecialchars($row['name']); ?>
                                        </a>

                                        <?php if (!empty($row['variant_name'])) { ?>
                                            <div class="cart-item-variant">
                                                <?= htmlspecialchars($row['variant_name']); ?>
                                            </div>
                                        <?php } ?>

                                        <div class="cart-item-meta">

    <b>Qty:</b> <?= $row['qty']; ?>
    &nbsp;•&nbsp;
    <span class="cart-item-price">
        ₹<?= number_format($price,2); ?>
    </span>
    /Each

    <br>

    <b>Total:</b>
    ₹<?= number_format($lineTotal,2); ?>

    <?php if(!empty($row['size'])){ ?>
        <br>
        <b>Size:</b>
        <?= htmlspecialchars($row['size']); ?>
    <?php } ?>

    <?php if(!empty($row['color'])){ ?>
        <br>
        <b>Color:</b>
        <?= htmlspecialchars($row['color']); ?>
    <?php } ?>

    <?php if(!empty($row['size_requirement'])){ ?>
        <br>
        <b>Size Requirement:</b>
        <?= nl2br(htmlspecialchars($row['size_requirement'])); ?>
    <?php } ?>

    <?php if(!empty($row['remarks'])){ ?>
        <br>
        <b>Remarks:</b>
        <?= nl2br(htmlspecialchars($row['remarks'])); ?>
    <?php } ?>

</div>

                                    </div>

                                </div>

                            <?php } ?>

                            <input type="hidden" name="required_qty" value="<?= $totalQty; ?>">

                            <div class="summary-totals">

                                <div class="row-line">
                                    <span>Total Quantity</span>
                                    <span><?= $totalQty; ?></span>
                                </div>

                                <div class="grand-total">
                                    <span>Grand Total</span>
                                    <span>₹<?= number_format($grandTotal, 2); ?></span>
                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </form>

    </div>
    
    
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.querySelector("form").addEventListener("submit", function(e) {

    const selectedAddress = document.querySelector('input[name="saved_address"]:checked');

    if (!selectedAddress) {
        e.preventDefault();

        Swal.fire({
            icon: "warning",
            title: "Address Required",
            text: "Please select a delivery address before submitting your inquiry.",
            confirmButtonColor: "#1f8a4c"
        });

        return false;
    }

});
</script>

    <script>
        document.querySelectorAll(".savedAddress").forEach(function(radio) {
            radio.addEventListener("change", fillAddress);
        });

        function fillAddress() {

            document.getElementById("company_name").value = this.dataset.company || "";
            document.getElementById("gst_number").value = this.dataset.gst || "";
            document.getElementById("business_type").value = this.dataset.business || "";
            document.getElementById("contact_person").value = this.dataset.name || "";
            document.getElementById("email").value = "<?= htmlspecialchars($user['email']); ?>";
            document.getElementById("phone").value = this.dataset.phone || "";
            document.getElementById("country").value = "<?= htmlspecialchars($user['country']); ?>";
            document.getElementById("state").value = this.dataset.state || "";
            document.getElementById("region").value = this.dataset.region || "";
            document.getElementById("city").value = this.dataset.city || "";
            document.getElementById("pincode").value = this.dataset.pincode || "";
            document.getElementById("delivery_date").value = this.dataset.delivery || "";
            document.getElementById("address").value = this.dataset.address || "";
            document.getElementById("remarks").value = this.dataset.remarks || "";

        }

        const checked = document.querySelector(".savedAddress:checked");

        if (checked) {
            checked.dispatchEvent(new Event("change"));
        }
    </script>

    <?php include "footer.php"; ?>
</body>

</html>