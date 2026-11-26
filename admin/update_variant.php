<?php
include "config.php";

$id = $_POST['variant_id'];
$product_id = $_POST['product_id'];
$color = $_POST['color'];
$size  = $_POST['size'];
$price = $_POST['variant_price'];

$imgNames = [];

if (!empty($_FILES['images']['name'][0])) {
    foreach ($_FILES['images']['name'] as $key => $img) {
        $fileName = time() . "_" . $img;
        move_uploaded_file($_FILES['images']['tmp_name'][$key], "uploads/" . $fileName);
        $imgNames[] = $fileName;
    }
    $imgStr = implode(",", $imgNames);
    $img_sql = ", images='$imgStr'";
} else {
    $img_sql = "";
}

$conn->query("
    UPDATE product_variants SET color='$color', size='$size',
    variant_price='$price' $img_sql WHERE id='$id'
");

header("Location: variants_list.php?id=$product_id");
