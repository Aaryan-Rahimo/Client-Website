<?php

declare(strict_types=1);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die('Method not allowed.');
}

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';

require_admin();
verify_csrf();

$id = (int) ($_POST['appointment_id'] ?? 0);
if ($id <= 0) {
    header('Location: ../admin.php');
    exit;
}

$db   = get_db();
$stmt = $db->prepare('SELECT * FROM appointments WHERE appointment_id = ?');
$stmt->execute([$id]);
$row = $stmt->fetch();

if (!$row) {
    header('Location: ../admin.php');
    exit;
}

$upd = $db->prepare('UPDATE appointments SET status = \'Declined\' WHERE appointment_id = ?');
$upd->execute([$id]);

$prettyDate = format_date_long($row['date']);
$prettyTime = format_time_ampm($row['time_start']);
$name       = $row['patient_name'];
$email      = $row['patient_email'];

$body = "Hi {$name},\n\n"
    . "We're sorry, but we could not accommodate your appointment request for {$prettyDate} at {$prettyTime}. "
    . "Please visit our website or call Ruby's Dental Clinic to choose another time — we'd still love to see you.\n\n"
    . "Best regards,\nRuby's Dental Clinic\n📞 905-000-0000";

send_email(
    $email,
    "Appointment Update — Ruby's Dental Clinic",
    $body
);

header('Location: ../admin.php');
exit;
