<?php

/*
 * Author: Inderbir
 * Date Created: 2026-04-12
 * Description: Appointment approval action for the clinic web application.
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

$id = request_post_int('appointment_id');
$returnTo = request_post_string('return_to');
$returnTo = $returnTo !== '' ? $returnTo : 'admin';
$target = ($returnTo === 'appointments') ? '../appointments.php' : '../admin.php';
if ($id <= 0) {
    header('Location: ' . $target);
    exit;
}

$db   = get_db();
$stmt = $db->prepare('SELECT * FROM appointments WHERE appointment_id = ?');
$stmt->execute([$id]);
$row = $stmt->fetch();

if (!$row) {
    header('Location: ' . $target);
    exit;
}

$upd = $db->prepare('UPDATE appointments SET status = \'Confirmed\' WHERE appointment_id = ?');
$upd->execute([$id]);

header('Location: ' . $target);
exit;
