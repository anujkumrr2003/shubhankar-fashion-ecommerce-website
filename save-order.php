<?php

session_start();
include "config.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login");
    exit;
}

error_reporting(0);

$user_id = (int)$_SESSION['user_id'];


/* =========================================================
   BASIC HELPERS
========================================================= */

function postValue($key, $default = '')
{
    return isset($_POST[$key]) ? trim((string)$_POST[$key]) : $default;
}


/* =========================================================
   GET CHECKOUT DATA
========================================================= */

$full_name = mysqli_real_escape_string(
    $conn,
    postValue('full_name')
);

$email = mysqli_real_escape_string(
    $conn,
    postValue('email')
);

$phone = mysqli_real_escape_string(
    $conn,
    postValue('phone')
);

$country = mysqli_real_escape_string(
    $conn,
    postValue('country')
);

$state = mysqli_real_escape_string(
    $conn,
    postValue('state')
);

$city = mysqli_real_escape_string(
    $conn,
    postValue('city')
);

$address = mysqli_real_escape_string(
    $conn,
    trim(
        postValue('address') . ' ' .
        postValue('address2')
    )
);

$pincode = mysqli_real_escape_string(
    $conn,
    postValue('pincode')
);

$company_name = mysqli_real_escape_string(
    $conn,
    postValue('company_name')
);

$gstin = mysqli_real_escape_string(
    $conn,
    postValue('gstin')
);


/* =========================================================
   BILLING DETAILS
========================================================= */

$billing_full_name = mysqli_real_escape_string(
    $conn,
    postValue('billing_full_name')
);

$billing_phone = mysqli_real_escape_string(
    $conn,
    postValue('billing_phone')
);

$billing_state = mysqli_real_escape_string(
    $conn,
    postValue('billing_state')
);

$billing_city = mysqli_real_escape_string(
    $conn,
    postValue('billing_city')
);

$billing_address = mysqli_real_escape_string(
    $conn,
    postValue('billing_address')
);

$billing_pincode = mysqli_real_escape_string(
    $conn,
    postValue('billing_pincode')
);

$billing_address_type = mysqli_real_escape_string(
    $conn,
    postValue('billing_address_type')
);

$billing_region = mysqli_real_escape_string(
    $conn,
    postValue('billing_region')
);

$same_as_shipping = (int)($_POST['same_as_shipping'] ?? 1);


/* =========================================================
   CURRENCY
========================================================= */

$currency = strtoupper(
    mysqli_real_escape_string(
        $conn,
        postValue('currency', 'INR')
    )
);

$currency_rate = isset($_POST['currency_rate'])
    ? (float)$_POST['currency_rate']
    : 1;


/*
|--------------------------------------------------------------------------
| Safety
|--------------------------------------------------------------------------
*/

if ($currency_rate <= 0 || !is_finite($currency_rate)) {
    $currency_rate = 1;
}


/* =========================================================
   COUPON
========================================================= */

$coupon_code = mysqli_real_escape_string(
    $conn,
    postValue('coupon_code')
);


/* =========================================================
   IMPORTANT:
   DO NOT TRUST POSTED SUBTOTAL / TOTAL
   CALCULATE AGAIN FROM DATABASE
========================================================= */


/* =========================================================
   GET CART
========================================================= */

$cart = mysqli_query(
    $conn,
    "
    SELECT
        product_id,
        variant_id,
        size,
        color,
        qty
    FROM cart
    WHERE user_id='$user_id'
    "
);

if (!$cart) {
    die("Unable to read cart.");
}

if (mysqli_num_rows($cart) == 0) {

    die(
        '<div style="
            background:#fff3cd;
            color:#856404;
            border:1px solid #ffeeba;
            padding:20px;
            border-radius:8px;
            text-align:center;
            font-size:16px;
            font-weight:600;
            margin:20px;
        ">
            <div style="font-size:18px;margin-bottom:15px;">
                🛒 Your cart is empty.
            </div>

            <a href="shop"
               style="
                    display:inline-block;
                    background:#000;
                    color:#fff;
                    text-decoration:none;
                    padding:10px 20px;
                    border-radius:6px;
                    font-size:15px;
                    font-weight:600;
               ">
                Continue Shopping
            </a>
        </div>'
    );
}


/* =========================================================
   READ CART + CALCULATE REAL INR SUBTOTAL
========================================================= */

$cartItems = [];

$subtotalINR = 0;


/*
|--------------------------------------------------------------------------
| Variant statement
|--------------------------------------------------------------------------
*/

$variantStmt = $conn->prepare(
    "
    SELECT variant_price
    FROM product_variants
    WHERE id=?
    AND product_id=?
    LIMIT 1
    "
);

if (!$variantStmt) {
    die("Unable to prepare variant query.");
}


/*
|--------------------------------------------------------------------------
| Product statement
|--------------------------------------------------------------------------
*/

$productStmt = $conn->prepare(
    "
    SELECT price
    FROM products
    WHERE id=?
    LIMIT 1
    "
);

if (!$productStmt) {
    die("Unable to prepare product query.");
}


/*
|--------------------------------------------------------------------------
| Read every cart item
|--------------------------------------------------------------------------
*/

while ($row = mysqli_fetch_assoc($cart)) {

    $product_id = (int)$row['product_id'];
    $variant_id = (int)$row['variant_id'];
    $qty        = (int)$row['qty'];

    $size  = $row['size'] ?? '';
    $color = $row['color'] ?? '';

    if ($qty <= 0) {
        $qty = 1;
    }


    /* =====================================================
       PRODUCT PRICE
    ===================================================== */

    $productStmt->bind_param(
        "i",
        $product_id
    );

    $productStmt->execute();

    $productResult = $productStmt->get_result();

    if (!$productResult || $productResult->num_rows == 0) {
        throw new Exception("Product not found.");
    }

    $productData = $productResult->fetch_assoc();

    $priceINR = (float)$productData['price'];


    /* =====================================================
       VARIANT PRICE
    ===================================================== */

    if ($variant_id > 0) {

        $variantStmt->bind_param(
            "ii",
            $variant_id,
            $product_id
        );

        $variantStmt->execute();

        $variantResult = $variantStmt->get_result();

        if ($variantResult && $variantResult->num_rows > 0) {

            $variantData = $variantResult->fetch_assoc();

            $priceINR = (float)$variantData['variant_price'];
        }
    }


    /* =====================================================
       INR ITEM TOTAL
    ===================================================== */

    $itemSubtotalINR =
        $priceINR * $qty;


    $subtotalINR +=
        $itemSubtotalINR;


    /*
    |--------------------------------------------------------------------------
    | Store original INR price for order_items
    |--------------------------------------------------------------------------
    */

    $cartItems[] = [
        'product_id' => $product_id,
        'variant_id' => $variant_id,
        'size'       => $size,
        'color'      => $color,
        'qty'        => $qty,
        'priceINR'   => $priceINR
    ];
}


/* =========================================================
   COUPON DISCOUNT
========================================================= */

/*
 * Existing checkout session discount is still respected.
 *
 * IMPORTANT:
 * This discount is an INR discount because your
 * promo system is based on INR product prices.
 */

$discountINR = isset($_SESSION['discount'])
    ? (float)$_SESSION['discount']
    : 0;

if ($discountINR < 0) {
    $discountINR = 0;
}


/*
|--------------------------------------------------------------------------
| Never allow discount greater than subtotal
|--------------------------------------------------------------------------
*/

if ($discountINR > $subtotalINR) {
    $discountINR = $subtotalINR;
}


/* =========================================================
   FOREIGN COUNTRY MARKUP
========================================================= */

/*
|--------------------------------------------------------------------------
| India
| -----
| No markup
|
| Foreign
| -------
| India price + 70%
|
| Example:
|
| ₹100
| × 1.70
| = ₹170
|--------------------------------------------------------------------------
*/

$isIndia = false;


/*
 * Currency itself is the safest indicator here.
 * INR = India.
 */

if ($currency === 'INR') {
    $isIndia = true;
}


/*
|--------------------------------------------------------------------------
| Foreign subtotal
|--------------------------------------------------------------------------
*/

// if ($isIndia) {

//     $foreignSubtotalINR =
//         $subtotalINR;

// } else {

//     $foreignSubtotalINR =
//         $subtotalINR * 1.70;
// }


if ($isIndia) {
    // India = 20% increase
    $foreignSubtotalINR = $subtotalINR * 1.20;
} else {
    // Other countries = 70% increase
    $foreignSubtotalINR = $subtotalINR * 1.70;
}


/* =========================================================
   DISCOUNT
========================================================= */

/*
 * Discount is subtracted after the foreign selling price
 * is calculated.
 */

$foreignTotalINR =
    $foreignSubtotalINR - $discountINR;


if ($foreignTotalINR < 0) {
    $foreignTotalINR = 0;
}


/* =========================================================
   CURRENCY CONVERSION
========================================================= */

$finalSubtotal =
    $foreignSubtotalINR * $currency_rate;

$finalDiscount =
    $discountINR * $currency_rate;

$finalTotal =
    $foreignTotalINR * $currency_rate;


/*
|--------------------------------------------------------------------------
| Round to 2 decimals
|--------------------------------------------------------------------------
*/

$finalSubtotal =
    round($finalSubtotal, 2);

$finalDiscount =
    round($finalDiscount, 2);

$finalTotal =
    round($finalTotal, 2);


/* =========================================================
   DEBUG LOG
========================================================= */

file_put_contents(
    __DIR__ . '/save-order-price.log',
    date('Y-m-d H:i:s') .
    " | User: " . $user_id .
    " | Currency: " . $currency .
    " | Rate: " . $currency_rate .
    " | INR Subtotal: " . $subtotalINR .
    " | Foreign Base: " . $foreignSubtotalINR .
    " | Discount INR: " . $discountINR .
    " | Final Subtotal: " . $finalSubtotal .
    " | Final Discount: " . $finalDiscount .
    " | Final Total: " . $finalTotal .
    PHP_EOL,
    FILE_APPEND
);


/* =========================================================
   GST
========================================================= */

$gst = 0;


/* =========================================================
   ORDER NUMBER
========================================================= */

$order_no =
    "ORD" .
    date("YmdHis") .
    rand(100, 999);


/* =========================================================
   START TRANSACTION
========================================================= */

mysqli_begin_transaction($conn);


try {


    /* =====================================================
       INSERT ORDER
    ===================================================== */

    $sql = "
    INSERT INTO orders
    (
        order_no,
        user_id,
        full_name,
        email,
        phone,
        country,
        state,
        city,
        address,
        pincode,
        company_name,
        gstin,

        billing_full_name,
        billing_phone,
        billing_state,
        billing_city,
        billing_address,
        billing_pincode,
        billing_address_type,
        billing_region,
        same_as_shipping,

        subtotal,
        gst,
        discount,
        total,
        coupon_code,
        payment_status,
        order_status,
        currency
    )
    VALUES
    (
        '$order_no',
        '$user_id',
        '$full_name',
        '$email',
        '$phone',
        '$country',
        '$state',
        '$city',
        '$address',
        '$pincode',
        '$company_name',
        '$gstin',

        '$billing_full_name',
        '$billing_phone',
        '$billing_state',
        '$billing_city',
        '$billing_address',
        '$billing_pincode',
        '$billing_address_type',
        '$billing_region',
        '$same_as_shipping',

        '$finalSubtotal',
        '$gst',
        '$finalDiscount',
        '$finalTotal',
        '$coupon_code',
        'Pending',
        'Pending',
        '$currency'
    )
    ";


    if (!mysqli_query($conn, $sql)) {
        throw new Exception(
            "Unable to save order."
        );
    }


    /* =====================================================
       ORDER ID
    ===================================================== */

    $order_id =
        mysqli_insert_id($conn);


    if ($order_id <= 0) {
        throw new Exception(
            "Invalid Order ID."
        );
    }


    /* =====================================================
       SAVE SESSION
    ===================================================== */

    $_SESSION['order_id'] =
        $order_id;

    $_SESSION['order_no'] =
        $order_no;


    /* =====================================================
       ORDER ITEMS
    ===================================================== */

    $itemStmt = $conn->prepare(
        "
        INSERT INTO order_items
        (
            order_id,
            product_id,
            variant_id,
            size,
            color,
            price,
            qty
        )
        VALUES
        (
            ?, ?, ?, ?, ?, ?, ?
        )
        "
    );


    if (!$itemStmt) {
        throw new Exception(
            "Unable to prepare order item query."
        );
    }


    foreach ($cartItems as $item) {

        $product_id =
            (int)$item['product_id'];

        $variant_id =
            (int)$item['variant_id'];

        $size =
            $item['size'];

        $color =
            $item['color'];

        $qty =
            (int)$item['qty'];

        $priceINR =
            (float)$item['priceINR'];


        /* =================================================
           FOREIGN 70% MARKUP
        ================================================= */

        // if (!$isIndia) {

        //     $sellingPriceINR =
        //         $priceINR * 1.70;

        // } else {

        //     $sellingPriceINR =
        //         $priceINR;
        // }
        
        if ($isIndia) {
    // India = 20% increase
    $sellingPriceINR = $priceINR * 1.20;
} else {
    // Other countries = 70% increase
    $sellingPriceINR = $priceINR * 1.70;
}

$finalItemPrice = round($sellingPriceINR * $currency_rate, 2);


        /* =================================================
           CURRENCY CONVERSION
        ================================================= */

        $finalItemPrice =
            round(
                $sellingPriceINR *
                $currency_rate,
                2
            );


        /* =================================================
           INSERT ITEM
        ================================================= */

        $itemStmt->bind_param(
            "iiissdi",
            $order_id,
            $product_id,
            $variant_id,
            $size,
            $color,
            $finalItemPrice,
            $qty
        );


        if (!$itemStmt->execute()) {

            throw new Exception(
                "Unable to save order item."
            );
        }
    }


    /* =====================================================
       COMMIT
    ===================================================== */

    mysqli_commit($conn);


    /* =====================================================
       CLEANUP SESSION COUPON
    ===================================================== */

    unset($_SESSION['coupon']);
    unset($_SESSION['discount']);


    /* =====================================================
       PAYMENT PAGE
    ===================================================== */

    header("Location: payment");
    exit;


} catch (Exception $e) {

    mysqli_rollback($conn);

    die(
        htmlspecialchars(
            $e->getMessage()
        )
    );
}
?>



<?php
// session_start();
// include "config.php";

// if (!isset($_SESSION['user_id'])) {
//     header("Location: login");
//     exit;
// }
// error_reporting(0);
// $user_id = $_SESSION['user_id'];

// /*
// |--------------------------------------------------------------------------
// | Get Checkout Form Data
// |--------------------------------------------------------------------------
// */

// $full_name = mysqli_real_escape_string($conn, trim($_POST['full_name']));
// $email     = mysqli_real_escape_string($conn, trim($_POST['email']));
// $phone     = mysqli_real_escape_string($conn, trim($_POST['phone']));

// $country   = mysqli_real_escape_string($conn, trim($_POST['country']));
// $state      = mysqli_real_escape_string($conn, trim($_POST['state']));
// $city       = mysqli_real_escape_string($conn, trim($_POST['city']));

// $address = mysqli_real_escape_string(
//     $conn,
//     trim($_POST['address'] . " " . $_POST['address2'])
// );

// $pincode = mysqli_real_escape_string($conn, trim($_POST['pincode']));

// $company_name = mysqli_real_escape_string($conn, trim($_POST['company_name']));
// $gstin        = mysqli_real_escape_string($conn, trim($_POST['gstin']));

// $billing_full_name    = mysqli_real_escape_string($conn, trim($_POST['billing_full_name'] ?? ''));
// $billing_phone        = mysqli_real_escape_string($conn, trim($_POST['billing_phone'] ?? ''));
// $billing_state        = mysqli_real_escape_string($conn, trim($_POST['billing_state'] ?? ''));
// $billing_city         = mysqli_real_escape_string($conn, trim($_POST['billing_city'] ?? ''));
// $billing_address      = mysqli_real_escape_string($conn, trim($_POST['billing_address'] ?? ''));
// $billing_pincode      = mysqli_real_escape_string($conn, trim($_POST['billing_pincode'] ?? ''));
// $billing_address_type = mysqli_real_escape_string($conn, trim($_POST['billing_address_type'] ?? ''));
// $billing_region       = mysqli_real_escape_string($conn, trim($_POST['billing_region'] ?? ''));
// $same_as_shipping     = (int)($_POST['same_as_shipping'] ?? 1);





// $currency      = mysqli_real_escape_string($conn, $_POST['currency']);
// $currency_rate = (float)$_POST['currency_rate'];

// if ($currency_rate <= 0) {
//     $currency_rate = 1;
// }

// $coupon_code = mysqli_real_escape_string(
//     $conn,
//     $_POST['coupon_code']
// );

// /*
// |--------------------------------------------------------------------------
// | Amounts
// |--------------------------------------------------------------------------
// */

// $subtotal = (float)$_POST['subtotal'];
// $discount = (float)$_POST['discount'];
// $total    = (float)$_POST['total'];

// $gst = 0;

// /*
// |--------------------------------------------------------------------------
// | Generate Order Number
// |--------------------------------------------------------------------------
// */

// $order_no = "ORD" . date("YmdHis") . rand(100, 999);

// /*
// |--------------------------------------------------------------------------
// | Start Database Transaction
// |--------------------------------------------------------------------------
// */

// mysqli_begin_transaction($conn);


// try {

//     // Insert Order
//     /*
// |--------------------------------------------------------------------------
// | Save Order (Pending)
// |--------------------------------------------------------------------------
// */

//     $sql = "INSERT INTO orders
// (
//     order_no,
//     user_id,
//     full_name,
//     email,
//     phone,
//     country,
//     state,
//     city,
//     address,
//     pincode,
//     company_name,
//     gstin,
//     billing_full_name,
// billing_phone,
// billing_state,
// billing_city,
// billing_address,
// billing_pincode,
// billing_address_type,
// billing_region,
// same_as_shipping,
//     subtotal,
//     discount,
//     total,
//     coupon_code,
//     payment_status,
//     order_status,
//     currency
// )
// VALUES
// (
//     '$order_no',
//     '$user_id',
//     '$full_name',
//     '$email',
//     '$phone',
//     '$country',
//     '$state',
//     '$city',
//     '$address',
//     '$pincode',
//     '$company_name',
// '$gstin',

// '$billing_full_name',
// '$billing_phone',
// '$billing_state',
// '$billing_city',
// '$billing_address',
// '$billing_pincode',
// '$billing_address_type',
// '$billing_region',
// '$same_as_shipping',

// '$subtotal',
//     '$discount',
//     '$total',
//     '$coupon_code',
//     'Pending',
//     'Pending',
//     '$currency'
// )";

//     if (!mysqli_query($conn, $sql)) {
//         throw new Exception("Unable to save order.");
//     }


//     /*
// |--------------------------------------------------------------------------
// | Get Order ID
// |--------------------------------------------------------------------------
// */

//     $order_id = mysqli_insert_id($conn);

//     if ($order_id <= 0) {
//         throw new Exception("Invalid Order ID.");
//     }

//     /*
// |--------------------------------------------------------------------------
// | Save Session
// |--------------------------------------------------------------------------
// */

//     $_SESSION['order_id'] = $order_id;
//     $_SESSION['order_no'] = $order_no;



//     /*
// |--------------------------------------------------------------------------
// | Save Cart Products Into order_items
// |--------------------------------------------------------------------------
// */

//     $cart = mysqli_query($conn, "
// SELECT
//     product_id,
//     variant_id,
//     size,
//     color,
//     qty
// FROM cart
// WHERE user_id='$user_id'
// ");

//   if (mysqli_num_rows($cart) == 0) {
//     throw new Exception(
//         '<div style="
//             background:#fff3cd;
//             color:#856404;
//             border:1px solid #ffeeba;
//             padding:20px;
//             border-radius:8px;
//             text-align:center;
//             font-size:16px;
//             font-weight:600;
//             margin:20px 0;
//         ">
//             <div style="font-size:18px; margin-bottom:15px;">
//                 🛒 Your cart is empty.
//             </div>

//             <a href="shop"
//               style="
//                     display:inline-block;
//                     background:#000;
//                     color:#fff;
//                     text-decoration:none;
//                     padding:10px 20px;
//                     border-radius:6px;
//                     font-size:15px;
//                     font-weight:600;
//               ">
//                 Continue Shopping
//             </a>
//         </div>'
//     );
// }

//     $variantStmt = $conn->prepare("
// SELECT variant_price
// FROM product_variants
// WHERE id=?
// AND product_id=?
// LIMIT 1
// ");

//     $itemStmt = $conn->prepare("
// INSERT INTO order_items
// (
//     order_id,
//     product_id,
//     variant_id,
//     size,
//     color,
//     price,
//     qty
// )
// VALUES
// (
//     ?, ?, ?, ?, ?, ?, ?
// )
// ");

//     while ($row = mysqli_fetch_assoc($cart)) {

//         $product_id = (int)$row['product_id'];
//         $variant_id = (int)$row['variant_id'];
//         $qty = (int)$row['qty'];
//         $size = $row['size'];
//         $color      = $row['color'];

//         /*
//     -----------------------------------------
//     Product Price
//     -----------------------------------------
//     */

//         $product = mysqli_query($conn, "
//     SELECT price
//     FROM products
//     WHERE id='$product_id'
//     LIMIT 1
//     ");

//         if (mysqli_num_rows($product) == 0) {
//             throw new Exception("Product not found.");
//         }

//         $productData = mysqli_fetch_assoc($product);

//         $price = (float)$productData['price'];

//         /*
//     -----------------------------------------
//     Variant Price
//     -----------------------------------------
//     */

//         if ($variant_id > 0) {

//             $variantStmt->bind_param(
//                 "ii",
//                 $variant_id,
//                 $product_id
//             );

//             $variantStmt->execute();

//             $variantResult = $variantStmt->get_result();

//             if ($variantResult->num_rows) {

//                 $price = (float)$variantResult
//                     ->fetch_assoc()['variant_price'];
//             }
//         }

//         /*
//     -----------------------------------------
//     Currency Conversion
//     -----------------------------------------
//     */

//         $price = round(
//             $price * $currency_rate,
//             2
//         );

//         /*
//     -----------------------------------------
//     Save Item
//     -----------------------------------------
//     */

//         $itemStmt->bind_param(
//     "iiissdi",
//     $order_id,
//     $product_id,
//     $variant_id,
//     $size,
//     $color,
//     $price,
//     $qty
// );

//         if (!$itemStmt->execute()) {
//             throw new Exception("Unable to save order item.");
//         }
//     }


//     mysqli_commit($conn);

//     header("Location: payment");
//     exit;
// } catch (Exception $e) {

//     mysqli_rollback($conn);

//     die($e->getMessage());
// }
?>
