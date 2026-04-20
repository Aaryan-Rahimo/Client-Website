<?php

/*
 * Author: Angad
 * Date Created: 2026-04-13
 * Description: Appointment check-in action for the clinic website.
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

$id = (int) ($_POST['appointment_id'] ?? 0);
$returnTo = trim((string) ($_POST['return_to'] ?? 'admin'));
$target = ($returnTo === 'appointments') ? '../appointments.php' : '../admin.php';
if ($id <= 0) {
    header('Location: ' . $target);
    exit;
}

$db = get_db();

$sel = $db->prepare('SELECT date, time_start FROM appointments WHERE appointment_id = ?');
$sel->execute([$id]);
$row = $sel->fetch();

if (!$row || !appointment_can_check_in((string) $row['date'], (string) $row['time_start'])) {
    header('Location: ' . $target . '?error=checkin_early');
    exit;
}

$stmt = $db->prepare('UPDATE appointments SET status = \'Checked In\' WHERE appointment_id = ?');
$stmt->execute([$id]);

header('Location: ' . $target);
exit;
