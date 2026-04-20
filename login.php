<?php

/*
 * Author: Aaryan and Angad
 * Date Created: 2026-03-29
 * Description: Handles admin login functionality for the clinic website, including session management and redirection based on user roles.
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/helpers.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!empty($_SESSION['user_id']) && ($_SESSION['role'] ?? '') === 'admin') {
    header('Location: admin.php');
    exit;
}

$error = isset($_GET['error']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Staff Login — Ephesians Dental</title>
  <link rel="stylesheet" href="css/styles.css" />
  <link rel="stylesheet" href="css/admin.css" />
</head>
<body>
  <div class="login-wrap">
    <div class="login-card">
      <h1>Staff Login</h1>
      <p class="login-subtitle">Ephesians Dental Clinic</p>
      <?php if ($error): ?>
        <div class="login-error" role="alert">Invalid email or password.</div>
      <?php endif; ?>
      <form method="post" action="actions/login.php" autocomplete="on">
        <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>" />
        <label for="email">Email</label>
        <input type="email" id="email" name="email" required />
        <label for="password">Password</label>
        <input type="password" id="password" name="password" required />
        <button type="submit" class="btn-primary">Sign In</button>
      </form>
      <p class="login-back-link"><a href="index.php">← Back to website</a></p>
    </div>
  </div>
</body>
</html>
