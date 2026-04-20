<?php

/*
 * Author: Kissan and Aaryan
 * Date Created: 2026-04-01
 * Description: Handles the display and management of messages sent by patients through the contact form on the clinic website.
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';

require_admin();

$db = get_db();

$filter = trim((string) ($_GET['filter'] ?? 'all'));
if ($filter === 'unread') {
  $stmt = $db->prepare('SELECT * FROM messages WHERE is_read = 0 ORDER BY created_at DESC');
  $stmt->execute();
  $msg = $stmt->fetchAll();
} else {
  $msg = $db->query('SELECT * FROM messages ORDER BY is_read ASC, created_at DESC')->fetchAll();
}
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
    .msg-modal[hidden] { display: none; }
    .msg-modal {
      position: fixed;
      inset: 0;
      z-index: 3000;
      display: grid;
      place-items: center;
      padding: 20px;
      background: rgba(0, 0, 0, 0.45);
    }
    .msg-modal__card {
      width: min(100%, 760px);
      max-height: min(88vh, 760px);
      overflow: auto;
      background: #fff;
      border-radius: 12px;
      border: 1px solid rgba(155, 27, 48, 0.12);
      box-shadow: 0 14px 40px rgba(0, 0, 0, 0.22);
      padding: 20px;
    }
    .msg-modal__head {
      display: flex;
      align-items: flex-start;
      justify-content: space-between;
      gap: 12px;
      margin-bottom: 14px;
    }
    .msg-modal__title {
      font-size: 1.1rem;
      font-weight: 700;
      color: #9b1b30;
      line-height: 1.25;
      margin: 0;
    }
    .msg-modal__close {
      border: 1px solid #ddd;
      background: #fff;
      border-radius: 8px;
      padding: 6px 10px;
      cursor: pointer;
      font-size: 0.9rem;
      line-height: 1;
    }
    .msg-modal__meta {
      display: grid;
      grid-template-columns: 64px 1fr;
      gap: 6px 10px;
      font-size: 0.92rem;
      margin-bottom: 12px;
    }
    .msg-modal__meta strong { color: #1a3a5c; }
    .msg-modal__body {
      border: 1px solid #eee;
      border-radius: 10px;
      background: #fafafa;
      padding: 12px;
      white-space: pre-wrap;
      word-break: break-word;
      font-size: 0.95rem;
      color: #333;
      line-height: 1.55;
    }
    @media (max-width: 768px) {
      .msg-modal { padding: 12px; }
      .msg-modal__card { padding: 14px; }
    }
  </style>
</head>
<body class="admin-page">

  <nav class="navbar">
    <a href="index.php" class="navbar__logo">Dr. Ruby M. Suresh</a>
    <ul class="navbar__links admin-navlinks">
      <li><a href="index.php">Home</a></li>
      <li><a href="admin.php">Dashboard</a></li>
      <li><a href="appointments.php">Appointments</a></li>
      <li><a href="patients.php">Patients</a></li>
      <li><a href="messages.php" aria-current="page">Messages</a></li>
    </ul>
    <div class="navbar__cta">
      <a href="actions/logout.php" class="btn-secondary" style="padding:10px 20px;font-size:0.85rem;">Log Out</a>
    </div>
  </nav>

  <main class="admin-dashboard">
    <h1 class="admin-panel__title" style="margin-bottom:16px;">Messages</h1>
    <?php if ($filter === 'unread'): ?>
      <div class="flash flash--ok" style="margin-bottom:16px;background:rgba(41,171,226,0.15);color:#156a94;">Showing unread messages only.</div>
    <?php endif; ?>

    <section class="admin-panel">
      <div class="admin-table-wrap">
        <table class="admin-table">
          <thead>
            <tr>
              <th>Name</th>
              <th>Email</th>
              <th>Subject</th>
              <th>Date</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($msg as $m): ?>
              <?php
                $unread = (int) $m['is_read'] === 0;
              ?>
              <tr class="<?= $unread ? 'msg-unread' : '' ?>">
                <td><?= h($m['name']) ?></td>
                <td><?= h($m['email']) ?></td>
                <td>
                  <div><?= h((string) ($m['subject'] ?? '')) ?></div>
                  <div class="msg-body-preview" title="<?= h($m['body']) ?>"><?= h($m['body']) ?></div>
                </td>
                <td><?= h(date('M j, Y g:i A', strtotime($m['created_at']))) ?></td>
                <td><?= $unread ? '<span class="tag admin-tag--pending">Unread</span>' : '<span class="tag admin-tag--confirmed">Read</span>' ?></td>
                <td>
                  <div class="admin-actions" style="flex-direction:column;align-items:flex-start;">
                    <button
                      type="button"
                      class="admin-action js-view-message"
                      style="text-decoration:none;"
                      data-msg-name="<?= h($m['name']) ?>"
                      data-msg-email="<?= h($m['email']) ?>"
                      data-msg-subject="<?= h((string) ($m['subject'] ?? '')) ?>"
                      data-msg-date="<?= h(date('M j, Y g:i A', strtotime($m['created_at']))) ?>"
                      data-msg-body="<?= h($m['body']) ?>"
                    >
                      View Full
                    </button>
                    <?php if ($unread): ?>
                      <form method="post" action="actions/mark_read.php">
                        <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>" />
                        <input type="hidden" name="message_id" value="<?= h((string) $m['message_id']) ?>" />
                        <button type="submit" class="admin-action" style="text-decoration:none;">Mark Read</button>
                      </form>
                    <?php endif; ?>
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
              <tr><td colspan="6">No messages.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </section>
  </main>

  <div class="msg-modal" id="msg-modal" hidden>
    <div class="msg-modal__card" role="dialog" aria-modal="true" aria-labelledby="msg-modal-title">
      <div class="msg-modal__head">
        <h2 class="msg-modal__title" id="msg-modal-title">Message</h2>
        <button type="button" class="msg-modal__close" id="msg-modal-close" aria-label="Close full message view">Close</button>
      </div>
      <div class="msg-modal__meta">
        <strong>From</strong><span id="msg-modal-from">-</span>
        <strong>Email</strong><span id="msg-modal-email">-</span>
        <strong>Sent</strong><span id="msg-modal-date">-</span>
      </div>
      <div class="msg-modal__body" id="msg-modal-body">-</div>
    </div>
  </div>

  <script src="js/messages-admin.js"></script>
</body>
</html>
