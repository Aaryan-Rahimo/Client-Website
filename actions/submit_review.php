<?php

/*
 * Author: Angad and Inderbir
 * Date Created: 2026-04-01
 * Description: Submit review actions for the clinic website.
 */

declare(strict_types=1);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die('Method not allowed.');
}

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';

verify_csrf();

$name   = request_post_string('name');
$body   = request_post_string('body');
$rating = request_post_int('rating');

if ($name === '' || $body === '' || $rating < 1 || $rating > 5) {
    header('Location: ../index.php?error=review#reviews');
    exit;
}

$db = get_db();
ensure_reviews_table($db);
$stmt = $db->prepare('INSERT INTO reviews (name, rating, body) VALUES (?, ?, ?)');
$stmt->execute([$name, $rating, $body]);

header('Location: ../index.php?success=review#reviews');
exit;
