<?php

/*
 * Author: Kissan and Inderbir
 * Date Created: 2026-04-19
 * Description: Project source file used by the clinic web application.
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';

require_admin();

$db = get_db();

$newMonthOnly = (string) ($_GET['new_month'] ?? '') === '1';
$monthStart = date('Y-m-01 00:00:00');

if ($newMonthOnly) {
    $stmt = $db->prepare(
        'SELECT p.patient_name, p.patient_email, p.patient_phone,
                (SELECT COUNT(*) FROM appointments a2 WHERE a2.patient_email = p.patient_email) AS appointment_count
         FROM (
            SELECT patient_email,
                   MIN(patient_name) AS patient_name,
                   MAX(patient_phone) AS patient_phone
            FROM appointments
            WHERE created_at >= ?
            GROUP BY patient_email
         ) p
         ORDER BY p.patient_name ASC'
    );
    $stmt->execute([$monthStart]);
    $patients = $stmt->fetchAll();
} else {
    $patients = $db->query(
        'SELECT p.patient_name, p.patient_email, p.patient_phone,
                (SELECT COUNT(*) FROM appointments a2 WHERE a2.patient_email = p.patient_email) AS appointment_count
         FROM (
            SELECT patient_email,
                   MIN(patient_name) AS patient_name,
                   MAX(patient_phone) AS patient_phone
            FROM appointments
            GROUP BY patient_email
         ) p
         ORDER BY p.patient_name ASC'
    )->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Patients — Admin</title>
  <link rel="stylesheet" href="css/styles.css" />
  <link rel="stylesheet" href="css/admin.css" />
  <style>
    .admin-navlinks { display: flex; gap: 20px; list-style: none; align-items: center; flex-wrap: wrap; }
    .flash { padding: 12px 16px; border-radius: 8px; margin-bottom: 16px; }
    .flash--info { background: rgba(41,171,226,0.15); color: #156a94; }
  </style>
</head>
<body class="admin-page">

  <nav class="navbar">
    <a href="index.php" class="navbar__logo">Dr. Ruby M. Suresh</a>
    <ul class="navbar__links admin-navlinks">
      <li><a href="index.php">Home</a></li>
      <li><a href="admin.php">Dashboard</a></li>
      <li><a href="appointments.php">Appointments</a></li>
      <li><a href="patients.php" aria-current="page">Patients</a></li>
      <li><a href="messages.php">Messages</a></li>
    </ul>
    <div class="navbar__cta">
      <a href="actions/logout.php" class="btn-secondary" style="padding:10px 20px;font-size:0.85rem;">Log Out</a>
    </div>
  </nav>

  <main class="admin-dashboard">
    <h1 class="admin-panel__title" style="margin-bottom:16px;">Patients</h1>

    <?php if ($newMonthOnly): ?>
      <div class="flash flash--info">Showing patients with appointments booked this month.</div>
    <?php endif; ?>

    <section class="admin-panel">
      <div class="admin-table-wrap">
        <table class="admin-table">
          <thead>
            <tr>
              <th>Patient Name</th>
              <th>Email</th>
              <th>Phone</th>
              <th>Total Appointments</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($patients as $p): ?>
              <tr>
                <td><?= h((string) $p['patient_name']) ?></td>
                <td><?= h((string) $p['patient_email']) ?></td>
                <td><?= h((string) ($p['patient_phone'] ?? '')) ?></td>
                <td><?= h((string) $p['appointment_count']) ?></td>
              </tr>
            <?php endforeach; ?>
            <?php if (count($patients) === 0): ?>
              <tr><td colspan="4">No patients found.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </section>
  </main>
</body>
</html>
