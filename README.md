# MKSU Student Registration Portal

This is a lightweight PHP application that lets new MKSU students activate their portal accounts by submitting their bio data. The stack is intentionally minimal so it can run with the built-in PHP development server and a MySQL or MariaDB instance.

## Prerequisites

- PHP 8.1+ with the PDO MySQL extension enabled
- MySQL/MariaDB database (default credentials are defined in `config/database.php`)

## Database setup

1. Create a database named `mksu_reg` (or update the name in `config/database.php`).
2. Run the SQL statements in `queries/queries.sql` to create the required tables (`students`, `courses`, `units`, `registrations`). The statements are idempotent (`IF NOT EXISTS`), so you can rerun them safely.
3. On first launch the app will auto-create/seed the `courses` table if it's empty, but you can also insert your actual faculty offerings, e.g.

```sql
INSERT INTO courses (course_code, course_name, faculty)
VALUES ('BCS', 'Bachelor of Computer Science', 'Computing & Informatics');
```

## Running the development server

```powershell
# From the project root
php -S localhost:8000
```

Then open <http://localhost:8000/register> in your browser.

## Student registration flow

1. Fill out every required field in the registration form.
2. Passwords must have at least 8 characters and the confirmation should match.
3. Submit the form; successful registrations display a green confirmation banner.
4. The record is persisted in the `students` table with the hashed password stored in `default_password`.

## Login + dashboard

1. Visit `/login` and enter your admission number (e.g. `MKSU/COM/001/2024`) or student email, plus the password you set.
2. After a successful login you’ll be redirected to `/student/dashboard.php`, which shows a summary card with your bio, contact info, course, and year of study.
3. Use the “Logout” button on the card header to clear the session and return to the login screen.

## Troubleshooting tips

- If you see "Unable to load courses", confirm you ran the course seed query and that the DB credentials are correct.
- Validation errors are shown inline under the hero card; fix the highlighted issues and resubmit.
- Server errors are logged via `error_log` for easier debugging.

## Next steps

- Build the login flow in `login.php` to authenticate students using their admission number/email plus password.
- Add unit registration workflows leveraging the `registrations` table.
