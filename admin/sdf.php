           <?php
            include 'config.php';

            $stmt = $conn->prepare("
SELECT 
o.*, 
b.name,
b.email,
b.mobile,
b.pincode,
b.city,
b.address
FROM orders o
LEFT JOIN billing_details b 
ON o.user_id = b.user_id
ORDER BY o.id DESC
");

            $stmt->execute();
            $orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            ?>

            <!DOCTYPE html>
            <html lang="en">

            <head>
                <meta charset="UTF-8">
                <title>Admin Orders</title>

                <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

                <style>
                    body {
                        background: #f4f5f7;
                    }

                    .header {
                        background: #b0175f;
                        color: white;
                        padding: 12px;
                        border-radius: 8px;
                    }

                    .table thead {
                        background: #b0175f;
                        color: white;
                    }

                    thead tr th {
                        color: #ffffff !important;
                    }
                </style>

            </head>

            <body>

                <div class="container py-4">

                    <h3 class="text-center header mb-4">Orders Management</h3>

                    <?php if (!empty($orders)): ?>

                        <div class="table-responsive">

                            <table class="table table-bordered text-center bg-white">

                                <thead>
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Amount</th>
                                        <th>Payment</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                        <th>View</th>
                                    </tr>
                                </thead>

                                <tbody>

                                    <?php foreach ($orders as $order): ?>

                                        <tr>

                                            <td>#0<?= $order['id'] ?></td>

                                            <td><?= htmlspecialchars($order['name']) ?></td>

                                            <td><?= htmlspecialchars($order['email']) ?></td>

                                            <td>₹<?= $order['final_amount'] ?></td>

                                            <td>
                                                <span class="badge bg-<?=
                                                                        $order['payment_status'] == "Pending" ? "warning" : "success"
                                                                        ?>">
                                                    <?= $order['payment_status'] ?>
                                                </span>
                                            </td>

                                            <td>

                                                <?php
                                                $statusClass = [
                                                    "Pending" => "info",
                                                    "Confirmed" => "success",
                                                    "Cancelled" => "danger",
                                                    "Shipped" => "primary",
                                                    "Delivered" => "dark"
                                                ];
                                                ?>

                                                <span class="badge bg-<?= $statusClass[$order['order_status']] ?? 'secondary' ?>">
                                                    <?= $order['order_status'] ?>
                                                </span>

                                            </td>

                                            <td>

                                                <select class="form-select form-select-sm mb-2"
                                                    data-id="<?= $order['id'] ?>">

                                                    <option value="Pending"
                                                        <?= $order['order_status'] == "Pending" ? "selected" : "" ?>>
                                                        Pending
                                                    </option>

                                                    <option value="Confirmed"
                                                        <?= $order['order_status'] == "Confirmed" ? "selected" : "" ?>>
                                                        Confirmed
                                                    </option>

                                                    <option value="Cancelled"
                                                        <?= $order['order_status'] == "Cancelled" ? "selected" : "" ?>>
                                                        Cancelled
                                                    </option>

                                                </select>

                                                <button class="btn btn-sm text-white w-100"
                                                    style="background:#b0175f"
                                                    onclick="updateStatus(<?= $order['id'] ?>)">
                                                    Update
                                                </button>

                                            </td>

                                            <td>

                                                <button class="btn btn-info btn-sm w-100"
                                                    onclick='viewOrder(<?= json_encode($order) ?>)'>
                                                    View
                                                </button>

                                            </td>

                                        </tr>

                                    <?php endforeach; ?>

                                </tbody>
                            </table>
                        </div>

                    <?php else: ?>

                        <p class="text-center">No Orders Found</p>

                    <?php endif; ?>

                </div>


                <!-- ORDER DETAIL MODAL -->

                <div class="modal fade" id="orderModal">
                    <div class="modal-dialog modal-lg modal-dialog-centered">
                        <div class="modal-content border-0 shadow">

                            <div class="modal-header text-white"
                                style="background:#b0175f">

                                <h5 class="modal-title">Order Details</h5>
                                <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>

                            </div>

                            <div class="modal-body">

                                <!-- Customer Info -->

                                <h6 class="fw-bold mb-3 text-secondary">Customer Information</h6>

                                <div class="row g-3 mb-4">

                                    <div class="col-md-6">
                                        <label class="text-muted small">Name</label>
                                        <div class="fw-semibold" id="m_name"></div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="text-muted small">Email</label>
                                        <div id="m_email"></div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="text-muted small">Mobile</label>
                                        <div id="m_mobile"></div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="text-muted small">Order ID</label>
                                        <div class="fw-semibold">
                                            #0<span id="m_id"></span>
                                        </div>
                                    </div>
                                </div>


                                <!-- Address -->

                                <h6 class="fw-bold mb-3 text-secondary">Shipping Address</h6>

                                <div class="p-3 mb-4 bg-light rounded border">

                                    <div id="m_address"></div>

                                    <div class="mt-2">
                                        <span class="me-2"><b>City:</b> <span id="m_city"></span></span>
                                        <span><b>Pincode:</b> <span id="m_pincode"></span></span>
                                    </div>

                                </div>


                                <!-- Order Summary -->

                                <h6 class="fw-bold mb-3 text-secondary">Order Summary</h6>

                                <div class="row g-3 mb-4">

                                    <div class="col-md-4">
                                        <div class="border rounded p-3 text-center">
                                            <div class="small text-muted">Total Amount</div>
                                            <div class="fw-bold fs-5" id="m_total"></div>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="border rounded p-3 text-center">
                                            <div class="small text-muted">Discount</div>
                                            <div class="fw-bold fs-5 text-success" id="m_discount"></div>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="border rounded p-3 text-center">
                                            <div class="small text-muted">Final Amount</div>
                                            <div class="fw-bold fs-5 text-danger" id="m_final"></div>
                                        </div>
                                    </div>

                                </div>


                                <!-- Payment Info -->



                                <h6 class="fw-bold mb-3 text-secondary">Payment Details</h6>

                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped align-middle">

                                        <tr>
                                            <th width="35%">Payment Method</th>
                                            <td id="m_method"></td>
                                        </tr>

                                        <tr>
                                            <th>Payment Status</th>
                                            <td id="m_payment"></td>
                                        </tr>

                                        <tr>
                                            <th>Razorpay ID</th>
                                            <td id="m_razor"></td>
                                        </tr>

                                        <tr>
                                            <th>Order Status</th>
                                            <td id="m_status"></td>
                                        </tr>

                                    </table>
                                </div>

                            </div>

                        </div>
                    </div>
                </div>


                <!-- STATUS MODAL -->

                <div class="modal fade" id="statusModal">

                    <div class="modal-dialog modal-dialog-centered">

                        <div class="modal-content">

                            <div class="modal-header text-white"
                                style="background:#b0175f">

                                <h5 class="modal-title">Status</h5>

                            </div>

                            <div class="modal-body">

                                <p id="modalMsg"></p>

                            </div>

                        </div>
                    </div>
                </div>


                <script>
                    function viewOrder(order) {

                        document.getElementById("m_id").innerText = order.id
                        document.getElementById("m_name").innerText = order.name
                        document.getElementById("m_email").innerText = order.email
                        document.getElementById("m_mobile").innerText = order.mobile
                        document.getElementById("m_address").innerText = order.address
                        document.getElementById("m_city").innerText = order.city
                        document.getElementById("m_pincode").innerText = order.pincode

                        document.getElementById("m_total").innerText = "₹" + order.total_amount
                        document.getElementById("m_discount").innerText = "₹" + order.discount_amount
                        document.getElementById("m_final").innerText = "₹" + order.final_amount

                        document.getElementById("m_method").innerText = order.payment_method
                        document.getElementById("m_payment").innerText = order.payment_status
                        document.getElementById("m_razor").innerText = order.razorpay_payment_id || "-"

                        document.getElementById("m_status").innerText = order.order_status

                        var modal = new bootstrap.Modal(document.getElementById('orderModal'))
                        modal.show()

                    }



                    function updateStatus(orderId) {

                        let status = document.querySelector(
                            'select[data-id="' + orderId + '"]'
                        ).value


                        var pop = new bootstrap.Modal(document.getElementById('statusModal'))

                        document.getElementById('modalMsg').innerHTML = "Updating..."

                        pop.show()


                        fetch("update_order_status.php", {

                                method: "POST",

                                headers: {
                                    "Content-Type": "application/x-www-form-urlencoded"
                                },

                                body: "order_id=" + orderId + "&status=" + status

                            })

                            .then(res => res.text())

                            .then(data => {

                                document.getElementById('modalMsg').innerHTML = data

                                setTimeout(() => {

                                    location.reload()

                                }, 1200)

                            })

                    }
                </script>


                <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

            </body>

            </html> 