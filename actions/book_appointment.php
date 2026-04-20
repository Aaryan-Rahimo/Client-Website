<?php

/*
 * Author: Kissan
 * Date Created: 2026-04-12
 * Description: Appointment booking action for the clinic website.
 */

declare(strict_types=1);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die('Method not allowed.');
}

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

verify_csrf();

$patient_name  = request_post_string('patient_name');
$patient_email = request_post_string('patient_email');
$patient_phone = request_post_string('patient_phone');
$date          = request_post_string('date');
$time_start    = request_post_string('time_start');
$type          = request_post_string('type');
$notes         = request_post_string('notes');

$_SESSION['book_old'] = [
    'patient_name' => $patient_name,
    'patient_email' => $patient_email,
    'patient_phone' => $patient_phone,
    'date' => $date,
    'time_start' => $time_start,
    'type' => $type,
    'notes' => $notes,
];

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

unset($_SESSION['book_old']);

header('Location: ../index.php?success=1#appointment');
exit;
