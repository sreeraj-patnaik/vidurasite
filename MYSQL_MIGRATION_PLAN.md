# MySQL Migration Plan for VIDURA

## Goal
Move the entire VIDURA app from PostgreSQL to MySQL with the least possible disruption.

The easiest path is not a rewrite. It is a controlled swap of:
- database connection settings,
- table creation SQL,
- a few PostgreSQL-specific assumptions,
- and a short migration/import step for existing data.

This app is already close to MySQL-friendly because most queries are plain PDO prepared statements and standard SQL.

## Current Assessment

### What is already easy to migrate
- Most application queries use `PDO::prepare()` and parameter binding.
- Passwords are already handled with `password_hash()` and `password_verify()`, which work the same in MySQL.
- The app structure is simple: users, clubs, events, registrations, points, badges, gallery, announcements, settings.
- File uploads are already application-managed, so they do not depend on the database engine.

### What is PostgreSQL-specific
- `config/database.php` uses a PostgreSQL DSN.
- `setup.php` uses `SERIAL`, `TIMESTAMP DEFAULT CURRENT_TIMESTAMP`, and foreign key syntax written for PostgreSQL.
- `schema` export references PostgreSQL defaults like `nextval('..._seq'::regclass)`.
- Existing database values may depend on PostgreSQL auto-increment behavior.

### What can become a problem in MySQL
- `SERIAL` should become `INT AUTO_INCREMENT`.
- `BOOLEAN` should become `TINYINT(1)` or `BOOLEAN` in MySQL, which is effectively tinyint.
- `TEXT` is fine, but index behavior differs if you ever need indexed text columns.
- Existing PostgreSQL data cannot be connected to MySQL directly without export/import.

## Easiest Migration Strategy

The easiest and safest approach is:

1. Create a fresh MySQL database.
2. Rewrite only the database layer and schema setup.
3. Import or reseed data into MySQL.
4. Keep the PHP application logic mostly unchanged.
5. Test every major admin and member flow.

This is much easier than trying to make PostgreSQL and MySQL both work at runtime in the same codebase.

## Recommended Migration Steps

### Phase 1: Prepare MySQL
- Install or confirm MySQL 8.x.
- Create a new database for VIDURA.
- Create a dedicated MySQL user with full access to that database.
- Use `utf8mb4` encoding and `utf8mb4_unicode_ci` collation.

Recommended DB defaults:
- Charset: `utf8mb4`
- Collation: `utf8mb4_unicode_ci`
- Engine: `InnoDB`

### Phase 2: Update Connection Layer
Replace the PostgreSQL DSN in the database config with MySQL.

Current pattern:
- PostgreSQL host/port/dbname/user/password
- `pgsql:` DSN

MySQL target:
- `mysql:host=...;port=...;dbname=...;charset=utf8mb4`
- Same PDO error mode and fetch mode can stay.

Keep:
- `PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION`
- `PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC`
- `PDO::ATTR_EMULATE_PREPARES => false`

### Phase 3: Rewrite Schema Creation
Update `setup.php` so every table is created with MySQL syntax.

#### Main conversions
- `SERIAL PRIMARY KEY` -> `INT AUTO_INCREMENT PRIMARY KEY`
- `TIMESTAMP DEFAULT CURRENT_TIMESTAMP` -> valid MySQL equivalent
- foreign keys should be created with `ENGINE=InnoDB`
- use explicit `FOREIGN KEY (...) REFERENCES ...(...)`

#### Table-by-table notes
- `users`
  - `id` should be `INT AUTO_INCREMENT`
  - keep `roll_number`, `email`, `password`, `status`, `role`
  - keep `password` as `TEXT` or `VARCHAR(255)`; `VARCHAR(255)` is usually better for hashes
- `clubs`
  - simple MySQL conversion
- `events`
  - confirm all columns are actually present and consistently named
  - `club_id` foreign key should point to `clubs(id)`
  - `first_come_first_serve` should be `TINYINT(1)` if you want strict MySQL style
- `registrations`
  - foreign keys to `events(id)` and `users(id)`
- `point_logs`
  - foreign keys to `users(id)` and `events(id)`
- `badges`
  - straightforward conversion
- `user_badges`
  - foreign keys to `users(id)` and `badges(id)`
- `gallery`
  - foreign key to `events(id)`
- `announcements`
  - straightforward conversion
- `settings`
  - straightforward conversion
  - preserve homepage image fields and logo fields

### Phase 4: Fix Any SQL Assumptions
Review the app for SQL that may behave differently in MySQL.

Known focus areas:
- date/time handling
- boolean values
- `LIMIT` usage
- `NULL` handling in comparisons
- any future case-insensitive search features

Based on the current codebase, most normal `SELECT`, `INSERT`, `UPDATE`, and `DELETE` statements should work unchanged after the schema and connection swap.

### Phase 5: Data Migration
Move the existing data from PostgreSQL into MySQL.

Best options, from easiest to safest:

1. Manual export/import of CSV or SQL into MySQL
2. Scripted migration using PHP/PDO
3. One-time dump from PostgreSQL and transform into MySQL-friendly inserts

Recommended practical approach:
- Export each table from PostgreSQL.
- Import into MySQL in dependency order:
  1. clubs
  2. users
  3. settings
  4. events
  5. registrations
  6. point_logs
  7. badges
  8. user_badges
  9. gallery
  10. announcements

Why this order:
- tables with no foreign key dependencies first
- dependent tables after parent rows exist

### Phase 6: Verify Authentication
Confirm password behavior remains unchanged.

Good news:
- `password_hash()` and `password_verify()` are database-independent.
- No need to change the login logic just because the DB changes.

Verify:
- admin login works
- student login works
- password reset/edit flows still work

### Phase 7: Verify Uploads and Settings
Make sure the admin image settings still work after the DB switch.

Check:
- homepage banner upload
- TechKruti image upload
- KhelKruti image upload
- SamsKruti image upload
- LIET logo upload
- VIDURA logo upload

Because these are stored as filenames in the database, they should migrate cleanly as long as the `settings` row comes across correctly.

### Phase 8: Test Core Workflows
After the migration, run end-to-end checks:

- login/logout
- add student
- edit student
- delete student
- create event
- edit event
- delete event
- upload event banner
- register for event
- award points
- view leaderboard
- view gallery
- admin settings save

### Phase 9: Cutover
When MySQL is confirmed stable:

- point the app to the MySQL config
- stop using the PostgreSQL database for the live app
- keep a backup of the old PostgreSQL data for rollback

## Files That Will Need Attention

These are the main files involved in a MySQL migration:

- [config/database.php](C:/xampp/htdocs/vidurasite/config/database.php)
- [setup.php](C:/xampp/htdocs/vidurasite/setup.php)
- [setup_admin.php](C:/xampp/htdocs/vidurasite/setup_admin.php)
- [actions/login.php](C:/xampp/htdocs/vidurasite/actions/login.php)
- [actions/student_save.php](C:/xampp/htdocs/vidurasite/actions/student_save.php)
- [actions/student_update.php](C:/xampp/htdocs/vidurasite/actions/student_update.php)
- [actions/profile_update.php](C:/xampp/htdocs/vidurasite/actions/profile_update.php)
- [actions/event_save.php](C:/xampp/htdocs/vidurasite/actions/event_save.php)
- [actions/event_update.php](C:/xampp/htdocs/vidurasite/actions/event_update.php)
- [actions/announcement_save.php](C:/xampp/htdocs/vidurasite/actions/announcement_save.php)
- [actions/announcement_update.php](C:/xampp/htdocs/vidurasite/actions/announcement_update.php)
- [actions/settings_save.php](C:/xampp/htdocs/vidurasite/actions/settings_save.php)

The biggest changes are likely in:
- the connection file,
- the schema/bootstrap file,
- and any places where the current schema does not exactly match the runtime queries.

## Likely MySQL Schema Style

Example pattern:

```sql
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  roll_number VARCHAR(20) NOT NULL UNIQUE,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(120) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  phone VARCHAR(20),
  year SMALLINT,
  department VARCHAR(100),
  section VARCHAR(20),
  club_id INT NULL,
  profile_photo TEXT,
  bio TEXT,
  points INT DEFAULT 0,
  level INT DEFAULT 1,
  status VARCHAR(20) DEFAULT 'pending',
  role VARCHAR(20) DEFAULT 'member',
  joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_users_club
    FOREIGN KEY (club_id) REFERENCES clubs(id)
    ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

This is the style to apply across the whole schema.

## Important Caution

Do not try to rely on PostgreSQL-only helpers like:
- `SERIAL`
- `nextval(...)`
- `::regclass`
- `pgcrypto`

If you want a default password like `pass123` in MySQL, do it in the application or via a MySQL-compatible default strategy, not with PostgreSQL functions.

## Lowest-Risk Implementation Order

If this were being executed in the safest order, I would do it like this:

1. Create a MySQL clone of the schema.
2. Switch `config/database.php` to MySQL on a development copy.
3. Make `setup.php` MySQL-compatible.
4. Migrate seed data.
5. Validate all admin flows.
6. Validate all member/public flows.
7. Cut production over only after backup and verification.

## Rollback Plan

Before final cutover:
- keep the PostgreSQL database untouched
- export a fresh backup
- record current config values
- verify image uploads and authentication in MySQL

If anything fails after cutover:
- point `config/database.php` back to PostgreSQL
- restore the old DSN
- troubleshoot the MySQL schema separately

## Bottom Line

The easiest migration is a **schema-and-connection swap**, not an application rewrite.

The app is already well suited for this because:
- most SQL is simple,
- authentication is portable,
- uploads are file-based,
- and the domain model is small enough to migrate table by table.

The main work is converting schema bootstrap SQL and moving data carefully.
