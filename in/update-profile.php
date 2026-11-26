<?php
session_start();
include "config.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Get User
$getUser = mysqli_query($conn, "SELECT * FROM user WHERE id='$user_id' LIMIT 1");

if (mysqli_num_rows($getUser) == 0) {
    die("User not found.");
}

$user = mysqli_fetch_assoc($getUser);

// Form Data
$full_name = mysqli_real_escape_string($conn, trim($_POST['full_name']));
$phone     = mysqli_real_escape_string($conn, trim($_POST['phone']));
$address   = mysqli_real_escape_string($conn, trim($_POST['address']));
$city      = mysqli_real_escape_string($conn, trim($_POST['city']));
$state     = mysqli_real_escape_string($conn, trim($_POST['state']));
$country   = mysqli_real_escape_string($conn, trim($_POST['country']));
$pincode   = mysqli_real_escape_string($conn, trim($_POST['pincode']));

$profile_image = $user['profile_image'];

if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {

    $allowed = ['jpg','jpeg','png','webp'];

    $ext = strtolower(pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION));

    if (in_array($ext, $allowed)) {

        if (!is_dir("uploads/profile")) {
            mkdir("uploads/profile", 0777, true);
        }

        $newName = uniqid() . "." . $ext;

        $uploadPath = "uploads/profile/" . $newName;

        if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $uploadPath)) {

            // Delete old image
            if (
                !empty($user['profile_image']) &&
                file_exists("uploads/profile/" . $user['profile_image'])
            ) {
                unlink("uploads/profile/" . $user['profile_image']);
            }

            $profile_image = $newName;

        } else {

            $_SESSION['error'] = "Image upload failed.";
            header("Location: my-profile.php");
            exit;

        }

    } else {

        $_SESSION['error'] = "Only JPG, JPEG, PNG and WEBP files are allowed.";
        header("Location: my-profile.php");
        exit;

    }
}

/*==============================
Update User
==============================*/

$sql = "UPDATE user SET

full_name='$full_name',
phone='$phone',
profile_image='$profile_image',
address='$address',
city='$city',
state='$state',
country='$country',
pincode='$pincode'

WHERE id='$user_id'
";

if (mysqli_query($conn, $sql)) {

    $_SESSION['success'] = "Profile updated successfully.";

} else {

    $_SESSION['error'] = "Something went wrong.";

}

header("Location: my-profile.php");
exit;

?>