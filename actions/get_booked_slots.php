<?php

/*
 * Author: Team Inderbir
    * Date Created: 2026-04-15
    * Description: Get booked slots action for the clinic website.
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode([]);
    exit;
}

$date = request_get_string('date');
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
    echo json_encode([]);
    exit;
}

try {
    $db = get_db();

    $stmt = $db->prepare("SELECT time_start FROM appointments WHERE date = ? AND status != 'Declined'");
    $stmt->execute([$date]);
    $slots = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo json_encode($slots);
} catch (Exception $e) {
    echo json_encode([]);
}
