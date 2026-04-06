<?php

declare(strict_types=1);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die('Method not allowed.');
}

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';

verify_csrf();

$name    = trim((string) ($_POST['name'] ?? ''));
$email   = trim((string) ($_POST['email'] ?? ''));
$subject = trim((string) ($_POST['subject'] ?? ''));
$body    = trim((string) ($_POST['body'] ?? ''));

if ($name === '' || $email === '' || $body === '') {
    header('Location: ../index.php?error=msg_missing#appointment');
    exit;
}

$priority = detect_message_priority($subject, $body);

$db = get_db();
$stmt = $db->prepare(
    'INSERT INTO messages (name, email, subject, body, priority, is_read)
     VALUES (?, ?, ?, ?, ?, 0)'
);
$stmt->execute([$name, $email, $subject !== '' ? $subject : null, $body, $priority]);

$subjLine = $subject !== '' ? $subject : '(no subject)';
$adminBody = "New contact form message.\n\n"
    . "Name: {$name}\n"
    . "Email: {$email}\n"
    . "Subject: {$subjLine}\n\n"
    . "Message:\n{$body}\n\n"
    . "Best regards,\nRuby's Dental Clinic\n📞 905-000-0000";

send_email(
    'ruby@clinic.com',
    '[Clinic Contact] ' . $subjLine,
    $adminBody,
    $email
);

header('Location: ../index.php?success=msg#appointment');
exit;
