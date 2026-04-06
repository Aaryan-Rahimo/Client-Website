<?php

declare(strict_types=1);

/**
 * @return PDO
 */
function get_db(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $host    = 'localhost';
        $db      = 'clinic_db';
        $user    = 'root';
        $pass    = '';
        $charset = 'utf8mb4';
        $dsn     = "mysql:host={$host};dbname={$db};charset={$charset}";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        $pdo = new PDO($dsn, $user, $pass, $options);
    }
    return $pdo;
}

/**
 * Ensure the reviews table exists before reading/writing reviews.
 */
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

/**
 * Ensure the users table exists for authentication.
 */
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

/**
 * Seed the default admin user when users table is empty.
 */
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
        'Dr. Ruby Suresh',
        'ruby@clinic.com',
        '905-000-0000',
        password_hash('admin123', PASSWORD_BCRYPT),
    ]);
}
