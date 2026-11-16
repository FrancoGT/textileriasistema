# Migration to Symfony 8.2 and PHP 8.4.27

## Summary

| | Before (`public_html/`) | Now |
|---|---|---|
| PHP | 5.3 (shared hosting) | **8.4.27** |
| Framework | None (separate scripts) | **Symfony 8.2** |
| Templates | Smarty 3.1.8 | **Twig** |
| Documents | PHPExcel, ezpdf, RTF classes | **FPDF** (`setasign/fpdf`) and an RTF template |
| Database | MariaDB 10.0, credentials in the code | MariaDB **10.11**, credentials in the `DATABASE_*` variables |
| Database access | `mysqli` with SQL built from `$_REQUEST` | **Doctrine DBAL 4** with bound parameters |
| Deployment | FTP | **Docker** (`Dockerfile`, `compose.yaml`, `compose.prod.yaml`) |

> Symfony 8.2 is a beta release (8.2.0-BETA2, 2026-11-13). `composer.json` uses
> `minimum-stability: beta` and `prefer-stable: true`. When 8.2.0 is released, run
> `docker compose exec app composer update "symfony/*"`, then remove `minimum-stability`.

## How the migration ran

The migration followed the Strangler Fig pattern. A catch-all `LegacyController`
ran the old scripts of `legacy/` while Symfony routes replaced the screens one by
one. Each Symfony route keeps the URL of the old script. When the last screen had a
route, `legacy/`, the bridge classes, Smarty, PhpSpreadsheet and the `mysqli`
extension were removed (2026-11-15).

[ARCHITECTURE.md](ARCHITECTURE.md) describes the current code and the changes in
behavior. [MAINTENANCE-MIGRATION.md](MAINTENANCE-MIGRATION.md) describes the
maintenance screens, the login and the InnoDB requirement.

## Removed code

- `public_html/`, the copy of the original code.
- `legacy/`, the original code adapted to PHP 8.4. This includes the `Seg*`
  subsystem: the database menu does not link it, and its `S02*` tables do not exist.
- `legacy/FILES/` and `legacy/Ficheros/`, the folders of uploaded and generated files.
  No screen reads them. The production volumes `app_ficheros` and `app_files` are no
  longer mounted. Docker keeps an existing volume until `docker volume rm` deletes it.

Git history keeps all these files.

## Database

`db/textileria.sql` quoted identifiers with single quotes, which is invalid SQL, and
had a `DEFINER` of the old hosting. `docker/mysql/convert_dump.py` writes
`docker/mysql/init/01-textileria.sql`, which MariaDB 10.11 accepts.

The passwords in `E01TUSU` are stored with `password_hash()` (bcrypt) since migration
`Version20261114000003`. The login still accepts a password in plain text, for
example from an old backup, and replaces it with a hash when the user logs in.
