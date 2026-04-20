<?php

/*
 * Author: Angad
 * Date Created: 2026-04-14
 * Description: Appointment rescheduling action for the clinic website, allowing admins to change the date and time of existing appointments while ensuring no scheduling conflicts occur.
 */

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

$id       = request_post_int('appointment_id');
$new_date = request_post_string('new_date');
$new_time = request_post_string('new_time');
$returnTo = request_post_string('return_to');
$returnTo = $returnTo !== '' ? $returnTo : 'appointments';
$target = ($returnTo === 'admin') ? '../admin.php' : '../appointments.php';

if ($id <= 0 || $new_date === '' || $new_time === '') {
    header('Location: ' . $target . '?error=missing_fields');
    exit;
}

if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $new_date) !== 1) {
    header('Location: ' . $target . '?error=missing_fields');
    exit;
}

$today = date('Y-m-d');
if ($new_date < $today) {
    header('Location: ' . $target . '?error=past_date');
    exit;
}

$db = get_db();

$countStmt = $db->prepare(
    'SELECT COUNT(*) FROM appointments
     WHERE date = ? AND time_start = ? AND appointment_id != ? AND status NOT IN (\'Declined\', \'Checked In\')'
);
$countStmt->execute([$new_date, $new_time, $id]);
if ((int) $countStmt->fetchColumn() > 0) {
    header('Location: ' . $target . '?error=time_taken');
    exit;
}

$sel = $db->prepare('SELECT * FROM appointments WHERE appointment_id = ?');
$sel->execute([$id]);
$row = $sel->fetch();

if (!$row) {
    header('Location: ' . $target);
    exit;
}

$upd = $db->prepare(
    'UPDATE appointments SET date = ?, time_start = ?, status = \'Confirmed\' WHERE appointment_id = ?'
);
$upd->execute([$new_date, $new_time, $id]);

header('Location: ' . $target . '?success=1');
exit;
