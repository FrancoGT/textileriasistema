-- Run on the application database before enabling writes in ERP1030 (clients) and ERP1080 (catalog tables).
-- ALTER TABLE commits implicitly. Apply during a maintenance window with a backup.
-- E01TTAB is read by every screen through V_E01TTAB; the conversion locks it while it runs.
ALTER TABLE E01MCLI ENGINE = InnoDB;
ALTER TABLE E01TTAB ENGINE = InnoDB;
