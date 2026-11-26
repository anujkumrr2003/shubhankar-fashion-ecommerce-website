<?php
session_start();
include "config.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = (int)$_SESSION['user_id'];

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location:b2b-checkout.php");
    exit;
}

$address_id = (int)$_GET['id'];

/*-----------------------------------
GET ADDRESS
------------------------------------*/

$stmt = mysqli_prepare($conn, "
SELECT *
FROM b2b_user_addresses
WHERE id=? AND user_id=?
LIMIT 1
");

mysqli_stmt_bind_param($stmt, "ii", $address_id, $user_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {

    $_SESSION['error'] = "Address not found.";
    header("Location:b2b-checkout.php");
    exit;
}

$address = mysqli_fetch_assoc($result);


/*-----------------------------------
UPDATE ADDRESS
------------------------------------*/

if ($_SERVER['REQUEST_METHOD'] == "POST") {

    /*===========================
    SHIPPING
    ===========================*/

    $company_name   = trim($_POST['company_name']);
    $gst_number     = trim($_POST['gst_number']);
    $business_type  = trim($_POST['business_type']);
    $delivery_date  = trim($_POST['delivery_date']);
    $remarks        = trim($_POST['remarks']);

    $full_name      = trim($_POST['full_name']);
    $phone          = trim($_POST['phone']);
    $state          = trim($_POST['state']);
    $city           = trim($_POST['city']);
    $region         = trim($_POST['region']);
    $address_line   = trim($_POST['address']);
    $pincode        = trim($_POST['pincode']);
    $address_type   = trim($_POST['address_type']);

    $same_as_shipping = isset($_POST['same_as_shipping']) ? 1 : 0;
    $is_default       = isset($_POST['is_default']) ? 1 : 0;


    /*===========================
    BILLING
    ===========================*/

    if ($same_as_shipping) {

        $billing_company_name   = $company_name;
        $billing_gst_number     = $gst_number;
        $billing_business_type  = $business_type;
        $billing_delivery_date  = $delivery_date;
        $billing_remarks        = $remarks;

        $billing_full_name      = $full_name;
        $billing_phone          = $phone;
        $billing_state          = $state;
        $billing_city           = $city;
        $billing_region         = $region;
        $billing_address        = $address_line;
        $billing_pincode        = $pincode;
        $billing_address_type   = $address_type;
    } else {

        $billing_company_name   = trim($_POST['billing_company_name']);
        $billing_gst_number     = trim($_POST['billing_gst_number']);
        $billing_business_type  = trim($_POST['billing_business_type']);
        $billing_delivery_date  = trim($_POST['billing_delivery_date']);
        $billing_remarks        = trim($_POST['billing_remarks']);

        $billing_full_name      = trim($_POST['billing_full_name']);
        $billing_phone          = trim($_POST['billing_phone']);
        $billing_state          = trim($_POST['billing_state']);
        $billing_city           = trim($_POST['billing_city']);
        $billing_region         = trim($_POST['billing_region']);
        $billing_address        = trim($_POST['billing_address']);
        $billing_pincode        = trim($_POST['billing_pincode']);
        $billing_address_type   = trim($_POST['billing_address_type']);
    }


    /*===========================
    VALIDATION
    ===========================*/

    if (
        empty($company_name) ||
        empty($full_name) ||
        empty($phone) ||
        empty($state) ||
        empty($city) ||
        empty($region) ||
        empty($address_line) ||
        empty($pincode) ||
        empty($address_type)
    ) {

        $_SESSION['error'] = "Please fill all Shipping Address fields.";
    } elseif (
        !$same_as_shipping &&
        (
            empty($billing_company_name) ||
            empty($billing_full_name) ||
            empty($billing_phone) ||
            empty($billing_state) ||
            empty($billing_city) ||
            empty($billing_region) ||
            empty($billing_address) ||
            empty($billing_pincode) ||
            empty($billing_address_type)
        )
    ) {

        $_SESSION['error'] = "Please fill all Billing Address fields.";
    } else {

        if ($is_default) {

            mysqli_query($conn, "
            UPDATE b2b_user_addresses
            SET is_default='0'
            WHERE user_id='$user_id'
            ");
        }

        $update = mysqli_prepare($conn, "
        UPDATE b2b_user_addresses
        SET

        company_name=?,
        gst_number=?,
        business_type=?,
        delivery_date=?,
        remarks=?,

        full_name=?,
        phone=?,
        state=?,
        city=?,
        address=?,
        pincode=?,
        address_type=?,
        region=?,

        billing_company_name=?,
        billing_gst_number=?,
        billing_business_type=?,
        billing_delivery_date=?,
        billing_remarks=?,

        billing_full_name=?,
        billing_phone=?,
        billing_state=?,
        billing_city=?,
        billing_address=?,
        billing_pincode=?,
        billing_address_type=?,
        billing_region=?,

        same_as_shipping=?,
        is_default=?

        WHERE id=? AND user_id=?
        ");

        mysqli_stmt_bind_param(
            $update,
            "ssssssssssssssssssssssssssiiii",

            $company_name,
            $gst_number,
            $business_type,
            $delivery_date,
            $remarks,

            $full_name,
            $phone,
            $state,
            $city,
            $address_line,
            $pincode,
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
            $is_default,
            $address_id,
            $user_id
        );

        if (mysqli_stmt_execute($update)) {

            $_SESSION['success'] = "Address updated successfully.";

            mysqli_stmt_close($update);

            header("Location:b2b-checkout.php");
            exit;
        } else {

            $_SESSION['error'] = "Database Error : " . mysqli_error($conn);

            mysqli_stmt_close($update);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Edit Address</title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">

</head>

<body>

    <?php include 'header.php'; ?>

    <section class="py-5">

        <div class="container">

            <div class="row justify-content-center">

                <div class="col-xl-12 col-lg-12">
                    <div class="card shadow-sm">

                        <div class="card-header text-white" style=" background: var(--primary); color:#fff;">

                            <h4 class="mb-0" style="color:#fff;">
                                Edit Address
                            </h4>

                        </div>

                        <div class="card-body">

                            <?php
                            if (isset($_SESSION['error'])) {
                            ?>
                                <div class="alert alert-danger">
                                    <?= $_SESSION['error']; ?>
                                </div>
                            <?php
                                unset($_SESSION['error']);
                            }
                            ?>

                            <form method="POST">

                                <div class="row">

                                    <!-- ===========================
             SHIPPING ADDRESS
                                    ============================ -->

                                    <!-- ===========================
SHIPPING ADDRESS
=========================== -->

<div class="col-12 mb-3">
    <h5 class="border-bottom pb-2">
        Shipping Address
    </h5>
</div>

<!-- Company Name -->
<div class="col-lg-3 col-md-6 mb-3">
    <label class="form-label">
        Company Name <span class="text-danger">*</span>
    </label>
    <input
        type="text"
        name="company_name"
        class="form-control"
        value="<?= htmlspecialchars($address['company_name']) ?>"
        required>
</div>

<!-- GST Number -->
<div class="col-lg-3 col-md-6 mb-3">
    <label class="form-label">
        GST Number
    </label>
    <input
        type="text"
        name="gst_number"
        class="form-control"
        value="<?= htmlspecialchars($address['gst_number']) ?>">
</div>

<!-- Business Type -->
<div class="col-lg-3 col-md-6 mb-3">
    <label class="form-label">
        Business Type <span class="text-danger">*</span>
    </label>

    <select
        name="business_type"
        class="form-select"
        required>

        <option value="">Select</option>

        <option value="Manufacturer" <?= ($address['business_type']=="Manufacturer")?'selected':''; ?>>
            Manufacturer
        </option>

        <option value="Wholesaler" <?= ($address['business_type']=="Wholesaler")?'selected':''; ?>>
            Wholesaler
        </option>

        <option value="Distributor" <?= ($address['business_type']=="Distributor")?'selected':''; ?>>
            Distributor
        </option>

        <option value="Retailer" <?= ($address['business_type']=="Retailer")?'selected':''; ?>>
            Retailer
        </option>

        <option value="Exporter" <?= ($address['business_type']=="Exporter")?'selected':''; ?>>
            Exporter
        </option>

        <option value="Importer" <?= ($address['business_type']=="Importer")?'selected':''; ?>>
            Importer
        </option>

        <option value="Other" <?= ($address['business_type']=="Other")?'selected':''; ?>>
            Other
        </option>

    </select>
</div>

<!-- Delivery Date -->
<div class="col-lg-3 col-md-6 mb-3">
    <label class="form-label">
        Delivery Date
    </label>
    <input
        type="date"
        name="delivery_date"
        class="form-control"
        value="<?= htmlspecialchars($address['delivery_date']) ?>">
</div>

<!-- Full Name -->
<div class="col-lg-3 col-md-6 mb-3">
    <label class="form-label">
        Customer Name <span class="text-danger">*</span>
    </label>

    <input
        type="text"
        name="full_name"
        class="form-control"
        value="<?= htmlspecialchars($address['full_name']) ?>"
        required>
</div>

<!-- Phone -->
<div class="col-lg-3 col-md-6 mb-3">
    <label class="form-label">
        Phone Number <span class="text-danger">*</span>
    </label>

    <input
        type="text"
        name="phone"
        class="form-control"
        value="<?= htmlspecialchars($address['phone']) ?>"
        required>
</div>

<!-- Address Type -->
<div class="col-lg-3 col-md-6 mb-3">
    <label class="form-label">
        Address Type <span class="text-danger">*</span>
    </label>

    <select
        name="address_type"
        class="form-select"
        required>

        <option value="Home" <?= ($address['address_type']=="Home")?'selected':''; ?>>
            Home
        </option>

        <option value="Office" <?= ($address['address_type']=="Office")?'selected':''; ?>>
            Office
        </option>

        <option value="Warehouse" <?= ($address['address_type']=="Warehouse")?'selected':''; ?>>
            Warehouse
        </option>

        <option value="Factory" <?= ($address['address_type']=="Factory")?'selected':''; ?>>
            Factory
        </option>

        <option value="Other" <?= ($address['address_type']=="Other")?'selected':''; ?>>
            Other
        </option>

    </select>
</div>

<!-- Address Line 1 -->
<div class="col-lg-3 col-md-6 mb-3">
    <label class="form-label">
        Address Line 1 <span class="text-danger">*</span>
    </label>

    <textarea
        name="address"
        rows="1"
        class="form-control"
        required><?= htmlspecialchars($address['address']) ?></textarea>
</div>

<!-- Address Line 2 -->
<div class="col-lg-3 col-md-6 mb-3">
    <label class="form-label">
        Address Line 2
    </label>

    <input
        type="text"
        name="city"
        class="form-control"
        value="<?= htmlspecialchars($address['city']) ?>">
</div>

<!-- City -->
<div class="col-lg-3 col-md-6 mb-3">
    <label class="form-label">
        City / Town <span class="text-danger">*</span>
    </label>

    <input
        type="text"
        name="state"
        class="form-control"
        value="<?= htmlspecialchars($address['state']) ?>"
        required>
</div>

<!-- Region -->
<div class="col-lg-3 col-md-6 mb-3">
    <label class="form-label">
        State / Province / Region <span class="text-danger">*</span>
    </label>

    <input
        type="text"
        name="region"
        class="form-control"
        value="<?= htmlspecialchars($address['region']) ?>"
        required>
</div>

<!-- Pincode -->
<div class="col-lg-3 col-md-6 mb-3">
    <label class="form-label">
        Postal / ZIP Code <span class="text-danger">*</span>
    </label>

    <input
        type="text"
        name="pincode"
        class="form-control"
        value="<?= htmlspecialchars($address['pincode']) ?>"
        required>
</div>

<!-- Remarks -->
<div class="col-lg-12 col-md-12 mb-3">
    <label class="form-label">
        Remarks
    </label>

    <textarea
        name="remarks"
        rows="1"
        class="form-control"><?= htmlspecialchars($address['remarks']) ?></textarea>
</div>

                                    <div class="col-12 ">

                                        <div class="form-check">

                                            <input
                                                class="form-check-input"
                                                type="checkbox"
                                                name="same_as_shipping"
                                                id="same_as_shipping"
                                                value="1"
                                                <?= ($address['same_as_shipping']) ? "checked" : ""; ?>>

                                            <label class="form-check-label" for="same_as_shipping">

                                                Billing Address is same as Shipping Address

                                            </label>

                                        </div>

                                    </div>

                                    <!-- ===========================
             BILLING ADDRESS
        ============================ -->

                                   <div id="billingSection">

    <div class="row">

        <div class="col-12 mb-3">
            <h5 class="border-bottom pb-2 mt-1" style="background: var(--primary); color:#fff; padding:5px;">
                Billing Address
            </h5>
        </div>

        <!-- Company Name -->
        <div class="col-lg-3 col-md-6 mb-3">
            <label class="form-label">
                Company Name
            </label>

            <input
                type="text"
                name="billing_company_name"
                class="form-control"
                value="<?= htmlspecialchars($address['billing_company_name']) ?>">
        </div>

        <!-- GST Number -->
        <div class="col-lg-3 col-md-6 mb-3">
            <label class="form-label">
                GST Number
            </label>

            <input
                type="text"
                name="billing_gst_number"
                class="form-control"
                value="<?= htmlspecialchars($address['billing_gst_number']) ?>">
        </div>

        <!-- Business Type -->
        <div class="col-lg-3 col-md-6 mb-3">
            <label class="form-label">
                Business Type
            </label>

            <select
                name="billing_business_type"
                class="form-select">

                <option value="">Select</option>

                <option value="Manufacturer" <?= ($address['billing_business_type']=="Manufacturer") ? "selected" : ""; ?>>
                    Manufacturer
                </option>

                <option value="Wholesaler" <?= ($address['billing_business_type']=="Wholesaler") ? "selected" : ""; ?>>
                    Wholesaler
                </option>

                <option value="Distributor" <?= ($address['billing_business_type']=="Distributor") ? "selected" : ""; ?>>
                    Distributor
                </option>

                <option value="Retailer" <?= ($address['billing_business_type']=="Retailer") ? "selected" : ""; ?>>
                    Retailer
                </option>

                <option value="Exporter" <?= ($address['billing_business_type']=="Exporter") ? "selected" : ""; ?>>
                    Exporter
                </option>

                <option value="Importer" <?= ($address['billing_business_type']=="Importer") ? "selected" : ""; ?>>
                    Importer
                </option>

                <option value="Other" <?= ($address['billing_business_type']=="Other") ? "selected" : ""; ?>>
                    Other
                </option>

            </select>
        </div>

        <!-- Delivery Date -->
        <div class="col-lg-3 col-md-6 mb-3">
            <label class="form-label">
                Delivery Date
            </label>

            <input
                type="date"
                name="billing_delivery_date"
                class="form-control"
                value="<?= htmlspecialchars($address['billing_delivery_date']) ?>">
        </div>

        <!-- Full Name -->
        <div class="col-lg-3 col-md-6 mb-3">
            <label class="form-label">
                Customer Name
            </label>

            <input
                type="text"
                name="billing_full_name"
                class="form-control"
                value="<?= htmlspecialchars($address['billing_full_name']) ?>">
        </div>

        <!-- Phone -->
        <div class="col-lg-3 col-md-6 mb-3">
            <label class="form-label">
                Phone Number
            </label>

            <input
                type="text"
                name="billing_phone"
                class="form-control"
                value="<?= htmlspecialchars($address['billing_phone']) ?>">
        </div>

        <!-- Address Type -->
        <div class="col-lg-3 col-md-6 mb-3">

            <label class="form-label">
                Address Type
            </label>

            <select
                name="billing_address_type"
                class="form-select">

                <option value="Home" <?= ($address['billing_address_type']=="Home") ? "selected" : ""; ?>>
                    Home
                </option>

                <option value="Office" <?= ($address['billing_address_type']=="Office") ? "selected" : ""; ?>>
                    Office
                </option>

                <option value="Warehouse" <?= ($address['billing_address_type']=="Warehouse") ? "selected" : ""; ?>>
                    Warehouse
                </option>

                <option value="Factory" <?= ($address['billing_address_type']=="Factory") ? "selected" : ""; ?>>
                    Factory
                </option>

                <option value="Other" <?= ($address['billing_address_type']=="Other") ? "selected" : ""; ?>>
                    Other
                </option>

            </select>

        </div>

        <!-- Address Line 1 -->
        <div class="col-lg-3 col-md-6 mb-3">

            <label class="form-label">
                Address Line 1
            </label>

            <textarea
                name="billing_address"
                rows="1"
                class="form-control"><?= htmlspecialchars($address['billing_address']) ?></textarea>

        </div>

        <!-- Address Line 2 -->
        <div class="col-lg-3 col-md-6 mb-3">

            <label class="form-label">
                Address Line 2
            </label>

            <input
                type="text"
                name="billing_city"
                class="form-control"
                value="<?= htmlspecialchars($address['billing_city']) ?>">

        </div>

        <!-- City -->
        <div class="col-lg-3 col-md-6 mb-3">

            <label class="form-label">
                City / Town
            </label>

            <input
                type="text"
                name="billing_state"
                class="form-control"
                value="<?= htmlspecialchars($address['billing_state']) ?>">

        </div>

        <!-- Region -->
        <div class="col-lg-3 col-md-6 mb-3">

            <label class="form-label">
                State / Province / Region
            </label>

            <input
                type="text"
                name="billing_region"
                class="form-control"
                value="<?= htmlspecialchars($address['billing_region']) ?>">

        </div>

        <!-- Postal Code -->
        <div class="col-lg-3 col-md-6 mb-3">

            <label class="form-label">
                Postal / ZIP Code
            </label>

            <input
                type="text"
                name="billing_pincode"
                class="form-control"
                value="<?= htmlspecialchars($address['billing_pincode']) ?>">

        </div>

        <!-- Remarks -->
        <div class="col-lg-12 col-md-12 mb-3">

            <label class="form-label">
                Remarks
            </label>

            <textarea
                name="billing_remarks"
                rows="1"
                class="form-control"><?= htmlspecialchars($address['billing_remarks']) ?></textarea>

        </div>

    </div>

</div>

                                    <!-- ===========================
             DEFAULT ADDRESS
        ============================ -->

                                    <div class="col-lg-3 col-md-6 mb-4 d-flex align-items-end">

                                        <div class="form-check">

                                            <input
                                                class="form-check-input"
                                                type="checkbox"
                                                name="is_default"
                                                value="1"
                                                id="is_default"
                                                <?= ($address['is_default']) ? "checked" : ""; ?>>

                                            <label class="form-check-label" for="is_default">

                                                Set as Default Address

                                            </label>

                                        </div>

                                    </div>

                                    <div class="col-12">

                                        <button
                                            type="submit"
                                            class="btn btn-success">

                                            Update Address

                                        </button>

                                        <a
                                            href="b2b-checkout.php"
                                            class="btn btn-secondary">

                                            Cancel

                                        </a>

                                    </div>

                                </div>

                            </form>

                            <script>
                                document.addEventListener("DOMContentLoaded", function() {

                                    const checkbox = document.getElementById("same_as_shipping");
                                    const billingSection = document.getElementById("billingSection");

                                    function toggleBilling() {
                                        billingSection.style.display = checkbox.checked ? "none" : "block";
                                    }

                                    // Default checked
                                    checkbox.checked = true;
                                    toggleBilling();

                                    checkbox.addEventListener("change", toggleBilling);

                                });
                            </script>

                        </div>

                    </div>

                </div>





            </div>

        </div>

    </section>

    <?php include 'footer.php'; ?>

    <!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
 <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script> -->
</body>

</html>