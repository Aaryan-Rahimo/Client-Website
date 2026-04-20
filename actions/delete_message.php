<?php

/*
 * Author: Team Kissan
 * Date Created: 2026-04-14
 * Description: Message deletion action for the clinic website.
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

$id = (int) ($_POST['message_id'] ?? 0);
if ($id <= 0) {
    header('Location: ../messages.php');
    exit;
}

$db = get_db();
$stmt = $db->prepare('DELETE FROM messages WHERE message_id = ?');
$stmt->execute([$id]);

header('Location: ../messages.php');
exit;
