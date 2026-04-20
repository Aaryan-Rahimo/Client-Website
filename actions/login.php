<?php

/*
 * Author: Aaryan and Angad
 * Date Created: 2026-04-05
 * Description: Login actions for the clinic website, handling user authentication by verifying credentials against the database and managing session state to maintain user login status across pages.
 */

declare(strict_types=1);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die('Method not allowed.');
}

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';

verify_csrf();

$email = trim((string) ($_POST['email'] ?? ''));
$pass  = (string) ($_POST['password'] ?? '');

if ($email === '' || $pass === '') {
    header('Location: ../login.php?error=1');
    exit;
}

$db   = get_db();
ensure_default_admin_user($db);
$stmt = $db->prepare('SELECT * FROM users WHERE email = ?');
$stmt->execute([$email]);
$row = $stmt->fetch();

if (!$row || !password_verify($pass, $row['password'])) {
    header('Location: ../login.php?error=1');
    exit;
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
session_regenerate_id(true);
$_SESSION['user_id'] = (int) $row['user_id'];
$_SESSION['role']   = $row['role'];
$_SESSION['name']   = $row['name'];

if ($row['role'] === 'admin') {
    header('Location: ../admin.php');
} else {
    header('Location: ../index.php');
}
exit;
