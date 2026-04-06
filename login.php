<?php

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
  <title>Staff Login — Ruby Suresh Dental</title>
  <link rel="stylesheet" href="css/styles.css" />
  <style>
    .login-wrap { min-height: 70vh; display: flex; align-items: center; justify-content: center; padding: 40px 24px; background: #FDF0F0; }
    .login-card { background: #fff; padding: 40px; border-radius: 8px; border-top: 3px solid #9B1B30; box-shadow: 0 2px 12px rgba(0,0,0,0.08); width: 100%; max-width: 400px; }
    .login-card h1 { color: #9B1B30; font-size: 1.5rem; margin-bottom: 8px; }
    .login-card p { color: #555; margin-bottom: 24px; font-size: 0.95rem; }
    .login-error { background: #fdecea; color: #9B1B30; padding: 10px 14px; border-radius: 8px; margin-bottom: 16px; font-size: 0.9rem; }
    .login-card label { display: block; font-size: 0.75rem; font-weight: 500; color: #666; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 6px; }
    .login-card input { width: 100%; margin-bottom: 16px; }
    .login-card button.btn-primary { width: 100%; text-align: center; }
  </style>
</head>
<body>
  <div class="login-wrap">
    <div class="login-card">
      <h1>Staff Login</h1>
      <p>Ruby Suresh Dental Clinic</p>
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
      <p style="margin-top:20px;"><a href="index.php" style="color:#29ABE2;">← Back to website</a></p>
    </div>
  </div>
</body>
</html>
