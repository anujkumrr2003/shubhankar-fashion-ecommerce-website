<?php
// include "config.php";

// $category_id = $_GET['category_id'];

// $q = $conn->query("
// SELECT DISTINCT subcategory_name 
// FROM products 
// WHERE category_id='$category_id'
// ");

// echo '<option value="">All Subcategories</option>';

// while($r = $q->fetch_assoc()){
//     echo '<option value="'.$r['subcategory_name'].'">'.$r['subcategory_name'].'</option>';
// }


include "config.php";

if(isset($_GET['category_id']))
{
    $category_id = $_GET['category_id'];

    $q = $conn->query("
        SELECT subcategory_name 
        FROM subcategories 
        WHERE category_id='$category_id'
    ");

    echo '<option value="">Select Sub Category</option>';

    while($r = $q->fetch_assoc())
    {
        echo '<option value="'.$r['subcategory_name'].'">'
            .$r['subcategory_name'].
            '</option>';
    }
}
?>