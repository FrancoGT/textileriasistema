# Personnel and supplier business migration

`/ERP1000.php` and `/ERP1060.php` now resolve to Symfony controllers. The controllers
use `PersonnelService` and `SupplierService`; the services enforce module access
and business validation before calling DBAL repositories. SQL uses bound
parameters. The existing Smarty views remain in use through a rendering adapter.
The legacy implementations remain because other screens still share their classes.

The project-wide migration is incomplete. See [the script inventory](MODULE-MIGRATION.md)
for the remaining entry points. PHP, container and database checks have not run in
the current execution environment. These routes are implemented but not validated
for deployment. There is no measured performance improvement.

## Persistence

The original `E01MPER`, `E01MPRV` and `E01DPRV` tables use MyISAM. A transaction
cannot roll back writes to these tables. The repositories reject writes until
all tables needed by the operation use InnoDB.

Apply [001-maintenance-transactions.sql](migrations/001-maintenance-transactions.sql)
to the target database before deploying these routes. Clients (ERP1030) and
catalog tables (ERP1080) also need
[002-catalog-client-transactions.sql](migrations/002-catalog-client-transactions.sql),
because `E01MCLI` and `E01TTAB` use MyISAM. Take a backup and schedule
the conversion because `ALTER TABLE` can lock tables and commits implicitly.
The conversion script has not been applied by this change. It does not change
column definitions, character sets or existing data.

Changes from the legacy screens in ERP1030 to ERP1094:

- New codes for clients, supplies and banks use the highest existing code. The
  legacy clients and supplies screens used `COUNT(*) + 1`, and the legacy banks
  screen produced the same code for every new bank.
- Catalog table saves update existing rows and insert new rows. The legacy
  screen deleted and inserted all rows, which removed `mTexto` (for example the
  295 CIIU texts of table 099). A save is rejected when another user changed
  the table after it was opened.
- Supply edits update the selected supply. The legacy update filtered on a
  column that does not exist. Supply deletion removes the row from `E01MINS` and
  is refused while `E01DCOM`, `E01DPOG` or a foreign key references the code.
  The legacy deletion targeted the missing table `E01MARS`.
- Client edits keep type, name, DNI and RUC, which the form shows read-only.
- Bank account registration stores the audit user and rejects duplicates.

Supplier saves include the header and replacement production lines in one
transaction. New supplier codes use the highest existing code rather than row
count, so deleted rows do not cause code reuse. Code selection locks the last
eligible row within the transaction. A database deadlock aborts the save; it is
not converted into a successful response.

Supplier edits preserve name, DNI, RUC, person type and date. Only address, notes,
audit user and production lines change, as in the previous entry point.
Invalid dates on new suppliers and invalid DNI/RUC values are rejected instead
of relying on permissive database coercion. Personnel email is limited to the
actual 50-character column. Existing supplier dates are not rewritten.

## Permissions and forms

The permission query preserves the existing active profile membership rule.
Proveedores continues to require `ERP1000`, because the old `ERP1060.php` uses
that permission. Changing permission assignment is a separate business decision.
Every service operation checks access, including writes after the form is opened.
An absent session redirects to the existing login. A denied permission returns 403.

The installed HttpKernel `MapRequestPayload` attribute targets a form resolver
for the existing Spanish field names and submit buttons. The resolver checks
payload shape; application services validate business fields. This avoids a
dependency on Serializer and Validator, which are not installed in this project.
Readonly DTO classes are excluded from service discovery.

The native session adapter owns the compatibility form tokens and edit state.
The installed packages do not include Security CSRF. Tokens use `random_bytes`
and `hash_equals`, are scoped to the module and are cleared on logout. Move token
management to Symfony Security CSRF when authentication is migrated and the
component is installed. A successful save redirects with 303 to avoid repeated
submissions on reload. Failed validation returns 422 and keeps submitted values.

## Verification commands

Provision a separate **empty** MariaDB database with a name ending in `_test`.
Grant the test database user permission to create tables, a view and a trigger
in that database. The test refuses an application database name or a nonempty
database. It creates only test tables and removes the objects it created.
Use the same MariaDB version as the application.

```powershell
symfony php tests/php/legacy.php
$env:ERP_TEST_DATABASE_NAME = 'textileria_migration_test'
symfony php tests/php/maintenance.php
symfony console lint:container
symfony console lint:twig templates/
symfony console debug:router
node --test tests/*.test.cjs
node --test tests/workspace.browser.cjs
```

For a Docker-only setup with a separately provisioned empty test database:

```console
docker compose exec -T -e ERP_TEST_DATABASE_NAME=textileria_migration_test app php tests/php/maintenance.php
docker compose exec -T app php tests/php/legacy.php
docker compose exec -T app php bin/console lint:container
```

The database checks cover create/update persistence, quoted input, audit user,
validation without writes, active and inactive profile permissions, the existing
supplier permission alias, rollback on a forced detail insert failure, MyISAM
write rejection, input shape, login redirects and session-bound form tokens.
The schema is read from the existing initialization SQL, without importing
production or seed customer data. The browser checks use fixture data and cover
layout and navigation; they do not replace database or kernel checks.
