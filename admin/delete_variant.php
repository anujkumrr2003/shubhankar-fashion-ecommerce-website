<?php
include "config.php";

$id = $_GET['id'];
$product_id = $_GET['product_id'];

$conn->query("DELETE FROM product_variants WHERE id='$id'");

header("Location: variants_list.php?id=$product_id");
