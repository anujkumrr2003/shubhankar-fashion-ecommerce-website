<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

include('menu.php');
include('config.php');

/*
|--------------------------------------------------------------------------
| Get Orders
|--------------------------------------------------------------------------
|
| One order can have multiple products.
| GROUP_CONCAT is used to show all product details in View modal.
|
*/

$stmt = $conn->prepare("
    SELECT
        o.*,

        GROUP_CONCAT(
            DISTINCT p.name
            ORDER BY oi.id
            SEPARATOR '|||'
        ) AS product_name,

        GROUP_CONCAT(
            COALESCE(oi.size, '-')
            ORDER BY oi.id
            SEPARATOR '|||'
        ) AS size,

        GROUP_CONCAT(
            COALESCE(oi.color, '-')
            ORDER BY oi.id
            SEPARATOR '|||'
        ) AS color,

        GROUP_CONCAT(
            COALESCE(oi.qty, 0)
            ORDER BY oi.id
            SEPARATOR '|||'
        ) AS qty,

        GROUP_CONCAT(
            COALESCE(oi.price, 0)
            ORDER BY oi.id
            SEPARATOR '|||'
        ) AS item_price,

        GROUP_CONCAT(
            COALESCE(oi.size_requirement, '-')
            ORDER BY oi.id
            SEPARATOR '|||'
        ) AS size_requirement

    FROM orders o

    LEFT JOIN order_items oi
        ON oi.order_id = o.id

    LEFT JOIN products p
        ON p.id = oi.product_id

    GROUP BY o.id

    ORDER BY o.id DESC
");

$stmt->execute();

$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

?>

<style>

    body {
        background: #f4f5f7;
    }

    .orders-header {
        background: #000;
        color: #fff;
        padding: 12px;
        border-radius: 8px;
    }

    .table thead {
        background: #000;
        color: #fff;
    }

    .table thead th {
        color: #fff !important;
        white-space: nowrap;
    }

    .view-btn {
        min-width: 75px;
    }

    .order-product-table th {
        background: #f8f9fa;
        white-space: nowrap;
    }

    .product-name-cell {
        font-weight: 600;
    }

    .modal-label {
        font-size: 12px;
        color: #777;
        margin-bottom: 3px;
    }

    .modal-value {
        font-weight: 500;
        word-break: break-word;
    }

    .summary-box {
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 15px;
        text-align: center;
        height: 100%;
    }

    .summary-title {
        font-size: 13px;
        color: #777;
        margin-bottom: 5px;
    }

    .summary-value {
        font-size: 18px;
        font-weight: 700;
    }

    .final-box {
        background: #f8f9fa;
    }

</style>


<div class="row">

    <div class="col-xl-12">

        <div class="card">

            <div class="card-header">

                <h4 class="header-title">
                    Orders Management
                </h4>

            </div>


            <div class="card-body">


                <!-- =====================================================
                     ORDERS TABLE
                ====================================================== -->

                <?php if (!empty($orders)): ?>

                    <div class="table-responsive">

                        <table class="table table-bordered table-hover text-center bg-white align-middle">

                            <thead>

                                <tr>

                                    <th>
                                        Order ID
                                    </th>

                                    <th>
                                        Name
                                    </th>

                                    <th>
                                        Email
                                    </th>

                                    <th>
                                        Amount
                                    </th>

                                    <th>
                                        Payment
                                    </th>

                                    <th>
                                        Status
                                    </th>

                                    <th>
                                        Action
                                    </th>

                                    <th>
                                        View
                                    </th>

                                </tr>

                            </thead>


                            <tbody>

                            <?php foreach ($orders as $order): ?>

                                <?php

                                /*
                                |--------------------------------------------------------------------------
                                | Safely encode order data
                                |--------------------------------------------------------------------------
                                */

                                $orderJson = htmlspecialchars(
                                    json_encode(
                                        $order,
                                        JSON_HEX_TAG |
                                        JSON_HEX_APOS |
                                        JSON_HEX_AMP |
                                        JSON_HEX_QUOT
                                    ),
                                    ENT_QUOTES,
                                    'UTF-8'
                                );


                                /*
                                |--------------------------------------------------------------------------
                                | Status Badge
                                |--------------------------------------------------------------------------
                                */

                                $statusClass = [

                                    "Pending"   => "info",
                                    "Accepted"  => "success",
                                    "Confirmed" => "success",
                                    "Cancelled" => "danger",
                                    "Shipping"  => "primary",
                                    "Shipped"   => "primary",
                                    "Delivered" => "dark"

                                ];

                                $currentStatus =
                                    $order['order_status'] ?? 'Pending';

                                $paymentStatus =
                                    $order['payment_status'] ?? 'Pending';

                                ?>

                                <tr>


                                    <!-- ORDER ID -->

                                    <td>

                                        #0<?= (int)$order['id']; ?>

                                    </td>


                                    <!-- NAME -->

                                    <td>

                                        <?= htmlspecialchars(
                                            $order['full_name'] ?? '-'
                                        ); ?>

                                    </td>


                                    <!-- EMAIL -->

                                    <td>

                                        <?= htmlspecialchars(
                                            $order['email'] ?? '-'
                                        ); ?>

                                    </td>


                                    <!-- AMOUNT -->

                                    <td>

                                        <strong>

                                            <?= htmlspecialchars(
                                                $order['currency'] ?? ''
                                            ); ?>

                                            <?= number_format(
                                                (float)($order['total'] ?? 0),
                                                2
                                            ); ?>

                                        </strong>

                                    </td>


                                    <!-- PAYMENT -->

                                    <td>

                                        <span
                                            class="badge bg-<?=
                                                $paymentStatus == 'Pending'
                                                    ? 'warning'
                                                    : 'success'
                                            ?>"
                                        >

                                            <?= htmlspecialchars(
                                                $paymentStatus
                                            ); ?>

                                        </span>

                                    </td>


                                    <!-- STATUS -->

                                    <td>

                                        <span
                                            class="badge bg-<?=
                                                $statusClass[$currentStatus]
                                                ?? 'secondary'
                                            ?>"
                                        >

                                            <?= htmlspecialchars(
                                                $currentStatus
                                            ); ?>

                                        </span>

                                    </td>


                                    <!-- ACTION -->

                                    <td>

                                        <select
                                            class="form-select form-select-sm mb-2 order-status-select"
                                            data-id="<?= (int)$order['id']; ?>"
                                        >

                                            <option
                                                value="Pending"
                                                <?= $currentStatus == 'Pending'
                                                    ? 'selected'
                                                    : ''
                                                ?>
                                            >
                                                Pending
                                            </option>

                                            <option
                                                value="Accepted"
                                                <?= $currentStatus == 'Accepted'
                                                    ? 'selected'
                                                    : ''
                                                ?>
                                            >
                                                Accepted
                                            </option>

                                            <option
                                                value="Shipping"
                                                <?= $currentStatus == 'Shipping'
                                                    ? 'selected'
                                                    : ''
                                                ?>
                                            >
                                                Shipping
                                            </option>

                                            <option
                                                value="Cancelled"
                                                <?= $currentStatus == 'Cancelled'
                                                    ? 'selected'
                                                    : ''
                                                ?>
                                            >
                                                Cancelled
                                            </option>

                                        </select>


                                        <button
                                            type="button"
                                            class="btn btn-sm text-white w-100"
                                            style="background:#b0175f"
                                            onclick="updateStatus(<?= (int)$order['id']; ?>)"
                                        >

                                            Update

                                        </button>

                                    </td>


                                    <!-- VIEW -->

                                    <td>

                                        <!--
                                        IMPORTANT:
                                        Safely encoded JSON is stored in data-order.
                                        This prevents quotes/apostrophes from breaking
                                        the View button.
                                        -->

                                        <button
                                            type="button"
                                            class="btn btn-info btn-sm view-btn"
                                            data-order="<?= $orderJson; ?>"
                                            onclick="viewOrder(this)"
                                        >

                                            <i class="bi bi-eye"></i>
                                            View

                                        </button>

                                    </td>

                                </tr>

                            <?php endforeach; ?>

                            </tbody>

                        </table>

                    </div>


                <?php else: ?>


                    <div class="text-center py-5">

                        <h5 class="text-muted">
                            No Orders Found
                        </h5>

                    </div>


                <?php endif; ?>


            </div>

        </div>

    </div>

</div>



<!-- ================================================================
     ORDER MODAL
================================================================ -->

<div
    class="modal fade"
    id="orderModal"
    tabindex="-1"
    aria-hidden="true"
>

    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">

        <div class="modal-content border-0 shadow">


            <!-- MODAL HEADER -->

            <div
                class="modal-header text-white"
                style="background:#000;"
            >

                <h5 class="modal-title">

                    <i class="bi bi-receipt me-2"></i>
                    Order Details

                </h5>

                <button
                    type="button"
                    class="btn-close btn-close-white"
                    data-bs-dismiss="modal"
                ></button>

            </div>


            <!-- MODAL BODY -->

            <div class="modal-body">


                <!-- =================================================
                     CUSTOMER INFORMATION
                ================================================== -->

                <h6 class="fw-bold mb-3 text-secondary">

                    Customer Information

                </h6>


                <div class="row g-3 mb-4">


                    <div class="col-md-4">

                        <div class="modal-label">
                            Name
                        </div>

                        <div
                            id="m_name"
                            class="modal-value"
                        >
                            -
                        </div>

                    </div>


                    <div class="col-md-4">

                        <div class="modal-label">
                            Email
                        </div>

                        <div
                            id="m_email"
                            class="modal-value"
                        >
                            -
                        </div>

                    </div>


                    <div class="col-md-4">

                        <div class="modal-label">
                            Mobile
                        </div>

                        <div
                            id="m_mobile"
                            class="modal-value"
                        >
                            -
                        </div>

                    </div>


                    <div class="col-md-4">

                        <div class="modal-label">
                            Order ID
                        </div>

                        <div
                            id="m_id"
                            class="modal-value"
                        >
                            -
                        </div>

                    </div>


                    <div class="col-md-4">

                        <div class="modal-label">
                            Order No
                        </div>

                        <div
                            id="m_order_no"
                            class="modal-value"
                        >
                            -
                        </div>

                    </div>


                    <div class="col-md-4">

                        <div class="modal-label">
                            Currency
                        </div>

                        <div
                            id="m_currency"
                            class="modal-value"
                        >
                            -
                        </div>

                    </div>

                </div>


                <!-- =================================================
                     SHIPPING ADDRESS
                ================================================== -->

                <h6 class="fw-bold mb-3 text-secondary">

                    Shipping Address

                </h6>


                <div class="p-3 mb-4 bg-light rounded border">

                    <div id="m_address">
                        -
                    </div>

                    <div class="mt-2">

                        <b>City:</b>

                        <span id="m_city">
                            -
                        </span>

                        &nbsp; | &nbsp;

                        <b>Pincode:</b>

                        <span id="m_pincode">
                            -
                        </span>

                    </div>

                </div>


                <!-- =================================================
                     PRODUCT DETAILS
                ================================================== -->

                <h6 class="fw-bold mb-3 text-secondary">

                    Product Details

                </h6>


                <div class="table-responsive mb-4">

                    <table class="table table-bordered align-middle order-product-table">

                        <thead>

                            <tr>

                                <th>
                                    #
                                </th>

                                <th>
                                    Product
                                </th>

                                <th>
                                    Size
                                </th>

                                <th>
                                    Color
                                </th>

                                <th>
                                    Qty
                                </th>

                                <th>
                                    Price
                                </th>

                                <th>
                                    Size Requirement
                                </th>

                            </tr>

                        </thead>


                        <tbody id="m_products">

                            <tr>

                                <td
                                    colspan="7"
                                    class="text-center text-muted"
                                >
                                    No product details
                                </td>

                            </tr>

                        </tbody>

                    </table>

                </div>


                <!-- =================================================
                     ORDER SUMMARY
                ================================================== -->

                <h6 class="fw-bold mb-3 text-secondary">

                    Order Summary

                </h6>


                <div class="row g-3 mb-4">


                    <!-- SUBTOTAL -->

                    <div class="col-md-4">

                        <div class="summary-box">

                            <div class="summary-title">
                                Subtotal
                            </div>

                            <div
                                id="m_total"
                                class="summary-value"
                            >
                                -
                            </div>

                        </div>

                    </div>


                    <!-- DISCOUNT -->

                    <div class="col-md-4">

                        <div class="summary-box">

                            <div class="summary-title">
                                Discount
                            </div>

                            <div
                                id="m_discount"
                                class="summary-value text-success"
                            >
                                -
                            </div>

                        </div>

                    </div>


                    <!-- FINAL -->

                    <div class="col-md-4">

                        <div class="summary-box final-box">

                            <div class="summary-title">
                                Final Total
                            </div>

                            <div
                                id="m_final"
                                class="summary-value"
                            >
                                -
                            </div>

                        </div>

                    </div>

                </div>


                <!-- =================================================
                     PAYMENT DETAILS
                ================================================== -->

                <h6 class="fw-bold mb-3 text-secondary">

                    Payment Details

                </h6>


                <table class="table table-bordered">

                    <tr>

                        <th width="30%">
                            Method
                        </th>

                        <td id="m_method">
                            -
                        </td>

                    </tr>


                    <tr>

                        <th>
                            Payment Status
                        </th>

                        <td id="m_payment">
                            -
                        </td>

                    </tr>


                    <tr>

                        <th>
                            Razorpay ID
                        </th>

                        <td id="m_razor">
                            -
                        </td>

                    </tr>


                    <tr>

                        <th>
                            Order Status
                        </th>

                        <td id="m_status">
                            -
                        </td>

                    </tr>

                </table>


            </div>


            <!-- MODAL FOOTER -->

            <div class="modal-footer">

                <button
                    type="button"
                    class="btn btn-secondary"
                    data-bs-dismiss="modal"
                >

                    Close

                </button>

            </div>

        </div>

    </div>

</div>



<!-- ================================================================
     STATUS MODAL
================================================================ -->

<div
    class="modal fade"
    id="statusModal"
    tabindex="-1"
    aria-hidden="true"
>

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content">

            <div
                class="modal-header text-white"
                style="background:#b0175f"
            >

                <h5 class="modal-title">
                    Status
                </h5>

                <button
                    type="button"
                    class="btn-close btn-close-white"
                    data-bs-dismiss="modal"
                ></button>

            </div>

            <div class="modal-body">

                <p
                    id="modalMsg"
                    class="mb-0"
                >
                    Updating...
                </p>

            </div>

        </div>

    </div>

</div>



<!-- ================================================================
     BOOTSTRAP JS
================================================================ -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>


<script>

/*
|--------------------------------------------------------------------------
| VIEW ORDER
|--------------------------------------------------------------------------
*/

function viewOrder(button) {

    try {

        /*
        |--------------------------------------------------------------------------
        | Read safely encoded JSON from button
        |--------------------------------------------------------------------------
        */

        const json = button.getAttribute("data-order");

        if (!json) {

            alert("Order data not found.");

            return;

        }


        const order = JSON.parse(json);


        /*
        |--------------------------------------------------------------------------
        | Customer Details
        |--------------------------------------------------------------------------
        */

        document.getElementById("m_id").textContent =
            order.id ? "#" + order.id : "-";

        document.getElementById("m_name").textContent =
            order.full_name || "-";

        document.getElementById("m_email").textContent =
            order.email || "-";

        document.getElementById("m_mobile").textContent =
            order.phone || "-";

        document.getElementById("m_order_no").textContent =
            order.order_no || "-";

        document.getElementById("m_currency").textContent =
            order.currency || "-";


        /*
        |--------------------------------------------------------------------------
        | Address
        |--------------------------------------------------------------------------
        */

        document.getElementById("m_address").textContent =
            order.address || "-";

        document.getElementById("m_city").textContent =
            order.city || "-";

        document.getElementById("m_pincode").textContent =
            order.pincode || "-";


        /*
        |--------------------------------------------------------------------------
        | Product Details
        |--------------------------------------------------------------------------
        */

        const products =
            order.product_name
                ? String(order.product_name).split("|||")
                : [];

        const sizes =
            order.size
                ? String(order.size).split("|||")
                : [];

        const colors =
            order.color
                ? String(order.color).split("|||")
                : [];

        const quantities =
            order.qty
                ? String(order.qty).split("|||")
                : [];

        const prices =
            order.item_price
                ? String(order.item_price).split("|||")
                : [];

        const sizeRequirements =
            order.size_requirement
                ? String(order.size_requirement).split("|||")
                : [];


        let productHTML = "";


        if (products.length > 0) {

            products.forEach(function(product, index) {

                const size =
                    sizes[index] || "-";

                const color =
                    colors[index] || "-";

                const qty =
                    quantities[index] || "0";

                const price =
                    prices[index] || "0.00";

                const sizeRequirement =
                    sizeRequirements[index] || "-";


                productHTML += `

                    <tr>

                        <td>
                            ${index + 1}
                        </td>

                        <td class="product-name-cell">
                            ${escapeHTML(product)}
                        </td>

                        <td>
                            ${escapeHTML(size)}
                        </td>

                        <td>
                            ${escapeHTML(color)}
                        </td>

                        <td>
                            ${escapeHTML(qty)}
                        </td>

                        <td>
                            ${escapeHTML(order.currency || "")}
                            ${formatMoney(price)}
                        </td>

                        <td>
                            ${escapeHTML(sizeRequirement)}
                        </td>

                    </tr>

                `;

            });

        } else {

            productHTML = `

                <tr>

                    <td
                        colspan="7"
                        class="text-center text-muted"
                    >
                        No product details found.
                    </td>

                </tr>

            `;

        }


        document.getElementById("m_products").innerHTML =
            productHTML;


        /*
        |--------------------------------------------------------------------------
        | Order Summary
        |--------------------------------------------------------------------------
        */

        const currency =
            order.currency || "";


        const subtotal =
            parseFloat(order.subtotal || 0);


        const discount =
            parseFloat(order.discount || 0);


        const finalTotal =
            parseFloat(order.total || 0);


        document.getElementById("m_total").textContent =
            currency + " " + formatMoney(subtotal);


        document.getElementById("m_discount").textContent =
            "- " + currency + " " + formatMoney(discount);


        document.getElementById("m_final").textContent =
            currency + " " + formatMoney(finalTotal);


        /*
        |--------------------------------------------------------------------------
        | Payment Details
        |--------------------------------------------------------------------------
        */

        document.getElementById("m_method").textContent =
            order.payment_method || "-";

        document.getElementById("m_payment").textContent =
            order.payment_status || "-";

        document.getElementById("m_razor").textContent =
            order.razorpay_payment_id || "-";

        document.getElementById("m_status").textContent =
            order.order_status || "-";


        /*
        |--------------------------------------------------------------------------
        | Open Modal
        |--------------------------------------------------------------------------
        */

        const modalElement =
            document.getElementById("orderModal");


        const modal =
            bootstrap.Modal.getOrCreateInstance(
                modalElement
            );


        modal.show();


    } catch (error) {

        console.error(
            "View Order Error:",
            error
        );

        alert(
            "Unable to open order details."
        );

    }

}


/*
|--------------------------------------------------------------------------
| Escape HTML
|--------------------------------------------------------------------------
|
| Prevents product/customer data from breaking modal HTML.
|
*/

function escapeHTML(value) {

    return String(value ?? "")
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");

}


/*
|--------------------------------------------------------------------------
| Format Money
|--------------------------------------------------------------------------
*/

function formatMoney(value) {

    const number =
        parseFloat(
            String(value).replace(/,/g, "")
        );


    if (isNaN(number)) {
        return "0.00";
    }


    return number.toFixed(2);

}


/*
|--------------------------------------------------------------------------
| UPDATE STATUS
|--------------------------------------------------------------------------
*/

function updateStatus(orderId) {

    const select =
        document.querySelector(
            'select[data-id="' + orderId + '"]'
        );


    if (!select) {

        alert(
            "Status selector not found."
        );

        return;

    }


    const status =
        select.value;


    const modalElement =
        document.getElementById("statusModal");


    const modal =
        bootstrap.Modal.getOrCreateInstance(
            modalElement
        );


    document.getElementById("modalMsg").textContent =
        "Updating...";


    modal.show();


    fetch("update_order_status.php", {

        method: "POST",

        headers: {

            "Content-Type":
                "application/x-www-form-urlencoded"

        },

        body:
            "order_id=" +
            encodeURIComponent(orderId) +

            "&status=" +
            encodeURIComponent(status)

    })

    .then(function(response) {

        return response.text();

    })

    .then(function(data) {

        modal.hide();

        alert(data);

        location.reload();

    })

    .catch(function(error) {

        console.error(
            "Status Update Error:",
            error
        );

        modal.hide();

        alert(
            "Something went wrong."
        );

    });

}

</script>