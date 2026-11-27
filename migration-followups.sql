-- Vision Guard CRM follow-ups migration
-- Run once in phpMyAdmin against the website database, after taking a backup.
-- Only adds empty, nullable columns to `leads`; nothing existing is changed or removed.
-- Until this runs, the admin hides the follow-up features and everything else works as before.
ALTER TABLE leads
  ADD COLUMN followup_at   DATETIME NULL DEFAULT NULL,
  ADD COLUMN followup_note VARCHAR(500) NULL DEFAULT NULL,
  ADD COLUMN contacted_at  DATETIME NULL DEFAULT NULL,
  ADD INDEX idx_leads_followup (followup_at);

-- followup_at:   when to call / message the lead next (shown on the dashboard on that day, and after if missed)
-- followup_note: short note about the lead ("wants 8 cameras, call after Friday")
-- contacted_at:  set automatically the first time a lead leaves the "new" status; used for average response time
