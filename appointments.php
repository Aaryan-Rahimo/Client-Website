<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';

require_admin();

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
    .filters input, .filters select { padding: 8px 10px; font-family: CreatoDisplay, sans-serif; }
    .flash { padding: 12px 16px; border-radius: 8px; margin-bottom: 16px; }
    .flash--ok { background: rgba(52,168,83,0.15); color: #1e7e34; }
    .flash--err { background: #fdecea; color: #9B1B30; }
    .subrow td { background: #f8f9fb; padding: 16px; }
    .subrow-hidden { display: none; }
    .pager { display: flex; justify-content: center; gap: 16px; margin-top: 20px; }
  </style>
</head>
<body class="admin-page">

  <nav class="navbar">
    <a href="index.php" class="navbar__logo">Ruby Suresh Dental</a>
    <ul class="navbar__links admin-navlinks">
      <li><a href="admin.php">Dashboard</a></li>
      <li><a href="appointments.php" aria-current="page">Appointments</a></li>
      <li><a href="messages.php">Messages</a></li>
      <li><a href="index.php">Website</a></li>
    </ul>
    <div class="navbar__cta">
      <a href="actions/logout.php" class="btn-secondary" style="padding:10px 20px;font-size:0.85rem;">Log Out</a>
    </div>
  </nav>

  <main class="admin-dashboard">
    <h1 class="admin-panel__title" style="margin-bottom:16px;">All Appointments</h1>

    <?php if ($flashSuccess): ?>
      <div class="flash flash--ok">Appointment updated successfully.</div>
    <?php endif; ?>
    <?php if ($flashError === 'time_taken'): ?>
      <div class="flash flash--err">That date and time is already taken. Please choose another slot.</div>
    <?php elseif ($flashError === 'missing_fields'): ?>
      <div class="flash flash--err">Please fill in the new date and time.</div>
    <?php endif; ?>

    <form method="get" class="filters">
      <div>
        <label for="status">Status</label>
        <select id="status" name="status">
          <?php
          $opts = ['All', 'Pending', 'Confirmed', 'Rescheduled', 'Checked In', 'Declined'];
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
                    <?php if ($a['status'] === 'Confirmed'): ?>
                      <form method="post" action="actions/checkin_appointment.php">
                        <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>" />
                        <input type="hidden" name="appointment_id" value="<?= h((string) $a['appointment_id']) ?>" />
                        <button type="submit" class="admin-action" style="text-decoration:none;">Check-in</button>
                      </form>
                    <?php endif; ?>
                    <button type="button" class="admin-action js-toggle-reschedule" data-target="<?= h((string) $a['appointment_id']) ?>">Reschedule</button>
                    <form method="post" action="actions/decline_appointment.php" onsubmit="return confirm('Decline this appointment?');">
                      <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>" />
                      <input type="hidden" name="appointment_id" value="<?= h((string) $a['appointment_id']) ?>" />
                      <button type="submit" class="admin-action admin-action--decline" style="text-decoration:none;">Decline</button>
                    </form>
                  </div>
                </td>
              </tr>
              <tr class="subrow subrow-hidden js-reschedule-<?= h((string) $a['appointment_id']) ?>">
                <td colspan="8">
                  <form method="post" action="actions/reschedule_appointment.php" class="admin-inline-form" style="margin:0;">
                    <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>" />
                    <input type="hidden" name="appointment_id" value="<?= h((string) $a['appointment_id']) ?>" />
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

  <script>
    document.querySelectorAll('.js-toggle-reschedule').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var id = btn.getAttribute('data-target');
        var row = document.querySelector('.js-reschedule-' + id);
        if (!row) return;
        row.classList.toggle('subrow-hidden');
      });
    });
  </script>
</body>
</html>
