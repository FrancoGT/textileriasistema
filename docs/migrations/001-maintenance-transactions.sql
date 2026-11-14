-- Run on the application database before enabling writes in the migrated modules.
-- ALTER TABLE commits implicitly. Apply during a maintenance window with a backup.
ALTER TABLE E01MPER ENGINE = InnoDB;
ALTER TABLE E01MPRV ENGINE = InnoDB;
ALTER TABLE E01DPRV ENGINE = InnoDB;
