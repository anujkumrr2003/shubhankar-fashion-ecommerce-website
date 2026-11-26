<?php

include "config.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require "phpMailer/Exception.php";
require "phpMailer/PHPMailer.php";
require "phpMailer/SMTP.php";

if ($_SERVER['REQUEST_METHOD'] == "POST") {

    $id     = (int)$_POST['id'];
    $status = mysqli_real_escape_string($conn, $_POST['status']);

    $sql = "UPDATE b2b_inquiries SET status='$status' WHERE id='$id'";

    if (mysqli_query($conn, $sql)) {

        // Customer Details
        $result = mysqli_query($conn, "SELECT * FROM b2b_inquiries WHERE id='$id' LIMIT 1");
        $row = mysqli_fetch_assoc($result);

        /* =========================================================
           INSTANT SUCCESS REDIRECT
           Redirect the admin immediately. Email sending keeps
           running on the server after the connection is closed.
           Reliable background execution requires PHP-FPM.
           ========================================================= */

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }
        ignore_user_abort(true);
        set_time_limit(0);

        header("Location: b2b-enquiry.php?msg=updated");

        if (function_exists('fastcgi_finish_request')) {
            // Best case: PHP-FPM. Admin's browser redirects now,
            // PHP keeps executing below in the background.
            fastcgi_finish_request();
        } else {
            // Fallback for mod_php / CLI servers: best effort flush.
            while (ob_get_level() > 0) {
                ob_end_flush();
            }
            flush();
        }

        /* =========================================================
           EMAIL SENDING (runs after the redirect has been sent)
           ========================================================= */

        // Color-code the status badge in the email
        $status_lower = strtolower($status);
        if (strpos($status_lower, 'approve') !== false || strpos($status_lower, 'accept') !== false || strpos($status_lower, 'confirm') !== false) {
            $status_bg     = '#e6f7ec';
            $status_border = '#b7e6c9';
            $status_color  = '#1e7e42';
        } elseif (strpos($status_lower, 'reject') !== false || strpos($status_lower, 'cancel') !== false || strpos($status_lower, 'decline') !== false) {
            $status_bg     = '#fdecec';
            $status_border = '#f5c2c2';
            $status_color  = '#b02a2a';
        } else {
            $status_bg     = '#fff8e6';
            $status_border = '#ffe8b3';
            $status_color  = '#8a6d1f';
        }

        $mail = new PHPMailer(true);

        try {

            // $mail->isSMTP();
            // $mail->Host = 'smtp.gmail.com';
            // $mail->SMTPAuth = true;
            // $mail->Username = 'vipanujkumarlmp@gmail.com';
            // $mail->Password = 'xwju bgei lyfm gghg'; // Gmail App Password
            // $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            // $mail->Port = 587;
            // $mail->setFrom('vipanujkumarlmp@gmail.com', 'Your Company');
            
            $mail->isSMTP();
$mail->Host = 'smtp.hostinger.com';
$mail->SMTPAuth = true;
$mail->Username = 'info@shubhankarfashion.com';
$mail->Password = 'Sunchit@11';
$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
$mail->Port = 587;
$mail->setFrom('info@shubhankarfashion.com', 'Shubhankar Fashion');
            
            $mail->addAddress($row['email'], $row['contact_person']);

            $mail->isHTML(true);
            $mail->Subject = "B2B Inquiry Status Updated";

            $mail->Body = "
            <div style='background-color:#f0f2f5;padding:30px 0;font-family:Segoe UI, Roboto, Helvetica, Arial, sans-serif;'>
              <table role='presentation' width='100%' cellpadding='0' cellspacing='0'>
                <tr>
                  <td align='center'>
                    <table role='presentation' width='600' cellpadding='0' cellspacing='0' style='background-color:#ffffff;border-radius:10px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.06);'>

                      <!-- Header -->
                      <tr>
                        <td style='background-color:#1a3d7c;background:linear-gradient(135deg,#1a3d7c,#2e5aa8);padding:28px 30px;text-align:center;'>
                          <h1 style='margin:0;color:#ffffff;font-size:22px;letter-spacing:0.5px;'>Status Update</h1>
                          <p style='margin:6px 0 0;color:#d6e0f5;font-size:13px;'>B2B Inquiry Notification</p>
                        </td>
                      </tr>

                      <!-- Greeting -->
                      <tr>
                        <td style='padding:28px 30px 10px;'>
                          <h2 style='margin:0 0 8px;color:#1a3d7c;font-size:18px;'>Hello, {$row['contact_person']}</h2>
                          <p style='margin:0;color:#555;font-size:14px;line-height:1.6;'>
                            The status of your B2B inquiry has been updated. Here are the latest details:
                          </p>
                        </td>
                      </tr>

                      <!-- Status Badge -->
                      <tr>
                        <td style='padding:16px 30px 0;'>
                          <div style='background-color:{$status_bg};border:1px solid {$status_border};border-radius:8px;padding:16px 18px;text-align:center;'>
                            <p style='margin:0 0 4px;color:#888;font-size:12px;text-transform:uppercase;letter-spacing:0.5px;'>Current Status</p>
                            <p style='margin:0;color:{$status_color};font-size:18px;font-weight:700;'>{$status}</p>
                          </div>
                        </td>
                      </tr>

                      <!-- Inquiry Details -->
                      <tr>
                        <td style='padding:22px 30px 0;'>
                          <table width='100%' cellpadding='0' cellspacing='0' style='background-color:#f8f9fc;border-radius:8px;border:1px solid #eef0f5;'>
                            <tr>
                              <td style='padding:10px 18px;color:#888;font-size:13px;width:40%;border-radius:8px 0 0 0;'>Company</td>
                              <td style='padding:10px 18px;color:#222;font-size:13px;font-weight:600;border-radius:0 8px 0 0;'>{$row['company_name']}</td>
                            </tr>
                            <tr>
                              <td style='padding:10px 18px;color:#888;font-size:13px;background-color:#ffffff;'>Contact Person</td>
                              <td style='padding:10px 18px;color:#222;font-size:13px;font-weight:600;background-color:#ffffff;'>{$row['contact_person']}</td>
                            </tr>
                            <tr>
                              <td style='padding:10px 18px;color:#888;font-size:13px;'>Email</td>
                              <td style='padding:10px 18px;color:#222;font-size:13px;font-weight:600;'>{$row['email']}</td>
                            </tr>
                            <tr>
                              <td style='padding:10px 18px;color:#888;font-size:13px;background-color:#ffffff;'>Phone</td>
                              <td style='padding:10px 18px;color:#222;font-size:13px;font-weight:600;background-color:#ffffff;'>{$row['phone']}</td>
                            </tr>
                            <tr>
                              <td style='padding:10px 18px;color:#888;font-size:13px;border-radius:0 0 0 8px;'>Reference ID</td>
                              <td style='padding:10px 18px;color:#222;font-size:13px;font-weight:600;border-radius:0 0 8px 0;'>#{$id}</td>
                            </tr>
                          </table>
                        </td>
                      </tr>

                      <!-- Footer -->
                      <tr>
                        <td style='padding:30px;text-align:center;'>
                          <p style='margin:0 0 6px;color:#333;font-size:14px;'>Thank you for choosing us!</p>
                          <p style='margin:0;color:#999;font-size:12px;'>This is an automated notification &mdash; please do not reply directly to this message.</p>
                        </td>
                      </tr>

                      <tr>
                        <td style='background-color:#1a3d7c;padding:16px;text-align:center;'>
                          <p style='margin:0;color:#c9d7f0;font-size:12px;'>&copy; " . date('Y') . " Your Company. All rights reserved.</p>
                        </td>
                      </tr>

                    </table>
                  </td>
                </tr>
              </table>
            </div>
            ";

            $mail->send();

        } catch (Exception $e) {
            // Mail failed silently — status is already updated in DB either way.
            error_log("Status update mail failed for inquiry #{$id}: " . $mail->ErrorInfo);
        }

        exit;

    } else {

        echo mysqli_error($conn);

    }
}