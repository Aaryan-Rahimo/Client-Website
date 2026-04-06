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

$id       = (int) ($_POST['appointment_id'] ?? 0);
$new_date = trim((string) ($_POST['new_date'] ?? ''));
$new_time = trim((string) ($_POST['new_time'] ?? ''));

if ($id <= 0 || $new_date === '' || $new_time === '') {
    header('Location: ../appointments.php?error=missing_fields');
    exit;
}

$db = get_db();

$countStmt = $db->prepare(
    'SELECT COUNT(*) FROM appointments
     WHERE date = ? AND time_start = ? AND appointment_id != ? AND status NOT IN (\'Declined\', \'Checked In\')'
);
$countStmt->execute([$new_date, $new_time, $id]);
if ((int) $countStmt->fetchColumn() > 0) {
    header('Location: ../appointments.php?error=time_taken');
    exit;
}

$sel = $db->prepare('SELECT * FROM appointments WHERE appointment_id = ?');
$sel->execute([$id]);
$row = $sel->fetch();

if (!$row) {
    header('Location: ../appointments.php');
    exit;
}

$upd = $db->prepare(
    'UPDATE appointments SET date = ?, time_start = ?, status = \'Rescheduled\' WHERE appointment_id = ?'
);
$upd->execute([$new_date, $new_time, $id]);

$name  = $row['patient_name'];
$email = $row['patient_email'];
$pd    = format_date_long($new_date);
$pt    = format_time_ampm($new_time);

$body = "Hi {$name},\n\n"
    . "Your appointment at Ruby's Dental Clinic has been rescheduled to {$pd} at {$pt}. "
    . "Please contact us if this time does not work for you.\n\n"
    . "Best regards,\nRuby's Dental Clinic\n📞 905-000-0000";

send_email(
    $email,
    "Appointment Rescheduled — Ruby's Dental Clinic",
    $body
);

header('Location: ../appointments.php?success=1');
exit;
