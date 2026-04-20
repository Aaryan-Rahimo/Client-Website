<?php

/*
 * Author: Aaryan, Kissan, Inderbir, Angad
 * Date Created: 2026-04-10
 * Description: Admin page for managing appointments in the clinic web application. Allows filtering, approving, rescheduling, and declining appointments, as well as viewing appointment notes.
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';

require_admin();

function split_appointment_notes(?string $raw): array
{
  $text = trim((string) $raw);
  if ($text === '') {
    return ['patient' => '', 'doctor' => '', 'declination' => ''];
  }

  $lines = preg_split('/\r\n|\r|\n/', $text) ?: [];
  $patient = [];
  $doctor  = [];
  $declination = [];

  foreach ($lines as $line) {
    $trimmed = trim($line);
    if ($trimmed === '') {
      continue;
    }

    if (preg_match('/^\[Completed [^\]]+\]\s*(.*)$/', $trimmed, $m) === 1) {
      $doctor[] = trim((string) ($m[1] ?? ''));
      continue;
    }

    if (preg_match('/^\[Declined [^\]]+\]\s*(.*)$/', $trimmed, $m) === 1) {
      $declination[] = trim((string) ($m[1] ?? ''));
      continue;
    }

    $patient[] = $trimmed;
  }

  return [
    'patient' => implode("\n", $patient),
    'doctor' => implode("\n", $doctor),
    'declination' => implode("\n", $declination),
  ];
}

$db = get_db();

$statusFilter = trim((string) ($_GET['status'] ?? 'All'));
$dateFrom     = trim((string) ($_GET['date_from'] ?? ''));
$dateTo       = trim((string) ($_GET['date_to'] ?? ''));
$search       = trim((string) ($_GET['search'] ?? ''));
$page         = max(1, (int) ($_GET['page'] ?? 1));
$limit        = 20;
$offset       = ($page - 1) * $limit;

$conditions = ['1=1'];
$params     = [];

if ($statusFilter !== '' && $statusFilter !== 'All') {
    $conditions[] = 'status = ?';
    $params[]     = $statusFilter;
}
if ($dateFrom !== '') {
    $conditions[] = 'date >= ?';
    $params[]     = $dateFrom;
}
if ($dateTo !== '') {
    $conditions[] = 'date <= ?';
    $params[]     = $dateTo;
}
if ($search !== '') {
    $conditions[] = 'patient_name LIKE ?';
    $params[]     = '%' . $search . '%';
}

$whereSql = implode(' AND ', $conditions);

$countStmt = $db->prepare("SELECT COUNT(*) FROM appointments WHERE {$whereSql}");
$countStmt->execute($params);
$totalRows = (int) $countStmt->fetchColumn();
$totalPages = max(1, (int) ceil($totalRows / $limit));
if ($page > $totalPages) {
    $page    = $totalPages;
    $offset  = ($page - 1) * $limit;
}

$sql = "SELECT * FROM appointments WHERE {$whereSql} ORDER BY date DESC, time_start ASC LIMIT {$limit} OFFSET {$offset}";
$listStmt = $db->prepare($sql);
$listStmt->execute($params);
$rows = $listStmt->fetchAll();

$timeSlots = appointment_time_slots();

$flashSuccess = isset($_GET['success']);
$flashError   = $_GET['error'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Appointments — Admin</title>
  <link rel="stylesheet" href="css/styles.css" />
  <link rel="stylesheet" href="css/admin.css" />
  <style>
    .admin-navlinks { display: flex; gap: 20px; list-style: none; align-items: center; flex-wrap: wrap; }
    .filters { display: flex; flex-wrap: wrap; gap: 12px; align-items: flex-end; margin-bottom: 20px; padding: 16px; background: #fff; border-radius: 8px; border: 1px solid #eee; }
    .filters label { font-size: 0.75rem; font-weight: 500; color: #666; display: block; margin-bottom: 4px; }
    .filters input, .filters select { padding: 8px 10px; }
    .flash { padding: 12px 16px; border-radius: 8px; margin-bottom: 16px; }
    .flash--ok { background: rgba(52,168,83,0.15); color: #1e7e34; }
    .flash--err { background: #fdecea; color: #9B1B30; }
    .flash--declined { background: rgba(155, 27, 48, 0.12); color: #7a1526; border: 1px solid rgba(155, 27, 48, 0.25); }
    .subrow td { background: #f8f9fb; padding: 16px; }
    .subrow-hidden { display: none; }
    .pager { display: flex; justify-content: center; gap: 16px; margin-top: 20px; }
    .notes-card { background: #fff; border: 1px solid rgba(0,0,0,0.08); border-radius: 10px; padding: 12px 14px; }
    .notes-card__title { font-weight: 700; color: #9b1b30; margin-bottom: 6px; }
    .notes-card__line { margin-top: 6px; color: #333; line-height: 1.45; }
    .notes-card__value { margin-top: 2px; white-space: pre-wrap; }
    .decline-modal-backdrop { position: fixed; inset: 0; background: rgba(0,0,0,0.42); display: none; align-items: center; justify-content: center; z-index: 3000; }
    .decline-modal-backdrop.is-open { display: flex; }
    .decline-modal { width: min(420px, 92vw); background: #fff; border-radius: 14px; box-shadow: 0 14px 44px rgba(0,0,0,0.28); border: 1px solid rgba(0,0,0,0.08); padding: 18px; }
    .decline-modal__title { font-size: 1.1rem; font-weight: 700; color: #9b1b30; margin-bottom: 6px; }
    .decline-modal__text { color: #333; margin-bottom: 16px; }
    .decline-modal__note { margin-bottom: 14px; }
    .decline-modal__note label { display: block; font-size: 0.85rem; font-weight: 600; color: #555; margin-bottom: 6px; }
    .decline-modal__note textarea { width: 100%; border: 1px solid #ddd; border-radius: 8px; padding: 10px 12px; font-family: Arial, Helvetica, sans-serif; font-size: 0.92rem; min-height: 88px; resize: vertical; }
    .decline-modal__actions { display: flex; justify-content: flex-end; gap: 10px; }
  </style>
</head>
<body class="admin-page">

  <nav class="navbar">
    <a href="index.php" class="navbar__logo">Dr. Ruby M. Suresh</a>
    <ul class="navbar__links admin-navlinks">
      <li><a href="index.php">Home</a></li>
      <li><a href="admin.php">Dashboard</a></li>
      <li><a href="appointments.php" aria-current="page">Appointments</a></li>
      <li><a href="patients.php">Patients</a></li>
      <li><a href="messages.php">Messages</a></li>
    </ul>
    <div class="navbar__cta">
      <a href="actions/logout.php" class="btn-secondary" style="padding:10px 20px;font-size:0.85rem;">Log Out</a>
    </div>
  </nav>

  <main class="admin-dashboard">
    <h1 class="admin-panel__title" style="margin-bottom:16px;">All Appointments</h1>

    <?php if ($flashSuccess): ?>
      <?php if (($_GET['success'] ?? '') === 'completed'): ?>
        <div class="flash flash--ok">Appointment marked as completed.</div>
      <?php elseif (($_GET['success'] ?? '') === 'declined'): ?>
        <div class="flash flash--declined">Appointment declined successfully.</div>
      <?php else: ?>
        <div class="flash flash--ok">Appointment updated successfully.</div>
      <?php endif; ?>
    <?php endif; ?>
    <?php if ($flashError === 'time_taken'): ?>
      <div class="flash flash--err">That date and time is already taken. Please choose another slot.</div>
    <?php elseif ($flashError === 'missing_fields'): ?>
      <div class="flash flash--err">Please fill in the new date and time.</div>
    <?php elseif ($flashError === 'checkin_early'): ?>
      <div class="flash flash--err">Check-in opens 30 minutes before appointment time.</div>
    <?php endif; ?>

    <form method="get" class="filters">
      <div>
        <label for="status">Status</label>
        <select id="status" name="status">
          <?php
          $opts = ['All', 'Pending', 'Confirmed', 'Rescheduled', 'Checked In', 'Completed', 'Declined'];
          foreach ($opts as $o):
              $sel = ($statusFilter === $o) ? ' selected' : '';
          ?>
            <option value="<?= h($o) ?>"<?= $sel ?>><?= h($o) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label for="date_from">From</label>
        <input type="date" id="date_from" name="date_from" value="<?= h($dateFrom) ?>" />
      </div>
      <div>
        <label for="date_to">To</label>
        <input type="date" id="date_to" name="date_to" value="<?= h($dateTo) ?>" />
      </div>
      <div>
        <label for="search">Patient</label>
        <input type="text" id="search" name="search" value="<?= h($search) ?>" placeholder="Search name" />
      </div>
      <div>
        <button type="submit" class="btn-primary" style="padding:10px 24px;">Filter</button>
      </div>
    </form>

    <section class="admin-panel">
      <div class="admin-table-wrap">
        <table class="admin-table">
          <thead>
            <tr>
              <th>Name</th>
              <th>Email</th>
              <th>Phone</th>
              <th>Date</th>
              <th>Time</th>
              <th>Type</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($rows as $a): ?>
              <?php $canCheckIn = appointment_can_check_in((string) $a['date'], (string) $a['time_start']); ?>
              <?php $noteParts = split_appointment_notes((string) ($a['notes'] ?? '')); ?>
              <tr data-appt-row="<?= h((string) $a['appointment_id']) ?>">
                <td><?= h($a['patient_name']) ?></td>
                <td><?= h($a['patient_email']) ?></td>
                <td><?= h((string) ($a['patient_phone'] ?? '')) ?></td>
                <td><?= h(format_date_long($a['date'])) ?></td>
                <td><?= h(format_time_ampm($a['time_start'])) ?></td>
                <td><?= h($a['type']) ?></td>
                <td><span class="<?= h(appointment_status_tag_class($a['status'])) ?>"><?= h($a['status']) ?></span></td>
                <td>
                  <div class="admin-actions" style="flex-direction:column;align-items:flex-start;gap:6px;">
                    <?php if ($a['status'] === 'Checked In'): ?>
                      <button type="button" class="admin-action admin-action--approve js-toggle-complete" data-target="<?= h((string) $a['appointment_id']) ?>" style="text-decoration:none;">Complete</button>
                    <?php elseif ($a['status'] === 'Pending'): ?>
                      <form method="post" action="actions/approve_appointment.php">
                        <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>" />
                        <input type="hidden" name="appointment_id" value="<?= h((string) $a['appointment_id']) ?>" />
                        <input type="hidden" name="return_to" value="appointments" />
                        <button type="submit" class="admin-action admin-action--approve" style="text-decoration:none;">Confirm</button>
                      </form>
                      <button type="button" class="admin-action js-toggle-reschedule" data-target="<?= h((string) $a['appointment_id']) ?>">Reschedule</button>
                      <form method="post" action="actions/decline_appointment.php" class="js-decline-form">
                        <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>" />
                        <input type="hidden" name="appointment_id" value="<?= h((string) $a['appointment_id']) ?>" />
                        <input type="hidden" name="return_to" value="appointments" />
                        <input type="hidden" name="notes" value="" />
                        <button type="submit" class="admin-action admin-action--decline" style="text-decoration:none;">Decline</button>
                      </form>
                    <?php elseif ($a['status'] === 'Confirmed'): ?>
                      <?php if ($canCheckIn): ?>
                        <form method="post" action="actions/checkin_appointment.php">
                          <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>" />
                          <input type="hidden" name="appointment_id" value="<?= h((string) $a['appointment_id']) ?>" />
                          <input type="hidden" name="return_to" value="appointments" />
                          <button type="submit" class="admin-action" style="text-decoration:none;">Check-in</button>
                        </form>
                      <?php else: ?>
                        <button type="button" class="admin-action" disabled title="Check-in opens 30 minutes before appointment time" style="text-decoration:none;">Check-in</button>
                      <?php endif; ?>
                      <button type="button" class="admin-action js-toggle-reschedule" data-target="<?= h((string) $a['appointment_id']) ?>">Reschedule</button>
                      <form method="post" action="actions/decline_appointment.php" class="js-decline-form">
                        <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>" />
                        <input type="hidden" name="appointment_id" value="<?= h((string) $a['appointment_id']) ?>" />
                        <input type="hidden" name="return_to" value="appointments" />
                        <input type="hidden" name="notes" value="" />
                        <button type="submit" class="admin-action admin-action--decline" style="text-decoration:none;">Decline</button>
                      </form>
                    <?php elseif ($a['status'] === 'Rescheduled'): ?>
                      <button type="button" class="admin-action js-toggle-reschedule" data-target="<?= h((string) $a['appointment_id']) ?>">Reschedule</button>
                      <form method="post" action="actions/decline_appointment.php" class="js-decline-form">
                        <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>" />
                        <input type="hidden" name="appointment_id" value="<?= h((string) $a['appointment_id']) ?>" />
                        <input type="hidden" name="return_to" value="appointments" />
                        <input type="hidden" name="notes" value="" />
                        <button type="submit" class="admin-action admin-action--decline" style="text-decoration:none;">Decline</button>
                      </form>
                    <?php elseif ($a['status'] === 'Completed' || $a['status'] === 'Declined'): ?>
                      <button type="button" class="admin-action js-toggle-notes" data-target="<?= h((string) $a['appointment_id']) ?>">Notes</button>
                    <?php else: ?>
                      <span style="color:#888;">—</span>
                    <?php endif; ?>
                  </div>
                </td>
              </tr>
              <tr class="subrow subrow-hidden js-reschedule-<?= h((string) $a['appointment_id']) ?>">
                <td colspan="8">
                  <form method="post" action="actions/reschedule_appointment.php" class="admin-inline-form" style="margin:0;">
                    <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>" />
                    <input type="hidden" name="appointment_id" value="<?= h((string) $a['appointment_id']) ?>" />
                    <input type="hidden" name="return_to" value="appointments" />
                    <label>New date <input type="date" name="new_date" value="<?= h($a['date']) ?>" required /></label>
                    <label>New time
                      <select name="new_time" required>
                        <?php foreach ($timeSlots as $val => $lab): ?>
                          <option value="<?= h($val) ?>"<?= ($a['time_start'] === $val) ? ' selected' : '' ?>><?= h($lab) ?></option>
                        <?php endforeach; ?>
                      </select>
                    </label>
                    <button type="submit" class="btn-secondary">Confirm reschedule</button>
                  </form>
                </td>
              </tr>
              <tr class="subrow subrow-hidden js-complete-<?= h((string) $a['appointment_id']) ?>">
                <td colspan="8">
                  <form method="post" action="actions/complete_appointment.php" class="admin-inline-form" style="margin:0;align-items:flex-end;">
                    <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>" />
                    <input type="hidden" name="appointment_id" value="<?= h((string) $a['appointment_id']) ?>" />
                    <input type="hidden" name="return_to" value="appointments" />
                    <label style="display:block;min-width:320px;">
                      Completion notes (optional)
                      <textarea name="notes" rows="3" style="margin-top:6px;"></textarea>
                    </label>
                    <button type="submit" class="btn-secondary">Save as Completed</button>
                  </form>
                </td>
              </tr>
              <tr class="subrow subrow-hidden js-notes-<?= h((string) $a['appointment_id']) ?>">
                <td colspan="8">
                  <div class="notes-card">
                    <div class="notes-card__title">Notes</div>
                    <div class="notes-card__line">
                      <strong>Patient's Notes:</strong>
                      <div class="notes-card__value"><?= $noteParts['patient'] === '' ? '<span style="color:#666;">None</span>' : nl2br(h($noteParts['patient'])) ?></div>
                    </div>
                    <div class="notes-card__line">
                      <strong>Doctors Notes:</strong>
                      <div class="notes-card__value"><?= $noteParts['doctor'] === '' ? '<span style="color:#666;">None</span>' : nl2br(h($noteParts['doctor'])) ?></div>
                    </div>
                    <div class="notes-card__line">
                      <strong>Declination Note:</strong>
                      <div class="notes-card__value"><?= $noteParts['declination'] === '' ? '<span style="color:#666;">None</span>' : nl2br(h($noteParts['declination'])) ?></div>
                    </div>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
            <?php if (count($rows) === 0): ?>
              <tr><td colspan="8">No appointments match your filters.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
      <div class="pager">
        <?php
        $q = $_GET;
        if ($page > 1):
            $q['page'] = $page - 1;
            ?>
          <a href="?<?= h(http_build_query($q)) ?>" class="btn-secondary" style="padding:8px 20px;">Previous</a>
        <?php endif; ?>
        <span style="align-self:center;">Page <?= h((string) $page) ?> / <?= h((string) $totalPages) ?></span>
        <?php
        if ($page < $totalPages):
            $q['page'] = $page + 1;
            ?>
          <a href="?<?= h(http_build_query($q)) ?>" class="btn-secondary" style="padding:8px 20px;">Next</a>
        <?php endif; ?>
      </div>
    </section>
  </main>

  <div class="decline-modal-backdrop" id="decline-modal-backdrop" aria-hidden="true">
    <div class="decline-modal" role="dialog" aria-modal="true" aria-labelledby="decline-modal-title">
      <div class="decline-modal__title" id="decline-modal-title">Decline Appointment</div>
      <p class="decline-modal__text">Are you sure you want to decline this appointment?</p>
      <div class="decline-modal__note">
        <label for="decline-note-input">Declination Note (optional)</label>
        <textarea id="decline-note-input" placeholder="Add a note to explain why this appointment is declined..."></textarea>
      </div>
      <div class="decline-modal__actions">
        <button type="button" class="btn-secondary" id="decline-modal-cancel" style="padding:8px 16px;font-size:0.9rem;">Cancel</button>
        <button type="button" class="btn-primary" id="decline-modal-confirm" style="padding:8px 16px;font-size:0.9rem;">Decline</button>
      </div>
    </div>
  </div>

  <script src="js/appointments-admin.js"></script>
</body>
</html>
