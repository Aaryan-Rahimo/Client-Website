<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';

require_admin();

$db    = get_db();
$today = date('Y-m-d');

$stmt = $db->prepare("SELECT COUNT(*) FROM appointments WHERE date = ? AND status != 'Declined'");
$stmt->execute([$today]);
$todayCount = (int) $stmt->fetchColumn();

$monthStart = date('Y-m-01');
$stmt       = $db->prepare('SELECT COUNT(DISTINCT patient_email) FROM appointments WHERE created_at >= ?');
$stmt->execute([$monthStart]);
$newPatients = (int) $stmt->fetchColumn();

$unread = (int) $db->query('SELECT COUNT(*) FROM messages WHERE is_read = 0')->fetchColumn();

$stmt = $db->prepare("SELECT * FROM appointments WHERE date = ? AND status != 'Declined' ORDER BY time_start ASC LIMIT 10");
$stmt->execute([$today]);
$recentAppts = $stmt->fetchAll();

$recentMsgs = $db->query('SELECT * FROM messages ORDER BY is_read ASC, created_at DESC LIMIT 5')->fetchAll();

$pendingAppts = $db->query("SELECT * FROM appointments WHERE status = 'Pending' ORDER BY date ASC, time_start ASC LIMIT 10")->fetchAll();

$timeSlots = appointment_time_slots();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin Dashboard — Ruby Suresh Dental</title>
  <meta name="description" content="Staff dashboard for Ruby Suresh Dental Clinic." />
  <link rel="stylesheet" href="css/styles.css" />
  <link rel="stylesheet" href="css/admin.css" />
  <style>
    .admin-navlinks { display: flex; gap: 20px; list-style: none; align-items: center; flex-wrap: wrap; }
    .admin-navlinks a { font-weight: 500; color: #333; }
    .admin-navlinks a:hover { color: #9B1B30; }
    .admin-inline-form { display: flex; flex-wrap: wrap; gap: 8px; align-items: center; margin-top: 6px; }
    .admin-inline-form input[type="date"],
    .admin-inline-form select { width: auto; min-width: 8rem; font-size: 0.8rem; padding: 6px 10px; }
    .admin-inline-form button { padding: 6px 14px; font-size: 0.75rem; border-radius: 50px; }
  </style>
</head>
<body class="admin-page">

  <nav class="navbar" id="navbar" aria-label="Main navigation">
    <a href="index.php" class="navbar__logo">Ruby Suresh Dental</a>
    <ul class="navbar__links admin-navlinks">
      <li><a href="index.php#hero">Home</a></li>
      <li><a href="index.php#about">About</a></li>
      <li><a href="index.php#services">Services</a></li>
      <li><a href="index.php#contact">Contact</a></li>
      <li><a href="admin.php" aria-current="page">Dashboard</a></li>
      <li><a href="appointments.php">Appointments</a></li>
      <li><a href="messages.php">Messages</a></li>
    </ul>
    <div class="navbar__cta">
      <a href="index.php#appointment" class="btn-primary">Book an Appointment</a>
    </div>
  </nav>

  <main class="admin-dashboard">
    <header class="admin-top" aria-label="Dashboard header">
      <div class="admin-top__titles">
        <h1>Admin Dashboard</h1>
        <p>Welcome back, Ruby!</p>
      </div>
      <div class="admin-top__controls">
        <div class="admin-search">
          <label class="visually-hidden" for="admin-search">Search Patients, Appointments</label>
          <input type="search" id="admin-search" placeholder="Search Patients, Appointments…" autocomplete="off" />
        </div>
        <div class="admin-profile">
          <button type="button" class="admin-profile__trigger" id="admin-profile-trigger" aria-expanded="false" aria-haspopup="true" aria-controls="admin-profile-menu">
            <span class="admin-profile__avatar" aria-hidden="true"></span>
            <span>Ruby <span class="admin-profile__caret" aria-hidden="true">▾</span></span>
          </button>
          <div class="admin-profile__menu" id="admin-profile-menu" role="menu">
            <a href="actions/logout.php" role="menuitem" style="display:block;padding:10px 12px;color:#9b1b30;font-weight:500;text-decoration:none;border-radius:6px;">Log Out</a>
          </div>
        </div>
      </div>
    </header>

    <section class="admin-stats" aria-label="Summary statistics">
      <article class="admin-stat-card admin-stat-card--ruby">
        <div class="admin-stat-card__icon" aria-hidden="true">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
            <path d="M16 2v4M8 2v4M3 10h18" stroke-linecap="round" />
          </svg>
        </div>
        <div class="admin-stat-card__body">
          <p class="admin-stat-card__label">Today&rsquo;s Appointments</p>
          <div class="admin-stat-card__value-row">
            <span class="admin-stat-card__value"><?= h((string) $todayCount) ?></span>
            <span class="admin-stat-card__delta">+/- 5%</span>
          </div>
        </div>
      </article>
      <article class="admin-stat-card">
        <div class="admin-stat-card__icon" aria-hidden="true">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
          </svg>
        </div>
        <div class="admin-stat-card__body">
          <p class="admin-stat-card__label">New Patients (This Month)</p>
          <div class="admin-stat-card__value-row">
            <span class="admin-stat-card__value"><?= h((string) $newPatients) ?></span>
          </div>
        </div>
      </article>
      <article class="admin-stat-card">
        <div class="admin-stat-card__icon" aria-hidden="true">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
          </svg>
        </div>
        <div class="admin-stat-card__body">
          <p class="admin-stat-card__label">Unread Messages</p>
          <div class="admin-stat-card__value-row">
            <span class="admin-stat-card__value"><?= h((string) $unread) ?></span>
          </div>
        </div>
      </article>
    </section>

    <div class="admin-main">
      <div class="admin-main__left">
        <section class="admin-panel" aria-labelledby="recent-appointments-heading">
          <h2 class="admin-panel__title" id="recent-appointments-heading">Recent Appointments</h2>
          <div class="admin-table-wrap">
            <table class="admin-table" id="appointments-table">
              <thead>
                <tr>
                  <th scope="col">Patient Name</th>
                  <th scope="col">Time</th>
                  <th scope="col">Type</th>
                  <th scope="col">Status</th>
                  <th scope="col">Actions</th>
                </tr>
              </thead>
              <tbody id="appointments-tbody">
                <?php if (count($recentAppts) === 0): ?>
                  <tr><td colspan="5">No appointments scheduled for today.</td></tr>
                <?php else: ?>
                  <?php foreach ($recentAppts as $a): ?>
                    <tr>
                      <td data-patient-name><?= h($a['patient_name']) ?></td>
                      <td data-time><?= h(format_time_ampm($a['time_start'])) ?></td>
                      <td><?= h($a['type']) ?></td>
                      <td><span class="<?= h(appointment_status_tag_class($a['status'])) ?>"><?= h($a['status']) ?></span></td>
                      <td data-actions>
                        <?php if ($a['status'] === 'Pending'): ?>
                          <div class="admin-actions" style="flex-direction:column;align-items:flex-start;">
                            <form method="post" action="actions/approve_appointment.php" style="display:inline;">
                              <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>" />
                              <input type="hidden" name="appointment_id" value="<?= h((string) $a['appointment_id']) ?>" />
                              <button type="submit" class="admin-action admin-action--approve" style="text-decoration:none;">[Approve]</button>
                            </form>
                            <form method="post" action="actions/decline_appointment.php" style="display:inline;">
                              <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>" />
                              <input type="hidden" name="appointment_id" value="<?= h((string) $a['appointment_id']) ?>" />
                              <button type="submit" class="admin-action admin-action--decline" style="text-decoration:none;">[Decline]</button>
                            </form>
                          </div>
                        <?php elseif ($a['status'] === 'Confirmed'): ?>
                          <div class="admin-actions">
                            <form method="post" action="actions/checkin_appointment.php" style="display:inline;">
                              <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>" />
                              <input type="hidden" name="appointment_id" value="<?= h((string) $a['appointment_id']) ?>" />
                              <button type="submit" class="admin-action" style="text-decoration:none;">[Check-in]</button>
                            </form>
                          </div>
                          <form method="post" action="actions/reschedule_appointment.php" class="admin-inline-form">
                            <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>" />
                            <input type="hidden" name="appointment_id" value="<?= h((string) $a['appointment_id']) ?>" />
                            <input type="date" name="new_date" value="<?= h($a['date']) ?>" required />
                            <select name="new_time" required>
                              <?php foreach ($timeSlots as $val => $lab): ?>
                                <option value="<?= h($val) ?>" <?= $a['time_start'] === $val ? ' selected' : '' ?>><?= h($lab) ?></option>
                              <?php endforeach; ?>
                            </select>
                            <button type="submit" class="btn-secondary">Reschedule</button>
                          </form>
                        <?php elseif ($a['status'] === 'Rescheduled'): ?>
                          <form method="post" action="actions/reschedule_appointment.php" class="admin-inline-form">
                            <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>" />
                            <input type="hidden" name="appointment_id" value="<?= h((string) $a['appointment_id']) ?>" />
                            <input type="date" name="new_date" value="<?= h($a['date']) ?>" required />
                            <select name="new_time" required>
                              <?php foreach ($timeSlots as $val => $lab): ?>
                                <option value="<?= h($val) ?>" <?= $a['time_start'] === $val ? ' selected' : '' ?>><?= h($lab) ?></option>
                              <?php endforeach; ?>
                            </select>
                            <button type="submit" class="btn-secondary">Reschedule</button>
                          </form>
                        <?php else: ?>
                          <span style="color:#888;">—</span>
                        <?php endif; ?>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
          <div class="admin-panel__footer admin-panel__footer--center">
            <a href="appointments.php" class="btn-primary">View All Appointments</a>
          </div>
        </section>
      </div>

      <div class="admin-main__right">
        <section class="admin-panel" aria-labelledby="recent-messages-heading">
          <h2 class="admin-panel__title" id="recent-messages-heading">Recent Messages</h2>
          <div class="admin-table-wrap">
            <table class="admin-table">
              <thead>
                <tr>
                  <th scope="col">Patient</th>
                  <th scope="col">Subject</th>
                  <th scope="col">Time</th>
                  <th scope="col">Priority</th>
                </tr>
              </thead>
              <tbody>
                <?php if (count($recentMsgs) === 0): ?>
                  <tr><td colspan="4">No messages yet.</td></tr>
                <?php else: ?>
                  <?php foreach ($recentMsgs as $m): ?>
                    <tr>
                      <td><?= h($m['name']) ?></td>
                      <td><?= h((string) ($m['subject'] ?? '')) ?></td>
                      <td><?= h(date('g:i A', strtotime($m['created_at']))) ?></td>
                      <td><span class="<?= h(message_priority_tag_class($m['priority'])) ?>"><?= h($m['priority']) ?></span></td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
          <div class="admin-panel__footer admin-panel__footer--right">
            <a href="messages.php" class="btn-secondary">Go to Messages</a>
          </div>
        </section>

        <section class="admin-panel" aria-labelledby="pending-requests-heading">
          <h2 class="admin-panel__title" id="pending-requests-heading">Pending Appointment Requests</h2>
          <div class="admin-table-wrap">
            <table class="admin-table" id="pending-table">
              <thead>
                <tr>
                  <th scope="col">Patient</th>
                  <th scope="col">Date/Time Request</th>
                  <th scope="col">Status</th>
                  <th scope="col">Actions</th>
                </tr>
              </thead>
              <tbody id="pending-tbody">
                <?php if (count($pendingAppts) === 0): ?>
                  <tr><td colspan="4">No pending requests.</td></tr>
                <?php else: ?>
                  <?php foreach ($pendingAppts as $p): ?>
                    <tr>
                      <td><?= h($p['patient_name']) ?></td>
                      <td><?= h(format_date_long($p['date']) . ', ' . format_time_ampm($p['time_start'])) ?></td>
                      <td><span class="<?= h(appointment_status_tag_class($p['status'])) ?>"><?= h($p['status']) ?></span></td>
                      <td>
                        <div class="admin-actions">
                          <form method="post" action="actions/approve_appointment.php" style="display:inline;">
                            <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>" />
                            <input type="hidden" name="appointment_id" value="<?= h((string) $p['appointment_id']) ?>" />
                            <button type="submit" class="admin-action admin-action--approve" style="text-decoration:none;">[Approve]</button>
                          </form>
                          <form method="post" action="actions/decline_appointment.php" style="display:inline;">
                            <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>" />
                            <input type="hidden" name="appointment_id" value="<?= h((string) $p['appointment_id']) ?>" />
                            <button type="submit" class="admin-action admin-action--decline" style="text-decoration:none;">[Decline]</button>
                          </form>
                        </div>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
          <div class="admin-panel__footer admin-panel__footer--center">
            <a href="appointments.php" class="btn-primary">Review All Requests</a>
          </div>
        </section>
      </div>
    </div>
  </main>

  <script>
    (function () {
      var profileTrigger = document.getElementById("admin-profile-trigger");
      var profileMenu = document.getElementById("admin-profile-menu");
      var searchInput = document.getElementById("admin-search");
      var appointmentsTbody = document.getElementById("appointments-tbody");

      function closeProfileMenu() {
        if (profileMenu && profileTrigger) {
          profileMenu.classList.remove("is-open");
          profileTrigger.setAttribute("aria-expanded", "false");
        }
      }

      if (profileTrigger && profileMenu) {
        profileTrigger.addEventListener("click", function (e) {
          e.stopPropagation();
          var open = !profileMenu.classList.contains("is-open");
          profileMenu.classList.toggle("is-open", open);
          profileTrigger.setAttribute("aria-expanded", open ? "true" : "false");
        });
        document.addEventListener("click", closeProfileMenu);
        /* keep menu open when clicking Log out link */
        profileMenu.addEventListener("click", function (e) { e.stopPropagation(); });
      }

      if (searchInput && appointmentsTbody) {
        searchInput.addEventListener("input", function () {
          var q = searchInput.value.trim().toLowerCase();
          var rows = appointmentsTbody.querySelectorAll("tr");
          rows.forEach(function (tr) {
            var nameCell = tr.querySelector("[data-patient-name]");
            var text = nameCell ? nameCell.textContent.trim().toLowerCase() : "";
            var hide = q !== "" && text.indexOf(q) === -1;
            tr.classList.toggle("row-hidden", hide);
          });
        });
      }
    })();
  </script>
</body>
</html>
