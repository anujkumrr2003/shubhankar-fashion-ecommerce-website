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

if ($_SERVER['REQUEST_METHOD'] == "POST") {

    /* ===========================
       SHIPPING ADDRESS
    =========================== */

    $full_name    = trim($_POST['full_name']);
    $phone        = trim($_POST['phone']);
    $state        = trim($_POST['state']);
    $city         = trim($_POST['city']);
    $address      = trim($_POST['address']);
    $pincode      = trim($_POST['pincode']);
    $address_type = trim($_POST['address_type']);
    $region       = trim($_POST['region']);

    /* ===========================
       BILLING ADDRESS
    =========================== */

    $same_as_shipping = isset($_POST['same_as_shipping']) ? 1 : 0;

    if ($same_as_shipping) {

        $billing_full_name    = $full_name;
        $billing_phone        = $phone;
        $billing_state        = $state;
        $billing_city         = $city;
        $billing_address      = $address;
        $billing_pincode      = $pincode;
        $billing_address_type = $address_type;
        $billing_region       = $region;
    } else {

        $billing_full_name    = trim($_POST['billing_full_name']);
        $billing_phone        = trim($_POST['billing_phone']);
        $billing_state        = trim($_POST['billing_state']);
        $billing_city         = trim($_POST['billing_city']);
        $billing_address      = trim($_POST['billing_address']);
        $billing_pincode      = trim($_POST['billing_pincode']);
        $billing_address_type = trim($_POST['billing_address_type']);
        $billing_region       = trim($_POST['billing_region']);

        if (
            empty($billing_full_name) ||
            empty($billing_phone) ||
            empty($billing_state) ||
            empty($billing_city) ||
            empty($billing_address) ||
            empty($billing_pincode) ||
            empty($billing_address_type) ||
            empty($billing_region)
        ) {
            $_SESSION['error'] = "Please fill all Billing Address fields.";
            header("Location: add-address.php");
            exit;
        }
    }

    $is_default = isset($_POST['is_default']) ? 1 : 0;

    /* ===========================
       VALIDATE SHIPPING ADDRESS
    =========================== */

    if (
        empty($full_name) ||
        empty($phone) ||
        empty($state) ||
        empty($city) ||
        empty($address) ||
        empty($pincode) ||
        empty($address_type) ||
        empty($region)
    ) {

        $_SESSION['error'] = "Please fill all required Shipping Address fields.";
    } else {

        // First address becomes default
        $check = mysqli_query($conn, "
            SELECT id
            FROM user_addresses
            WHERE user_id='$user_id'
        ");

        if (mysqli_num_rows($check) == 0) {
            $is_default = 1;
        }

        // Remove previous default
        if ($is_default == 1) {

            mysqli_query($conn, "
                UPDATE user_addresses
                SET is_default='0'
                WHERE user_id='$user_id'
            ");
        }

        $stmt = mysqli_prepare($conn, "
            INSERT INTO user_addresses
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
                ?,?,?,?,?,?,?,?,
                ?,?,?,?,?,?,?,?,
                ?,?,
                NOW()
            )
        ");

        mysqli_stmt_bind_param(
            $stmt,
            "issssssssssssssssii",
            $user_id,

            $full_name,
            $phone,
            $state,
            $city,
            $address,
            $pincode,
            $address_type,
            $region,

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

        if (mysqli_stmt_execute($stmt)) {

            mysqli_stmt_close($stmt);

            $_SESSION['success'] = "Address added successfully.";

            if ($return == "checkout") {
                header("Location: checkout.php");
            } else {
                header("Location: my-address.php");
            }

            exit;
        } else {

            $_SESSION['error'] = "Database Error : " . mysqli_error($conn);
            mysqli_stmt_close($stmt);
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
    <link rel="stylesheet" href="css/style.css">


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

                            <form action="" method="POST">

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
        inputmode="numeric"
        maxlength="10"
        minlength="10"
        pattern="[0-9]{10}"
        oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10);"
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
                                            class="form-control"
                                            required>

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
                                            href="checkout.php"
                                            class="btn btn-secondary">

                                            Cancel

                                        </a>

                                    </div>

                                </div>

                            </form>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </section>









    <script>
        // document.addEventListener("DOMContentLoaded", function() {

        //     const checkbox = document.getElementById("sameBilling");
        //     const billing = document.getElementById("billingSection");

        //     function toggleBilling() {
        //         billing.style.display = checkbox.checked ? "none" : "block";
        //     }

        //     toggleBilling();

        //     checkbox.addEventListener("change", toggleBilling);

        // });
    </script>
    
    
    
  
<script>
document.addEventListener("DOMContentLoaded", function () {

    const form = document.querySelector("form");
    const checkbox = document.getElementById("sameBilling");
    const billing = document.getElementById("billingSection");

    /* =========================
       BILLING SECTION TOGGLE
    ========================= */

    function toggleBilling() {
        billing.style.display = checkbox.checked ? "none" : "block";
    }

    toggleBilling();

    checkbox.addEventListener("change", function () {
        toggleBilling();

        // Remove billing errors when billing is hidden
        if (checkbox.checked) {
            document.querySelectorAll("#billingSection .js-error")
                .forEach(function (error) {
                    error.remove();
                });

            document.querySelectorAll("#billingSection .is-invalid")
                .forEach(function (input) {
                    input.classList.remove("is-invalid");
                });
        }
    });


    /* =========================
       VALIDATION FUNCTIONS
    ========================= */

    function showError(input, message) {

        input.classList.add("is-invalid");

        // Remove old error
        const oldError = input.parentElement.querySelector(".js-error");
        if (oldError) {
            oldError.remove();
        }

        const error = document.createElement("div");
        error.className = "js-error text-danger small mt-1";
        error.innerText = message;

        input.parentElement.appendChild(error);
    }


    function clearError(input) {

        input.classList.remove("is-invalid");

        const error = input.parentElement.querySelector(".js-error");

        if (error) {
            error.remove();
        }
    }


    function validateRequired(input, message) {

        if (input.value.trim() === "") {
            showError(input, message);
            return false;
        }

        clearError(input);
        return true;
    }


    /* =========================
       FORM VALIDATION
    ========================= */

    form.addEventListener("submit", function (e) {

        let isValid = true;
        let firstError = null;


        /* =========================
           SHIPPING ADDRESS
        ========================= */

        const fullName = document.querySelector('[name="full_name"]');
        const phone = document.querySelector('[name="phone"]');
        const addressType = document.querySelector('[name="address_type"]');
        const address = document.querySelector('[name="address"]');
        const city = document.querySelector('[name="city"]');
        const state = document.querySelector('[name="state"]');
        const region = document.querySelector('[name="region"]');
        const pincode = document.querySelector('[name="pincode"]');


        if (!validateRequired(fullName, "Please enter your full name.")) {
            isValid = false;
            firstError = firstError || fullName;
        }


        /* Phone validation */

        const phoneValue = phone.value.trim();

        if (phoneValue === "") {

            showError(phone, "Please enter phone number.");
            isValid = false;
            firstError = firstError || phone;

        } else if (!/^[0-9]{10}$/.test(phoneValue)) {

            showError(phone, "Please enter a valid 10-digit phone number.");
            isValid = false;
            firstError = firstError || phone;

        } else {

            clearError(phone);
        }


        if (!validateRequired(
            addressType,
            "Please select address type."
        )) {
            isValid = false;
            firstError = firstError || addressType;
        }


        if (!validateRequired(
            address,
            "Please enter your address."
        )) {
            isValid = false;
            firstError = firstError || address;
        }


        if (!validateRequired(
            city,
            "Please enter city / town."
        )) {
            isValid = false;
            firstError = firstError || city;
        }


        if (!validateRequired(
            state,
            "Please enter state / province."
        )) {
            isValid = false;
            firstError = firstError || state;
        }


        if (!validateRequired(
            region,
            "Please enter state / region."
        )) {
            isValid = false;
            firstError = firstError || region;
        }


        /* Pincode validation */

        const pincodeValue = pincode.value.trim();

        if (pincodeValue === "") {

            showError(pincode, "Please enter postal / ZIP code.");
            isValid = false;
            firstError = firstError || pincode;

        } else if (!/^[0-9]{4,10}$/.test(pincodeValue)) {

            showError(
                pincode,
                "Please enter a valid postal / ZIP code."
            );

            isValid = false;
            firstError = firstError || pincode;

        } else {

            clearError(pincode);
        }


        /* =========================
           BILLING ADDRESS
        ========================= */

        if (!checkbox.checked) {

            const billingFullName =
                document.querySelector('[name="billing_full_name"]');

            const billingPhone =
                document.querySelector('[name="billing_phone"]');

            const billingAddressType =
                document.querySelector('[name="billing_address_type"]');

            const billingAddress =
                document.querySelector('[name="billing_address"]');

            const billingCity =
                document.querySelector('[name="billing_city"]');

            const billingState =
                document.querySelector('[name="billing_state"]');

            const billingRegion =
                document.querySelector('[name="billing_region"]');

            const billingPincode =
                document.querySelector('[name="billing_pincode"]');


            if (!validateRequired(
                billingFullName,
                "Please enter billing full name."
            )) {
                isValid = false;
                firstError = firstError || billingFullName;
            }


            /* Billing phone */

            const billingPhoneValue =
                billingPhone.value.trim();

            if (billingPhoneValue === "") {

                showError(
                    billingPhone,
                    "Please enter billing phone number."
                );

                isValid = false;
                firstError = firstError || billingPhone;

            } else if (!/^[0-9]{10}$/.test(billingPhoneValue)) {

                showError(
                    billingPhone,
                    "Please enter a valid 10-digit phone number."
                );

                isValid = false;
                firstError = firstError || billingPhone;

            } else {

                clearError(billingPhone);
            }


            if (!validateRequired(
                billingAddressType,
                "Please select billing address type."
            )) {
                isValid = false;
                firstError = firstError || billingAddressType;
            }


            if (!validateRequired(
                billingAddress,
                "Please enter billing address."
            )) {
                isValid = false;
                firstError = firstError || billingAddress;
            }


            if (!validateRequired(
                billingCity,
                "Please enter billing city / town."
            )) {
                isValid = false;
                firstError = firstError || billingCity;
            }


            if (!validateRequired(
                billingState,
                "Please enter billing state."
            )) {
                isValid = false;
                firstError = firstError || billingState;
            }


            if (!validateRequired(
                billingRegion,
                "Please enter billing region."
            )) {
                isValid = false;
                firstError = firstError || billingRegion;
            }


            /* Billing pincode */

            const billingPincodeValue =
                billingPincode.value.trim();

            if (billingPincodeValue === "") {

                showError(
                    billingPincode,
                    "Please enter billing postal / ZIP code."
                );

                isValid = false;
                firstError = firstError || billingPincode;

            } else if (!/^[0-9]{4,10}$/.test(billingPincodeValue)) {

                showError(
                    billingPincode,
                    "Please enter a valid billing postal / ZIP code."
                );

                isValid = false;
                firstError = firstError || billingPincode;

            } else {

                clearError(billingPincode);
            }
        }


        /* =========================
           STOP SUBMIT IF INVALID
        ========================= */

        if (!isValid) {

            e.preventDefault();

            if (firstError) {
                firstError.focus();

                firstError.scrollIntoView({
                    behavior: "smooth",
                    block: "center"
                });
            }

            return false;
        }

    });


    /* =========================
       REMOVE ERROR WHILE TYPING
    ========================= */

    form.querySelectorAll("input, textarea, select")
        .forEach(function (input) {

            input.addEventListener("input", function () {
                if (input.value.trim() !== "") {
                    clearError(input);
                }
            });

            input.addEventListener("change", function () {
                if (input.value.trim() !== "") {
                    clearError(input);
                }
            });

        });

});
</script>
 

    <?php include 'footer.php'; ?>

    <!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script> -->

</body>

</html>