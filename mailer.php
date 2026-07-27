<?php
/*
 |--------------------------------------------------------------------
 | Simple Mailer Helper
 |--------------------------------------------------------------------
 | Uses PHP's built-in mail() function so the project works
 | out-of-the-box without any extra libraries or Composer installs.
 |
 | IMPORTANT (read this):
 | Most local servers (XAMPP/WAMP on Windows/Mac) do NOT have an
 | outgoing mail server configured, so mail() will silently fail
 | on localhost. For REAL email delivery (Gmail/SMTP) on localhost,
 | the easiest free options are:
 |   1) Install "PHPMailer" via Composer and use SMTP (Gmail App
 |      Password) - most reliable, works on localhost too.
 |   2) Use a free tool like "Mercury Mail" (bundled with some
 |      XAMPP versions) or "smtp4dev" for local testing.
 |   3) Deploy the site to a real hosting server - mail() works
 |      out of the box on almost every shared PHP host (Hostinger,
 |      000webhost, etc.)
 |
 | This file is written so that switching to PHPMailer later only
 | needs changes inside the send_mail() function below - the rest
 | of the website calls send_mail() and does not need to change.
 */

function send_mail($to, $subject, $bodyHtml) {
    $headers  = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8" . "\r\n";
    $headers .= "From: PolicyPoint Insurance <no-reply@policypoint.com>" . "\r\n";

    // mail() returns true/false - we don't stop the website flow
    // even if it fails, so the user experience is never blocked.
    $sent = @mail($to, $subject, $bodyHtml, $headers);

    // Always log every "email" to a local file too, so you can see
    // OTPs / messages during local testing even if real mail fails.
    $logLine = "[" . date("Y-m-d H:i:s") . "] TO: $to | SUBJECT: $subject | SENT: " . ($sent ? "yes" : "no") . "\n";
    $logLine .= strip_tags($bodyHtml) . "\n----------------------------------------\n";
    @file_put_contents(__DIR__ . "/../mail_log.txt", $logLine, FILE_APPEND);

    return $sent;
}
