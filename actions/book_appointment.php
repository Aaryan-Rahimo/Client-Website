<?php

declare(strict_types=1);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die('Method not allowed.');
}

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';

verify_csrf();

$patient_name  = trim((string) ($_POST['patient_name'] ?? ''));
$patient_email = trim((string) ($_POST['patient_email'] ?? ''));
$patient_phone = trim((string) ($_POST['patient_phone'] ?? ''));
$date          = trim((string) ($_POST['date'] ?? ''));
$time_start    = trim((string) ($_POST['time_start'] ?? ''));
$type          = trim((string) ($_POST['type'] ?? ''));
$notes         = trim((string) ($_POST['notes'] ?? ''));

if ($patient_name === '' || $patient_email === '' || $date === '' || $time_start === '' || $type === '') {
    header('Location: ../index.php?error=missing_fields#appointment');
    exit;
}

$db = get_db();

$stmt = $db->prepare(
    'SELECT COUNT(*) FROM appointments
     WHERE date = ? AND time_start = ? AND status NOT IN (\'Declined\', \'Checked In\')'
);
$stmt->execute([$date, $time_start]);
if ((int) $stmt->fetchColumn() > 0) {
    header('Location: ../index.php?error=time_taken#appointment');
    exit;
}

$ins = $db->prepare(
    'INSERT INTO appointments (patient_name, patient_email, patient_phone, date, time_start, type, status, notes)
     VALUES (?, ?, ?, ?, ?, ?, \'Pending\', ?)'
);
$ins->execute([
    $patient_name,
    $patient_email,
    $patient_phone !== '' ? $patient_phone : null,
    $date,
    $time_start,
    $type,
    $notes !== '' ? $notes : null,
]);

$prettyTime = format_time_ampm($time_start);
$prettyDate = format_date_long($date);
$body       = "Hi {$patient_name},\n\n"
    . "Thank you for requesting an appointment at Ruby's Dental Clinic. We have received your request for {$prettyDate} at {$prettyTime} ({$type}). Dr. Ruby will confirm shortly.\n\n"
    . "Best regards,\nRuby's Dental Clinic\n📞 905-000-0000";

send_email(
    $patient_email,
    "Appointment Request Received — Ruby's Dental Clinic",
    $body
);

header('Location: ../index.php?success=1#appointment');
exit;
