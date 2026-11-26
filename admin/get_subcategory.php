<?php
include "config.php";

$cat = $_GET['cat'];
$sub = $conn->query("SELECT * FROM subcategories WHERE category_id='$cat'");

echo "<option value=''>Select Subcategory</option>";
while ($s = $sub->fetch_assoc()) {
    echo "<option value='{$s['id']}'>{$s['subcategory_name']}</option>";
}
?>
