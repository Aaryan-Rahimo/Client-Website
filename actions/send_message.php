<?php

/*
 * Author: Aaryan
 * Date Created: 2026-04-18
 * Description: Send message actions for the clinic website.
 */

declare(strict_types=1);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die('Method not allowed.');
}

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';

verify_csrf();

$name    = request_post_string('name');
$email   = request_post_string('email');
$subject = request_post_string('subject');
$body    = request_post_string('body');

$name    = str_replace(["\r", "\n"], '', $name);
$email   = str_replace(["\r", "\n"], '', $email);
$subject = str_replace(["\r", "\n"], '', $subject);

if ($name === '' || $email === '' || $body === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: ../index.php?error=msg_missing#appointment');
    exit;
}



$priority = detect_message_priority($subject, $body);

$db = get_db();

$db->exec("
CREATE TABLE IF NOT EXISTS messages (
  message_id INT AUTO_INCREMENT PRIMARY KEY,
  name       VARCHAR(255) NOT NULL,
  email      VARCHAR(255) NOT NULL,
  subject    VARCHAR(255) DEFAULT NULL,
  body       TEXT NOT NULL,
  priority   ENUM('High', 'Medium', 'Low') DEFAULT 'Low',
  is_read    TINYINT(1) DEFAULT 0,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
");

$stmt = $db->prepare(
    'INSERT INTO messages (name, email, subject, body, priority, is_read)
     VALUES (?, ?, ?, ?, ?, 0)'
);
$dbOk = $stmt->execute([$name, $email, $subject !== '' ? $subject : null, $body, $priority]);

/* ──────────────────────────────────────────────
   Send email notification via PHPMailer 
   ────────────────────────────────────────────── */

require_once __DIR__ . '/../PHPMailer-master/PHPMailer-master/src/PHPMailer.php';
require_once __DIR__ . '/../PHPMailer-master/PHPMailer-master/src/SMTP.php';
require_once __DIR__ . '/../PHPMailer-master/PHPMailer-master/src/Exception.php';

$emailOk = false;

try {
  $smtpUser = trim((string) (getenv('MAIL_USERNAME') ?: ''));
  $smtpPass = trim((string) (getenv('MAIL_PASSWORD') ?: ''));
  $mailFrom = trim((string) (getenv('MAIL_FROM') ?: 'ruby@clinic.com'));
  $mailFromName = trim((string) (getenv('MAIL_FROM_NAME') ?: 'Ephesians Dental'));
  $mailTo = trim((string) (getenv('MAIL_TO') ?: ''));
  $mailToName = trim((string) (getenv('MAIL_TO_NAME') ?: 'Clinic Admin'));

  // Keep the app functional without exposing secrets in source code.
  if ($smtpUser === '' || $smtpPass === '' || $mailTo === '') {
    throw new RuntimeException('Mail environment variables are not configured.');
  }

  $mail = new PHPMailer(true);

    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
  $mail->Username   = $smtpUser;
  $mail->Password   = $smtpPass;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    /* ── Sender / recipient ── */
  $mail->setFrom($mailFrom, $mailFromName);
  $mail->addAddress($mailTo, $mailToName);
  $mail->addReplyTo($email, $name);

    $mail->isHTML(true);
    $mail->CharSet = 'UTF-8';

    $subjLine     = $subject !== '' ? htmlspecialchars($subject, ENT_QUOTES, 'UTF-8') : '(no subject)';
    $mail->Subject = '[Ephesians Dental] New Contact: ' . ($subject !== '' ? $subject : '(no subject)');

    $safeName    = htmlspecialchars($name,    ENT_QUOTES, 'UTF-8');
    $safeEmail   = htmlspecialchars($email,   ENT_QUOTES, 'UTF-8');
    $safeSubject = $subjLine;
    $safeBody    = nl2br(htmlspecialchars($body, ENT_QUOTES, 'UTF-8'));

    $mail->Body = <<<HTML
    <!DOCTYPE html>
    <html>
    <head>
      <meta charset="UTF-8">
      <style>
        body { margin: 0; padding: 0; font-family: 'Montserrat', 'Segoe UI', Arial, Helvetica, sans-serif; background-color: #f5f0f0; }
        .wrapper { max-width: 600px; margin: 40px auto; background: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 8px 30px rgba(0,0,0,0.08); }
        .header { background: #9B1B30; padding: 28px 32px; }
        .header h1 { margin: 0; color: #ffffff; font-size: 22px; font-weight: 600; }
        .header p { margin: 6px 0 0; color: rgba(255,255,255,0.8); font-size: 14px; }
        .content { padding: 32px; }
        .field { margin-bottom: 20px; }
        .field-label { font-size: 12px; font-weight: 600; color: #9B1B30; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 6px; }
        .field-value { font-size: 15px; color: #333; line-height: 1.6; }
        .message-box { background: #FDF0F0; border-left: 4px solid #9B1B30; padding: 16px 20px; border-radius: 0 8px 8px 0; margin-top: 8px; }
        .footer { text-align: center; padding: 20px 32px; border-top: 1px solid #f0e0e0; color: #999; font-size: 12px; }
        .footer a { color: #9B1B30; text-decoration: none; }
      </style>
    </head>
    <body>
      <div class="wrapper">
        <div class="header">
          <h1>📩 New Client/Patient Message</h1>
          <p>A client/patient has sent a message from the website!</p>
        </div>
        <div class="content">
          <div class="field">
            <div class="field-label">Name</div>
            <div class="field-value">{$safeName}</div>
          </div>
          <div class="field">
            <div class="field-label">Email</div>
            <div class="field-value"><a href="mailto:{$safeEmail}" style="color:#9B1B30;">{$safeEmail}</a></div>
          </div>
          <div class="field">
            <div class="field-label">Subject</div>
            <div class="field-value">{$safeSubject}</div>
          </div>
          <div class="field">
            <div class="field-label">Message</div>
            <div class="message-box">{$safeBody}</div>
          </div>
        </div>
        <div class="footer">
          <p>&copy; 2026 <a href="#">Ephesians Dental</a> &middot; All rights reserved</p>
        </div>
      </div>
    </body>
    </html>
    HTML;

    // Plain-text fallback for email clients that don't render HTML
    $mail->AltBody = "New Contact Form Submission\n\n"
        . "Name: {$name}\n"
        . "Email: {$email}\n"
        . "Subject: " . ($subject !== '' ? $subject : '(no subject)') . "\n\n"
        . "Message:\n{$body}\n\n"
        . "— Ephesians Dental";

    $mail->send();
    $emailOk = true;

} catch (Exception $e) {
    // Log the error but don't crash — the DB insert already succeeded
    error_log('[Ephesians Dental] PHPMailer error: ' . $mail->ErrorInfo);
    $emailOk = false;
}

/* ──────────────────────────────────────────────
   4. Redirect with appropriate feedback
   ────────────────────────────────────────────── */

if ($dbOk && $emailOk) {
    // Both database save and email succeeded
    header('Location: ../index.php?success=msg#appointment');
} elseif ($dbOk) {
    // Database saved but email failed — still show success to user
    // (the admin can see the message in the dashboard regardless)
    header('Location: ../index.php?success=msg#appointment');
} else {
    // Database insert failed
    header('Location: ../index.php?error=msg_missing#appointment');
}
exit;
