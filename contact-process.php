<?php
include "config.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $full_name = mysqli_real_escape_string($conn, trim($_POST['full_name']));
    $email     = mysqli_real_escape_string($conn, trim($_POST['email']));
    $contact   = mysqli_real_escape_string($conn, trim($_POST['contact']));
    $message   = mysqli_real_escape_string($conn, trim($_POST['message']));

    $sql = "INSERT INTO contact_messages
            (full_name, email, contact, message)
            VALUES
            ('$full_name', '$email', '$contact', '$message')";

    if (mysqli_query($conn, $sql)) {

        echo '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="utf-8">
            <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        </head>
        <body>

        <script>
        Swal.fire({
            icon: "success",
            title: "Success!",
            text: "Your message has been sent successfully.",
            confirmButtonColor: "#198754"
        }).then(function () {
            window.location.href = "contact.php";
        });
        </script>

        </body>
        </html>';
        exit;

    } else {

        $error = addslashes(mysqli_error($conn));

        echo '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="utf-8">
            <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        </head>
        <body>

        <script>
        Swal.fire({
            icon: "error",
            title: "Database Error!",
            text: "' . $error . '",
            confirmButtonColor: "#dc3545"
        }).then(function () {
            window.history.back();
        });
        </script>

        </body>
        </html>';
        exit;
    }

} else {

    header("Location: contact.php");
    exit;
}