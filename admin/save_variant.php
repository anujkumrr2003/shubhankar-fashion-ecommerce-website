<?php
include "config.php";

$product_id = $_POST['product_id'];

$colors  = $_POST['color'];
$sizes   = $_POST['size'];
$prices  = $_POST['variant_price'];

for ($i = 0; $i < count($colors); $i++) {

    $imgNames = [];

    if (!empty($_FILES['variant_images']['name'][$i])) {
        foreach ($_FILES['variant_images']['name'][$i] as $key => $img) {

            $fileName = time() . "_" . $img;
            move_uploaded_file($_FILES['variant_images']['tmp_name'][$i][$key],
                               "uploads/" . $fileName);

            $imgNames[] = $fileName;
        }
    }

    $imgStr = implode(",", $imgNames);

    $conn->query("
        INSERT INTO product_variants (product_id, color, size, variant_price, images)
        VALUES ('$product_id', '{$colors[$i]}', '{$sizes[$i]}', '{$prices[$i]}', '$imgStr')
    ");
}

echo "Variants saved successfully";
