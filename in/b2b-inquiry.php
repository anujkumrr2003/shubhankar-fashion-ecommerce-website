<?php
session_start();
include "config.php";

// $product_id = isset($_GET['product_id']) ? (int)$_GET['product_id'] : 0;

$product_id = isset($_GET['product_id']) ? (int)$_GET['product_id'] : 0;

$variant_id = isset($_GET['variant_id']) ? (int)$_GET['variant_id'] : 0;

$product = mysqli_fetch_assoc(mysqli_query($conn, "
SELECT *
FROM products
WHERE id='$product_id'
LIMIT 1
"));

if (!$product) {
    die("Product not found.");
}

if (isset($_POST['submit'])) {

    $user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;

    $product_id = (int)$_POST['product_id'];

    $variant_id = (int)$_POST['variant_id'];

    $company_name = mysqli_real_escape_string($conn, $_POST['company_name']);

    $gst_number = mysqli_real_escape_string($conn, $_POST['gst_number']);

    $contact_person = mysqli_real_escape_string($conn, $_POST['contact_person']);

    $email = mysqli_real_escape_string($conn, $_POST['email']);

    $phone = mysqli_real_escape_string($conn, $_POST['phone']);

    $country = mysqli_real_escape_string($conn, $_POST['country']);

    $state = mysqli_real_escape_string($conn, $_POST['state']);

    $city = mysqli_real_escape_string($conn, $_POST['city']);

    $address = mysqli_real_escape_string($conn, $_POST['address']);

    $business_type = mysqli_real_escape_string($conn, $_POST['business_type']);

    $required_qty = (int)$_POST['required_qty'];

    $delivery_date = mysqli_real_escape_string($conn, $_POST['delivery_date']);

    $remarks = mysqli_real_escape_string($conn, $_POST['remarks']);

    $status = "Pending";

    mysqli_query($conn, "
    INSERT INTO b2b_inquiries
    (
        user_id,
        product_id,
        variant_id,
        company_name,
        gst_number,
        contact_person,
        email,
        phone,
        country,
        state,
        city,
        address,
        business_type,
        required_qty,
        delivery_date,
        remarks,
        status
    )
    VALUES
    (
        '$user_id',
        '$product_id',
        '$variant_id',
        '$company_name',
        '$gst_number',
        '$contact_person',
        '$email',
        '$phone',
        '$country',
        '$state',
        '$city',
        '$address',
        '$business_type',
        '$required_qty',
        '$delivery_date',
        '$remarks',
        '$status'
    )
    ");

    echo "<script>
    alert('Inquiry Submitted Successfully');
    window.location='b2b.php';
    </script>";

    exit;
}



$variant = null;

if ($variant_id > 0) {

    $q = mysqli_query($conn, "
    SELECT *
    FROM product_variants
    WHERE id='$variant_id'
    LIMIT 1
    ");

    if (mysqli_num_rows($q)) {
        $variant = mysqli_fetch_assoc($q);
    }
}
?>

<!DOCTYPE html>
<html>

<head>

    <meta charset="UTF-8">

    <title>B2B Inquiry</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<body>

    <div class="container py-5">

        <h2 class="mb-4">B2B Product Inquiry</h2>

        <div class="row">

            <div class="col-md-4">

                <img
                    src="admin/uploads/<?php echo $product['image']; ?>"
                    class="img-fluid border">

                <h4 class="mt-3">
                    <?php echo $product['name']; ?>
                </h4>

                <h5>
                    ₹<?php echo number_format($product['price'], 2); ?>
                </h5>

            </div>

            <div class="col-md-8">

                <form method="post">




                    <input type="hidden" name="variant_id" value="<?= $variant_id; ?>">

                    <input type="hidden" name="product_id" value="<?= $product['id']; ?>">

                    <input type="hidden" name="variant_id" id="variant_id"
                        value="<?= isset($_GET['variant_id']) ? (int)$_GET['variant_id'] : 0; ?>">

                    <?php if (isset($_SESSION['user_id'])) { ?>
                        <input type="hidden" name="user_id" value="<?= $_SESSION['user_id']; ?>">
                    <?php } ?>

                    <div class="row">

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Company Name *</label>
                            <input type="text"
                                name="company_name"
                                class="form-control"
                                required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">GST Number</label>
                            <input type="text"
                                name="gst_number"
                                class="form-control">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Contact Person *</label>
                            <input type="text"
                                name="contact_person"
                                class="form-control"
                                required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email *</label>
                            <input type="email"
                                name="email"
                                class="form-control"
                                required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Phone *</label>
                            <input type="text"
                                name="phone"
                                class="form-control"
                                required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Business Type *</label>

                            <select name="business_type"
                                class="form-select"
                                required>

                                <option value="">Select Business</option>

                                <option value="Distributor">Distributor</option>

                                <option value="Wholesaler">Wholesaler</option>

                                <option value="Retailer">Retailer</option>

                                <option value="Importer">Importer</option>

                                <option value="Exporter">Exporter</option>

                                <option value="Manufacturer">Manufacturer</option>

                                <option value="Other">Other</option>

                            </select>

                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Country *</label>
                            <input type="text"
                                name="country"
                                class="form-control"
                                required>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">State *</label>
                            <input type="text"
                                name="state"
                                class="form-control"
                                required>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">City *</label>
                            <input type="text"
                                name="city"
                                class="form-control"
                                required>
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="form-label">Address *</label>

                            <textarea
                                name="address"
                                class="form-control"
                                rows="3"
                                required></textarea>

                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Required Quantity *</label>

                            <input
                                type="number"
                                min="1"
                                name="required_qty"
                                class="form-control"
                                required>

                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Expected Delivery Date</label>

                            <input
                                type="date"
                                name="delivery_date"
                                class="form-control">

                        </div>

                        <div class="col-md-12 mb-3">

                            <label class="form-label">Remarks</label>

                            <textarea
                                name="remarks"
                                class="form-control"
                                rows="5"
                                placeholder="Write your requirements..."></textarea>

                        </div>

                        <div class="col-md-12">

                            <button
                                type="submit"
                                name="submit"
                                class="btn btn-dark btn-lg">

                                Submit Inquiry

                            </button>

                        </div>

                    </div>

                </form>

            </div>

        </div>

    </div>

</body>

</html>