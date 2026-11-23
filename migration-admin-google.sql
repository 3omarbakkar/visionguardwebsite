-- Admin Google sign-in. Run ONCE in phpMyAdmin (database used by the site).
-- Not deployed: *.sql is excluded in .cpanel.yml / deploy-hook.php.
ALTER TABLE admin_users ADD COLUMN google_email VARCHAR(190) NULL UNIQUE;

-- Link the owner's Google account to the primary admin (id 1).
UPDATE admin_users SET google_email = '3omarbakkar@gmail.com' WHERE id = 1 AND role = 'admin';

-- Check: should return 1 row with active = 1 and role = 'admin'.
SELECT id, username, role, active, google_email FROM admin_users WHERE google_email = '3omarbakkar@gmail.com';
