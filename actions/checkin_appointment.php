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

$db = get_db();
$stmt = $db->prepare('UPDATE appointments SET status = \'Checked In\' WHERE appointment_id = ?');
$stmt->execute([$id]);

header('Location: ../admin.php');
exit;
