 
<?php
session_start();
include "config.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = (int)$_SESSION['user_id'];

// Return page
$return = $_GET['return'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    /* ===========================
       SHIPPING ADDRESS
    =========================== */

    $full_name = trim($_POST['full_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');

    $state = trim($_POST['state'] ?? '');
    $city = trim($_POST['city'] ?? '');

    $address = trim($_POST['address'] ?? '');
    $pincode = trim($_POST['pincode'] ?? '');

    $address_type = trim($_POST['address_type'] ?? '');
    $region = trim($_POST['region'] ?? '');

    $company_name = trim($_POST['company_name'] ?? '');
    $gst_number = trim($_POST['gst_number'] ?? '');

    $business_type = trim($_POST['business_type'] ?? '');
    $delivery_date = trim($_POST['delivery_date'] ?? '');

    $remarks = trim($_POST['remarks'] ?? '');


    /* ===========================
       BILLING ADDRESS
    =========================== */

    $same_as_shipping =
        isset($_POST['same_as_shipping']) ? 1 : 0;


    /*
     * CHECKBOX CHECKED
     * ----------------
     * Billing same as shipping.
     *
     * Billing columns will remain blank.
     */

    if ($same_as_shipping == 1) {

        $billing_full_name = '';
        $billing_phone = '';
        $billing_state = '';
        $billing_city = '';
        $billing_address = '';
        $billing_pincode = '';
        $billing_address_type = '';
        $billing_region = '';

        $billing_company_name = '';
        $billing_gst_number = '';
        $billing_business_type = '';
        $billing_delivery_date = '';
        $billing_remarks = '';

    }

    /*
     * CHECKBOX UNCHECKED
     * ------------------
     *
     * Billing address is OPTIONAL.
     *
     * User can fill all fields,
     * some fields, or leave them blank.
     */

    else {

        $billing_full_name =
            trim($_POST['billing_full_name'] ?? '');

        $billing_phone =
            trim($_POST['billing_phone'] ?? '');

        $billing_state =
            trim($_POST['billing_state'] ?? '');

        $billing_city =
            trim($_POST['billing_city'] ?? '');

        $billing_address =
            trim($_POST['billing_address'] ?? '');

        $billing_pincode =
            trim($_POST['billing_pincode'] ?? '');

        $billing_address_type =
            trim($_POST['billing_address_type'] ?? '');

        $billing_region =
            trim($_POST['billing_region'] ?? '');

        $billing_company_name =
            trim($_POST['billing_company_name'] ?? '');

        $billing_gst_number =
            trim($_POST['billing_gst_number'] ?? '');

        $billing_business_type =
            trim($_POST['billing_business_type'] ?? '');

        $billing_delivery_date =
            trim($_POST['billing_delivery_date'] ?? '');

        $billing_remarks =
            trim($_POST['billing_remarks'] ?? '');

    }


    /* ===========================
       DEFAULT ADDRESS
    =========================== */

    $is_default =
        isset($_POST['is_default']) ? 1 : 0;


    /* ===========================
       SHIPPING VALIDATION
    =========================== */

    if (
        $full_name === '' ||
        $phone === '' ||
        $state === '' ||
        $city === '' ||
        $address === '' ||
        $pincode === '' ||
        $address_type === '' ||
        $region === ''
    ) {

        $_SESSION['error'] =
            "Please fill all required Shipping Address fields.";

    }

    else {

        /* ===========================
           CHECK EXISTING ADDRESS
        =========================== */

        $check = mysqli_query(
            $conn,
            "
            SELECT id
            FROM b2b_user_addresses
            WHERE user_id='$user_id'
            "
        );


        if (!$check) {

            $_SESSION['error'] =
                "Database Error: " . mysqli_error($conn);

        }

        else {

            /*
             * First address automatically
             * becomes default.
             */

            if (mysqli_num_rows($check) == 0) {
                $is_default = 1;
            }


            /* ===========================
               REMOVE PREVIOUS DEFAULT
            =========================== */

            if ($is_default == 1) {

                mysqli_query(
                    $conn,
                    "
                    UPDATE b2b_user_addresses
                    SET is_default='0'
                    WHERE user_id='$user_id'
                    "
                );
            }


            /* ===========================
               INSERT ADDRESS
            =========================== */

            $stmt = mysqli_prepare(
                $conn,
                "
                INSERT INTO b2b_user_addresses
                (
                    user_id,

                    full_name,
                    phone,
                    state,
                    city,
                    address,
                    pincode,
                    address_type,
                    region,

                    company_name,
                    gst_number,
                    business_type,
                    delivery_date,
                    remarks,

                    billing_full_name,
                    billing_phone,
                    billing_state,
                    billing_city,
                    billing_address,
                    billing_pincode,
                    billing_address_type,
                    billing_region,

                    billing_company_name,
                    billing_gst_number,
                    billing_business_type,
                    billing_delivery_date,
                    billing_remarks,

                    same_as_shipping,
                    is_default,
                    created_at
                )

                VALUES
                (
                    ?,
                    ?,?,?,?,?,?,?,?,

                    ?,?,?,?,?,

                    ?,?,?,?,?,?,?,?,

                    ?,?,?,?,?,

                    ?,?,
                    NOW()
                )
                "
            );


            if (!$stmt) {

                $_SESSION['error'] =
                    "Database Prepare Error: " .
                    mysqli_error($conn);

            }

            else {

                mysqli_stmt_bind_param(
                    $stmt,
                    "issssssssssssssssssssssssssii",

                    $user_id,

                    $full_name,
                    $phone,
                    $state,
                    $city,
                    $address,
                    $pincode,
                    $address_type,
                    $region,

                    $company_name,
                    $gst_number,
                    $business_type,
                    $delivery_date,
                    $remarks,

                    $billing_full_name,
                    $billing_phone,
                    $billing_state,
                    $billing_city,
                    $billing_address,
                    $billing_pincode,
                    $billing_address_type,
                    $billing_region,

                    $billing_company_name,
                    $billing_gst_number,
                    $billing_business_type,
                    $billing_delivery_date,
                    $billing_remarks,

                    $same_as_shipping,
                    $is_default
                );


                /* ===========================
                   EXECUTE
                =========================== */

                if (mysqli_stmt_execute($stmt)) {

                    mysqli_stmt_close($stmt);

                    $_SESSION['success'] =
                        "Address added successfully.";


                    /* ===========================
                       RETURN
                    =========================== */

                    if ($return === "checkout") {

                        header(
                            "Location: b2b-checkout.php"
                        );

                    }

                    else {

                        header(
                            "Location: my-address.php"
                        );

                    }

                    exit;

                }

                else {

                    $_SESSION['error'] =
                        "Database Error: " .
                        mysqli_stmt_error($stmt);

                    mysqli_stmt_close($stmt);
                }
            }
        }
    }
}
?>
 
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Add Address</title>

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

    <section class="section py-5">

        <div class="container">

            <div class="row justify-content-center">

                <div class="col-xl-12 col-lg-12">

                    <div class="card shadow-sm">

                        <div class="card-header" style=" background: var(--primary); color:#fff;">

                            <h4 class="mb-0" style="color:#fff;">
                                Add New Address
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

                            <form action="" method="POST" id="addressForm" novalidate >

                                <div class="row">

                                    <div class="col-lg-3 col-md-6 mb-1">

                                        <label class="form-label">
                                            Full Name <span class="text-danger">*</span>
                                        </label>

                                        <input
                                            type="text"
                                            name="full_name"
                                            class="form-control"
                                            required>

                                    </div>

                                    <div class="col-lg-3 col-md-6 mb-1">

                                        <label class="form-label">
                                            Phone Number <span class="text-danger">*</span>
                                        </label>

                                        <input
                                            type="text"
                                            name="phone"
                                            class="form-control"
                                            required>

                                    </div>

                                    <div class="col-lg-3 col-md-6 mb-1">
                                        <label class="form-label">Address Type <span class="text-danger">*</span></label>
                                        <select name="address_type" class="form-select" required>
                                            <option value="">Select Address Type</option>
                                            <option value="Home">Home</option>
                                            <option value="Office">Office</option>
                                            <option value="Other">Other</option>
                                        </select>
                                    </div>

                                    <div class="col-3 mb-3">

                                        <label class="form-label">
                                            Address Line 1 <span class="text-danger">*</span>
                                        </label>

                                        <textarea
                                            name="address"
                                            rows="1"
                                            class="form-control"
                                            required></textarea>

                                    </div>

                                    <div class="col-lg-3 col-md-6 mb-1">

                                        <label class="form-label">
                                            Address Line 2 <span class="text-danger">*</span>
                                        </label>

                                        <input
                                            type="text"
                                            name="city"
                                            class="form-control"
                                            >

                                    </div>

                                    <div class="col-lg-3 col-md-6 mb-1">

                                        <label class="form-label">
                                            City / Town <span class="text-danger">*</span>
                                        </label>

                                        <input
                                            type="text"
                                            name="state"
                                            class="form-control"
                                            required>

                                    </div>


                                    <div class="col-lg-3 col-md-6 mb-1">

                                        <label class="form-label">
                                            State / Province / Region <span class="text-danger">*</span>
                                        </label>

                                        <input
                                            type="text"
                                            name="region"
                                            class="form-control"
                                            required>

                                    </div>

                                    <div class="col-lg-3 col-md-6 mb-1">

                                        <label class="form-label">
                                            Postal / ZIP Code <span class="text-danger">*</span>
                                        </label>

                                        <input
                                            type="text"
                                            name="pincode"
                                            maxlength="6"
                                            inputmode="numeric"
                                            class="form-control"
                                            required>

                                    </div>
                                    <div class="col-lg-3 col-md-6 mb-3">
                                        <label>Company Name <span class="text-danger">*</span></label>
                                        <input type="text" name="company_name" class="form-control" required>
                                    </div>
                                    <div class="col-lg-3 col-md-6 mb-3">
                                        <label>GST Number (Optional)</label>
                                        <input type="text" name="gst_number" class="form-control">
                                    </div>
                                    <div class="col-lg-3 col-md-6 mb-3">
                                        <label>Business Type <span class="text-danger">*</span></label>

                                        <select name="business_type" class="form-select" required>

                                            <option value="">Select</option>

                                            <option>Manufacturer</option>
                                            <option>Wholesaler</option>
                                            <option>Distributor</option>
                                            <option>Retailer</option>
                                            <option>Exporter</option>
                                            <option>Importer</option>
                                            <option>Other</option>

                                        </select>
                                    </div>
                                    <div class="col-lg-3 col-md-6 mb-3">
                                        <label>Delivery Date</label>
                                        <input type="date" name="delivery_date" class="form-control">
                                    </div>
                                    <div class="col-12 mb-3">
                                        <label>Remarks (Optional)</label>

                                        <textarea
                                            name="remarks"
                                            rows="1"
                                            class="form-control"></textarea>
                                    </div>

                                    <div class="col-12 mb-4">

                                        <div class="form-check">
                                            <div class="col-12 mb-4">
                                                <style>
                                                    .form-check {
                                                        padding-left: 13.5px !important;
                                                    }
                                                </style>
                                                <div class="form-check mb-2">

                                                    <input
                                                        class="form-check-input"
                                                        type="checkbox"
                                                        name="is_default"
                                                        id="defaultAddress"
                                                        checked>

                                                    <label class="form-check-label" for="defaultAddress">
                                                        Set as Default Address
                                                    </label>

                                                </div>

                                                <div class="form-check">

                                                    <input
                                                        class="form-check-input"
                                                        type="checkbox"
                                                        id="sameBilling"
                                                        name="same_as_shipping"
                                                        value="1"
                                                        checked>

                                                    <label class="form-check-label" for="sameBilling">
                                                        Billing Address is same as Shipping Address
                                                    </label>

                                                </div>

                                            </div>


                                            <div id="billingSection" style="display:none;">

                                                <hr class="my-4">

                                                <h4 class="mb-3" style=" background: var(--primary); color:#fff;padding:5px;">
                                                    Billing Address
                                                </h4>

                                                <div class="row">

                                                    <div class="col-lg-3 col-md-6 mb-1">

                                                        <label class="form-label">
                                                            Full Name
                                                        </label>

                                                        <input
                                                            type="text"
                                                            name="billing_full_name"
                                                            class="form-control">

                                                    </div>

                                                    <div class="col-lg-3 col-md-6 mb-1">

                                                        <label class="form-label">
                                                            Phone Number
                                                        </label>

                                                        <input
                                                            type="text"
                                                            name="billing_phone"
                                                            class="form-control">

                                                    </div>

                                                    <div class="col-lg-3 col-md-6 mb-1">

                                                        <label class="form-label">
                                                            Address Type
                                                        </label>

                                                        <select
                                                            name="billing_address_type"
                                                            class="form-select">

                                                            <option value="Home">Home</option>
                                                            <option value="Office">Office</option>
                                                            <option value="Other">Other</option>

                                                        </select>

                                                    </div>

                                                    <div class="col-lg-3 col-md-6 mb-1">

                                                        <label class="form-label">
                                                            Address Line 1
                                                        </label>

                                                        <textarea
                                                            rows="1"
                                                            class="form-control"
                                                            name="billing_address"></textarea>

                                                    </div>

                                                    <div class="col-lg-3 col-md-6 mb-1">

                                                        <label class="form-label">
                                                            Address Line 2
                                                        </label>

                                                        <input
                                                            type="text"
                                                            class="form-control"
                                                            name="billing_city">

                                                    </div>

                                                    <div class="col-lg-3 col-md-6 mb-1">

                                                        <label class="form-label">
                                                            City / Town
                                                        </label>

                                                        <input
                                                            type="text"
                                                            class="form-control"
                                                            name="billing_state">

                                                    </div>

                                                    <div class="col-lg-3 col-md-6 mb-1">

                                                        <label class="form-label">
                                                            State / Region
                                                        </label>

                                                        <input
                                                            type="text"
                                                            class="form-control"
                                                            name="billing_region">

                                                    </div>

                                                    <div class="col-lg-3 col-md-6 mb-1">

                                                        <label class="form-label">
                                                            Postal Code
                                                        </label>

                                                        <input
                                                            type="text"
                                                            class="form-control"
                                                            name="billing_pincode">

                                                    </div>













                                                    <div class="col-lg-3 col-md-6 mb-1">

                                                        <label class="form-label">
                                                            Billing Company Name
                                                        </label>

                                                        <input
                                                            type="text"
                                                            name="billing_company_name"
                                                            class="form-control">

                                                    </div>
                                                    <div class="col-lg-3 col-md-6 mb-1">

                                                        <label class="form-label">
                                                            Billing GST Number
                                                        </label>

                                                        <input
                                                            type="text"
                                                            name="billing_gst_number"
                                                            class="form-control">

                                                    </div>

                                                    <div class="col-lg-3 col-md-6 mb-1">

                                                        <label class="form-label">
                                                            Billing Business Type
                                                        </label>

                                                        <select
                                                            name="billing_business_type"
                                                            class="form-select">

                                                            <option>Manufacturer</option>
                                                            <option>Wholesaler</option>
                                                            <option>Distributor</option>
                                                            <option>Retailer</option>
                                                            <option>Exporter</option>
                                                            <option>Importer</option>
                                                            <option>Other</option>

                                                        </select>

                                                    </div>
                                                    <div class="col-lg-3 col-md-6 mb-1">

                                                        <label class="form-label">
                                                            Billing Delivery Date
                                                        </label>

                                                        <input
                                                            type="date"
                                                            name="billing_delivery_date"
                                                            class="form-control">

                                                    </div>
                                                    <div class="col-lg-3 col-md-6 mb-1">

                                                        <label class="form-label">
                                                            Billing Remarks
                                                        </label>

                                                        <textarea
                                                            name="billing_remarks"
                                                            rows="1"
                                                            class="form-control"></textarea>

                                                    </div>

                                                </div>

                                            </div>

                                        </div>

                                    </div>

                                    <div class="col-12 d-flex gap-2">

                                        <button
                                            type="submit"
                                            class="btn btn-success">

                                            Save Address

                                        </button>

                                        <a
                                            href="b2b-checkout.php"
                                            class="btn btn-secondary">

                                            Cancel

                                        </a>

                                    </div>

                                </div>

                            </form>
                            
                            
                            <style>
    .form-control.is-invalid,
    .form-select.is-invalid {
        border-color: #dc3545 !important;
    }

    .validation-error {
        color: #dc3545;
        font-size: 13px;
        margin-top: 4px;
        display: none;
    }

    .form-control.is-invalid + .validation-error,
    .form-select.is-invalid + .validation-error {
        display: block;
    }

    .form-control.is-valid,
    .form-select.is-valid {
        border-color: #198754 !important;
    }
</style>



                        </div>

                    </div>

                </div>

            </div>

        </div>

    </section>






<script>
document.addEventListener("DOMContentLoaded", function () {

    const form = document.getElementById("addressForm");

    if (!form) return;


    /* --------------------------------
       BILLING SHOW / HIDE
    -------------------------------- */

    const sameBilling = document.getElementById("sameBilling");
    const billingSection = document.getElementById("billingSection");

    function toggleBilling() {

        if (sameBilling.checked) {

            billingSection.style.display = "none";

        } else {

            billingSection.style.display = "block";

        }

    }

    sameBilling.addEventListener("change", toggleBilling);

    toggleBilling();


    /* --------------------------------
       VALIDATION FUNCTIONS
    -------------------------------- */

    function showError(input, message) {

        input.classList.add("is-invalid");
        input.classList.remove("is-valid");

        let error = input.parentElement.querySelector(".validation-error");

        if (!error) {

            error = document.createElement("div");
            error.className = "validation-error";

            input.parentElement.appendChild(error);

        }

        error.innerText = message;
        error.style.display = "block";
    }


    function showSuccess(input) {

        input.classList.remove("is-invalid");
        input.classList.add("is-valid");

        let error = input.parentElement.querySelector(".validation-error");

        if (error) {
            error.style.display = "none";
        }

    }


    function clearValidation(input) {

        input.classList.remove("is-invalid");
        input.classList.remove("is-valid");

        let error = input.parentElement.querySelector(".validation-error");

        if (error) {
            error.style.display = "none";
        }

    }


    /* --------------------------------
       FULL NAME
    -------------------------------- */

    function validateName(input) {

        let value = input.value.trim();

        if (value === "") {

            showError(input, "Please enter full name.");
            return false;

        }

        if (value.length < 3) {

            showError(input, "Name must be at least 3 characters.");
            return false;

        }

        if (!/^[a-zA-Z\s]+$/.test(value)) {

            showError(input, "Name can contain only letters.");
            return false;

        }

        showSuccess(input);
        return true;

    }


    /* --------------------------------
       PHONE
    -------------------------------- */

    function validatePhone(input) {

        let value = input.value.trim();

        if (value === "") {

            showError(input, "Please enter phone number.");
            return false;

        }

        if (!/^[6-9][0-9]{9}$/.test(value)) {

            showError(input, "Please enter a valid 10-digit phone number.");
            return false;

        }

        showSuccess(input);
        return true;

    }


    /* --------------------------------
       REQUIRED TEXT
    -------------------------------- */

    function validateRequired(input, message) {

        if (input.value.trim() === "") {

            showError(input, message);
            return false;

        }

        showSuccess(input);
        return true;

    }


    /* --------------------------------
       POSTAL / PINCODE
    -------------------------------- */

    function validatePincode(input) {

    let value = input.value.trim();

    if (value === "") {

        showError(input, "Please enter postal / ZIP code.");
        return false;

    }

    if (!/^[0-9]{6}$/.test(value)) {

        showError(input, "Please enter a valid 6-digit postal / ZIP code.");
        return false;

    }

    showSuccess(input);
    return true;

}


    /* --------------------------------
       GST
       Optional
    -------------------------------- */

    function validateGST(input) {

        let value = input.value.trim().toUpperCase();

        if (value === "") {

            clearValidation(input);
            return true;

        }

        let gstPattern =
            /^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z][1-9A-Z]Z[0-9A-Z]$/;

        if (!gstPattern.test(value)) {

            showError(input, "Please enter a valid GST number.");
            return false;

        }

        input.value = value;

        showSuccess(input);
        return true;

    }


    /* --------------------------------
       COMPANY NAME
    -------------------------------- */

    function validateCompany(input) {

        let value = input.value.trim();

        if (value === "") {

            showError(input, "Please enter company name.");
            return false;

        }

        if (value.length < 2) {

            showError(input, "Please enter a valid company name.");
            return false;

        }

        showSuccess(input);
        return true;

    }


    /* --------------------------------
       MAIN FORM VALIDATION
    -------------------------------- */

    form.addEventListener("submit", function (e) {

        let valid = true;

        /* Full Name */

        if (!validateName(
            form.querySelector('[name="full_name"]')
        )) {
            valid = false;
        }


        /* Phone */

        if (!validatePhone(
            form.querySelector('[name="phone"]')
        )) {
            valid = false;
        }


        /* Address Type */

        if (!validateRequired(
            form.querySelector('[name="address_type"]'),
            "Please select address type."
        )) {
            valid = false;
        }


        /* Address */

        if (!validateRequired(
            form.querySelector('[name="address"]'),
            "Please enter address."
        )) {
            valid = false;
        }


        /* City */

        if (!validateRequired(
            form.querySelector('[name="state"]'),
            "Please enter city / town."
        )) {
            valid = false;
        }


        /* Region */

        if (!validateRequired(
            form.querySelector('[name="region"]'),
            "Please enter state / region."
        )) {
            valid = false;
        }


        /* Pincode */

        if (!validatePincode(
            form.querySelector('[name="pincode"]')
        )) {
            valid = false;
        }


        /* Company */

        if (!validateCompany(
            form.querySelector('[name="company_name"]')
        )) {
            valid = false;
        }


        /* Business Type */

        if (!validateRequired(
            form.querySelector('[name="business_type"]'),
            "Please select business type."
        )) {
            valid = false;
        }


        /* GST */

        if (!validateGST(
            form.querySelector('[name="gst_number"]')
        )) {
            valid = false;
        }


        /* --------------------------------
           BILLING VALIDATION
        -------------------------------- */

        if (!sameBilling.checked) {

            let billingName =
                form.querySelector('[name="billing_full_name"]');

            let billingPhone =
                form.querySelector('[name="billing_phone"]');

            let billingAddress =
                form.querySelector('[name="billing_address"]');

            let billingCity =
                form.querySelector('[name="billing_state"]');

            let billingRegion =
                form.querySelector('[name="billing_region"]');

            let billingPincode =
                form.querySelector('[name="billing_pincode"]');


            if (!validateName(billingName)) {
                valid = false;
            }

            if (!validatePhone(billingPhone)) {
                valid = false;
            }

            if (!validateRequired(
                billingAddress,
                "Please enter billing address."
            )) {
                valid = false;
            }

            if (!validateRequired(
                billingCity,
                "Please enter billing city / town."
            )) {
                valid = false;
            }

            if (!validateRequired(
                billingRegion,
                "Please enter billing state / region."
            )) {
                valid = false;
            }

            if (!validatePincode(billingPincode)) {
                valid = false;
            }

        }


        /* --------------------------------
           STOP FORM IF INVALID
        -------------------------------- */

        if (!valid) {

            e.preventDefault();

            const firstError =
                form.querySelector(".is-invalid");

            if (firstError) {

                firstError.scrollIntoView({
                    behavior: "smooth",
                    block: "center"
                });

                firstError.focus();

            }

        }

    });


    /* --------------------------------
       LIVE VALIDATION
    -------------------------------- */

    form.querySelector('[name="full_name"]')
        .addEventListener("blur", function () {
            validateName(this);
        });


    form.querySelector('[name="phone"]')
        .addEventListener("blur", function () {
            validatePhone(this);
        });


    form.querySelector('[name="company_name"]')
        .addEventListener("blur", function () {
            validateCompany(this);
        });


    form.querySelector('[name="gst_number"]')
        .addEventListener("blur", function () {
            validateGST(this);
        });


    /* --------------------------------
       ONLY NUMBERS IN PHONE
    -------------------------------- */

    document.querySelectorAll(
        'input[name="phone"], input[name="billing_phone"], input[name="pincode"], input[name="billing_pincode"]'
    ).forEach(function (input) {

        input.addEventListener("input", function () {

            this.value = this.value.replace(/\D/g, "");

        });

    });

});
</script>


    <script>
        document.addEventListener("DOMContentLoaded", function() {

            const checkbox = document.getElementById("sameBilling");
            const billing = document.getElementById("billingSection");

            function toggleBilling() {
                billing.style.display = checkbox.checked ? "none" : "block";
            }

            toggleBilling();

            checkbox.addEventListener("change", toggleBilling);

        });
    </script>
    <?php include 'footer.php'; ?>

    <!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script> -->

</body>

</html>