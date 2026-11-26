<?php

include "config.php";

$cat_id=$_POST['cat_id'];

$q=$conn->query("SELECT * FROM subcategories
WHERE category_id='$cat_id' AND status='Active'");

echo '<option value="">Select Subcategory</option>';

while($row=$q->fetch_assoc()){

echo "<option value='".$row['id']."'>".$row['subcategory_name']."</option>";

}