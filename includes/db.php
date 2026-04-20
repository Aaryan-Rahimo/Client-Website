<?php

/*
 * Author: Aaryan, Kissan, Inderbir, Angad
 * Date Created: 2026-04-03
 * Description: Database connection and setup functions for the clinic web application, including PDO initialization, table creation, and default admin user setup.
 */

declare(strict_types=1);

function get_db(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $host    = 'localhost';
        $db      = 'rahima33_db';
        $user    = 'rahima33_local';
        $pass    = 'tiVrkar4!@';
        $charset = 'utf8mb4';

        $tempPdo = new PDO("mysql:host={$host};charset={$charset}", $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
        $tempPdo->exec("CREATE DATABASE IF NOT EXISTS `$db`");

        $dsn     = "mysql:host={$host};dbname={$db};charset={$charset}";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        $pdo = new PDO($dsn, $user, $pass, $options);

        try {
            $pdo->exec("ALTER TABLE appointments MODIFY status ENUM('Pending','Confirmed','Rescheduled','Checked In','Completed','Declined') DEFAULT 'Pending'");
        } catch (Throwable $e) {
        }
    }
    return $pdo;
}

function ensure_reviews_table(PDO $db): void
{
    $db->exec("
    CREATE TABLE IF NOT EXISTS reviews (
      review_id  INT AUTO_INCREMENT PRIMARY KEY,
      name       VARCHAR(255) NOT NULL,
      rating     TINYINT NOT NULL,
      body       TEXT NOT NULL,
      created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );
    ");
}

function ensure_users_table(PDO $db): void
{
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
}

function ensure_default_admin_user(PDO $db): void
{
    ensure_users_table($db);

    $count = (int) $db->query('SELECT COUNT(*) FROM users')->fetchColumn();
    if ($count > 0) {
        return;
    }

    $stmt = $db->prepare(
        "INSERT INTO users (name, email, phone, password, role)
         VALUES (?, ?, ?, ?, 'admin')"
    );
    $stmt->execute([
        'Dr. Ruby M. Suresh',
        'ruby@clinic.com',
        '905-000-0000',
        password_hash('admin123', PASSWORD_BCRYPT),
    ]);
}
