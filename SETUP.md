# Local setup

1. Create the database with `database.sql`.
2. For an older database, run the migrations in this order: `database_migration_profile.sql`, `database_migration_inquiries.sql`, `database_migration_system.sql`, then `database_migration_dashboard.sql`.
3. Set `DB_HOST`, `DB_NAME`, `DB_USER`, and `DB_PASSWORD` in local environment configuration if the defaults in `db.php` do not match XAMPP.
4. Create the first administrator with a password chosen by the developer. Generate its hash locally:

   ```powershell
   php -r "echo password_hash('choose-a-strong-local-password', PASSWORD_DEFAULT), PHP_EOL;"
   ```

   Then insert it in phpMyAdmin, replacing the placeholders:

   ```sql
   INSERT INTO admin_users (email, password_hash, full_name, role)
   VALUES ('your-admin@example.test', 'PASTE_GENERATED_HASH_HERE', 'Local Administrator', 'super_admin');
   ```

No production or shared default password is committed. Existing installations with the previous sample administrator should rotate that password manually.
