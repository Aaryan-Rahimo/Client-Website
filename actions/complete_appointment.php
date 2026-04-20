<?php

/*
 * Author: Angad and Inderbir
 * Date Created: 2026-04-13
 * Description: Appointment completion action for the clinic website.
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
$notes = request_post_string('notes');
$returnTo = request_post_string('return_to');
$returnTo = $returnTo !== '' ? $returnTo : 'appointments';

if ($id <= 0) {
    header('Location: ../appointments.php');
    exit;
}

$db = get_db();

$sel = $db->prepare('SELECT status, notes FROM appointments WHERE appointment_id = ?');
$sel->execute([$id]);
$row = $sel->fetch();

if (!$row || $row['status'] !== 'Checked In') {
    $target = ($returnTo === 'admin') ? '../admin.php' : '../appointments.php';
    header('Location: ' . $target);
    exit;
}

$existingNotes = trim((string) ($row['notes'] ?? ''));
$finalNotes = $existingNotes;
if ($notes !== '') {
    $stamp = date('Y-m-d H:i');
    $entry = '[Completed ' . $stamp . '] ' . $notes;
    $finalNotes = ($existingNotes === '') ? $entry : ($existingNotes . "\n" . $entry);
}

$upd = $db->prepare("UPDATE appointments SET status = 'Completed', notes = ? WHERE appointment_id = ?");
$upd->execute([$finalNotes, $id]);

if ($returnTo === 'admin') {
    header('Location: ../admin.php?success=completed');
    exit;
}

header('Location: ../appointments.php?success=completed');
exit;
