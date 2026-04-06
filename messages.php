<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';

require_admin();

$db  = get_db();
$msg = $db->query('SELECT * FROM messages ORDER BY is_read ASC, created_at DESC')->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Messages — Admin</title>
  <link rel="stylesheet" href="css/styles.css" />
  <link rel="stylesheet" href="css/admin.css" />
  <style>
    .admin-navlinks { display: flex; gap: 20px; list-style: none; align-items: center; flex-wrap: wrap; }
    tr.msg-unread { background-color: rgba(155, 27, 48, 0.06); }
    .msg-body-preview { max-width: 220px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; font-size: 0.85rem; color: #555; }
  </style>
</head>
<body class="admin-page">

  <nav class="navbar">
    <a href="index.php" class="navbar__logo">Ruby Suresh Dental</a>
    <ul class="navbar__links admin-navlinks">
      <li><a href="admin.php">Dashboard</a></li>
      <li><a href="appointments.php">Appointments</a></li>
      <li><a href="messages.php" aria-current="page">Messages</a></li>
      <li><a href="index.php">Website</a></li>
    </ul>
    <div class="navbar__cta">
      <a href="actions/logout.php" class="btn-secondary" style="padding:10px 20px;font-size:0.85rem;">Log Out</a>
    </div>
  </nav>

  <main class="admin-dashboard">
    <h1 class="admin-panel__title" style="margin-bottom:16px;">Messages</h1>

    <section class="admin-panel">
      <div class="admin-table-wrap">
        <table class="admin-table">
          <thead>
            <tr>
              <th>Name</th>
              <th>Email</th>
              <th>Subject</th>
              <th>Priority</th>
              <th>Date</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($msg as $m): ?>
              <?php
                $unread = (int) $m['is_read'] === 0;
                $mailto = 'mailto:' . rawurlencode($m['email']) . '?subject=Re:%20' . rawurlencode((string) ($m['subject'] ?? ''));
              ?>
              <tr class="<?= $unread ? 'msg-unread' : '' ?>">
                <td><?= h($m['name']) ?></td>
                <td><?= h($m['email']) ?></td>
                <td>
                  <div><?= h((string) ($m['subject'] ?? '')) ?></div>
                  <div class="msg-body-preview" title="<?= h($m['body']) ?>"><?= h($m['body']) ?></div>
                </td>
                <td><span class="<?= h(message_priority_tag_class($m['priority'])) ?>"><?= h($m['priority']) ?></span></td>
                <td><?= h(date('M j, Y g:i A', strtotime($m['created_at']))) ?></td>
                <td><?= $unread ? '<span class="tag admin-tag--pending">Unread</span>' : '<span class="tag admin-tag--confirmed">Read</span>' ?></td>
                <td>
                  <div class="admin-actions" style="flex-direction:column;align-items:flex-start;">
                    <?php if ($unread): ?>
                      <form method="post" action="actions/mark_read.php">
                        <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>" />
                        <input type="hidden" name="message_id" value="<?= h((string) $m['message_id']) ?>" />
                        <button type="submit" class="admin-action" style="text-decoration:none;">Mark Read</button>
                      </form>
                    <?php endif; ?>
                    <a class="admin-action" href="<?= h($mailto) ?>" style="text-decoration:underline;">Reply</a>
                    <form method="post" action="actions/delete_message.php" onsubmit="return confirm('Delete this message permanently?');">
                      <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>" />
                      <input type="hidden" name="message_id" value="<?= h((string) $m['message_id']) ?>" />
                      <button type="submit" class="admin-action admin-action--decline" style="text-decoration:none;">Delete</button>
                    </form>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
            <?php if (count($msg) === 0): ?>
              <tr><td colspan="7">No messages.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </section>
  </main>
</body>
</html>
