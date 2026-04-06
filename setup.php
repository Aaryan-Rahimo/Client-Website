<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/db.php';

$db = get_db();

$db->exec("
CREATE TABLE IF NOT EXISTS users (
  user_id    INT AUTO_INCREMENT PRIMARY KEY,
  name       VARCHAR(255) NOT NULL,
  email      VARCHAR(255) NOT NULL,
  phone      VARCHAR(50),
  password   VARCHAR(255) NOT NULL,
  role       ENUM('admin', 'client') DEFAULT 'client',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY email (email)
);
");

$db->exec("
CREATE TABLE IF NOT EXISTS appointments (
  appointment_id INT AUTO_INCREMENT PRIMARY KEY,
  user_id        INT DEFAULT NULL,
  patient_name   VARCHAR(255) NOT NULL,
  patient_email  VARCHAR(255) NOT NULL,
  patient_phone  VARCHAR(50),
  date           DATE NOT NULL,
  time_start     TIME NOT NULL,
  time_end       TIME DEFAULT NULL,
  type           VARCHAR(100) NOT NULL,
  status         ENUM('Pending','Confirmed','Rescheduled','Checked In','Declined') DEFAULT 'Pending',
  notes          TEXT,
  created_at     DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL
);
");

$db->exec("
CREATE TABLE IF NOT EXISTS messages (
  message_id INT AUTO_INCREMENT PRIMARY KEY,
  name       VARCHAR(255) NOT NULL,
  email      VARCHAR(255) NOT NULL,
  subject    VARCHAR(255) DEFAULT NULL,
  body       TEXT NOT NULL,
  priority   ENUM('High', 'Medium', 'Low') DEFAULT 'Low',
  is_read    TINYINT(1) DEFAULT 0,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
");

$db->exec("
CREATE TABLE IF NOT EXISTS reviews (
  review_id  INT AUTO_INCREMENT PRIMARY KEY,
  name       VARCHAR(255) NOT NULL,
  rating     TINYINT NOT NULL,
  body       TEXT NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
");

$count = (int) $db->query('SELECT COUNT(*) FROM users')->fetchColumn();
if ($count > 0) {
    die('Setup already complete.');
}

$stmt = $db->prepare(
    'INSERT INTO users (name, email, phone, password, role)
     VALUES (?, ?, ?, ?, \'admin\')'
);
$stmt->execute([
    'Dr. Ruby Suresh',
    'ruby@clinic.com',
    '905-000-0000',
    password_hash('admin123', PASSWORD_BCRYPT),
]);

echo 'Setup complete.';
