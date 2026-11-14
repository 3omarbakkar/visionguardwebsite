-- Vision Guard payment metadata migration
-- Run once in phpMyAdmin against the website database, after taking a backup.
ALTER TABLE leads
  ADD COLUMN payment_method VARCHAR(20) NULL AFTER customer_id,
  ADD COLUMN payment_status VARCHAR(24) NOT NULL DEFAULT 'unverified' AFTER payment_method;

-- New orders will be assigned by api/lead.php:
-- cod       -> cod_due
-- instapay  -> pending (admin manually confirms paid)
-- ewallet   -> pending (admin manually confirms paid)
