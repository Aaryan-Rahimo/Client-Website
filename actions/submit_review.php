<?php

declare(strict_types=1);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die('Method not allowed.');
}

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';

verify_csrf();

$name   = trim((string) ($_POST['name'] ?? ''));
$body   = trim((string) ($_POST['body'] ?? ''));
$rating = isset($_POST['rating']) ? (int) $_POST['rating'] : 0;

if ($name === '' || $body === '' || $rating < 1 || $rating > 5) {
    header('Location: ../index.php?error=review#services');
    exit;
}

$db = get_db();
ensure_reviews_table($db);
$stmt = $db->prepare('INSERT INTO reviews (name, rating, body) VALUES (?, ?, ?)');
$stmt->execute([$name, $rating, $body]);

header('Location: ../index.php?success=review#services');
exit;
