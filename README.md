# Client Website (PHP + MySQL)

## Quick Setup

1. Start **Apache** and **MySQL** in XAMPP.
2. Open phpMyAdmin: `http://localhost/phpmyadmin`.
3. Create a database named `clinic_db` (collation: `utf8mb4_general_ci` is fine).
4. Confirm DB connection settings in `includes/db.php`:
   - host: `localhost`
   - db: `clinic_db`
   - user: `root`
   - password: `` (empty by default in XAMPP)
5. Run setup once in browser:
   - `http://localhost/Client-Website/setup.php`
6. Open the app:
   - Website: `http://localhost/Client-Website/index.php`
   - Staff login: `http://localhost/Client-Website/login.php`

## Default Staff Login

- Email: `ruby@clinic.com`
- Password: `admin123`

## Notes

- The app now auto-creates missing core tables (like `users` and `reviews`) on first use.
- `setup.php` is still recommended to initialize everything in one go.

## Should I export the existing DB?

Short answer: **yes, if you want backup/sharing/deployment consistency**.

- **Export recommended when:**
  - you want a backup before making major changes
  - you are moving the project to another machine/server
  - teammates need the same data/schema
- **Not required for normal local development** if you can rerun setup and start fresh.

### How to export (phpMyAdmin)

1. Go to `clinic_db` in phpMyAdmin.
2. Click **Export**.
3. Choose **Quick** + **SQL**.
4. Download the `.sql` file and keep it in a safe place (or commit a schema-only export to the repo if desired).
