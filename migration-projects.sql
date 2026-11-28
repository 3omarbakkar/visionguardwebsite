-- Vision Guard CRM project stages migration (site survey -> quote -> install)
-- Run once in phpMyAdmin against the website database, after taking a backup.
-- Only adds empty, nullable columns to `leads`; nothing existing is changed or removed.
-- Until this runs, the admin hides the project-stage features and everything else works as before.
ALTER TABLE leads
  ADD COLUMN project_stage VARCHAR(12) NULL DEFAULT NULL,
  ADD COLUMN quote_value   DECIMAL(12,2) NULL DEFAULT NULL,
  ADD INDEX idx_leads_project_stage (project_stage);

-- project_stage: NULL (not started), 'survey' (site visit), 'quote' (quote sent), 'install' (installation), 'done' (installed)
-- quote_value:   quoted amount in EGP, summed per stage on the dashboard
