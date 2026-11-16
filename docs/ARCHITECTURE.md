# Architecture

Every screen of the ERP is a Symfony route. The URLs of the original application
(`/ERP2010.php`, `/Mnu0000.php`...) are kept, so the menu stored in the database
still works. The `legacy/` directory, the catch-all `LegacyController`, Smarty,
PhpSpreadsheet and the `mysqli` extension were removed on 2026-11-15. Git history
keeps the old code.

## Layers

| Layer | Location | Responsibility |
| --- | --- | --- |
| Controller | `src/Controller` | Map the request to an action, check the form token, redirect after a save (303), render the view. |
| Input | `src/Http` | `MaintenanceInputResolver` turns the Spanish field and button names into a `MaintenanceInput` (action, button, fields, grids). |
| Session | `src/Session` | `ErpSession` reads the user from the native PHP session. `MaintenanceFormState` keeps the token, the opened record, the draft and the flash message of one screen. |
| Use case | `src/Maintenance`, `src/Sales`, `src/Production`, `src/Finance`, `src/Dispatch`, `src/Inventory`, `src/Security` | Check the module permission (`ModulePermission::assertGranted`), validate business fields, throw `\DomainException` with the message for the user. |
| Persistence | `src/Repository` | One interface per use case and one `Dbal*` implementation. SQL uses bound parameters. Writes run in a transaction and lock the rows they read (`FOR UPDATE`). `TransactionalTables::requireInnoDb` refuses to write to a MyISAM table. |
| Documents | `src/Report` | `OrderReportPdf` (FPDF) and `OrderDocumentRtf` (template `templates/documents/order.rtf`). |
| View | `src/View`, `templates/erp` | `ErpView` renders `templates/erp/<screen>.html.twig`. Screens that differ only in their filter share one template. |

A controller catches `\DomainException` and shows its message with status 422.
Repositories do not know about HTTP, and services do not know about templates.

## Modules

| Screens | Controller | Service |
| --- | --- | --- |
| ERP1000 to ERP1094 | one controller per maintenance screen | `src/Maintenance`, `src/Security`, `src/Inventory` |
| ERP2010 | `SalesOrderController` | `SalesOrderService` |
| ERP2020, `/{screen}/{order}.rtf` | `OrderDocumentController` | `OrderDocumentService` |
| ERP3000, ERP3010, ERP3020 | `PlanningController` | `PlanningService` |
| ERP4000, ERP4060, ERP4070 and ERP4010 to ERP4050 | `ProductionController` | `ProductionService` |
| ERP5010 | `PurchaseController` | `PurchaseService` |
| ERP6010, ERP6020, ERP6030 | `ReceivableController` | `ReceivableService` |
| ERP7010, ERP7020 | `PayableController` | `PayableService` |
| ERP8000 | `WorkController` | `WorkService` |
| ERP9010, `/ERP9010/{order}.pdf` | `DispatchController` | `DispatchService` |

`Boards`, `ReceivableBoard` and `PayableBoard` describe the screens that list the
same records with another filter, permission or title.

## Changes from the original screens

These changes fix defects of the original code. They change what users see.

- ERP2010 lists companies by RUC. The original list showed the DNI column.
- The order document prints the DNI for a natural person and the RUC for a company.
  The original document always printed the RUC.
- ERP4050 lists the lines in the Finishing stage (`CPROCESO = 'E'`) and checks the
  ERP4050 permission. Profiles A and D have both ERP4030 and ERP4050.
- ERP8000 shows the machine of the job. The original screen showed the first machine of `E01MMAQ`.
- ERP5010 saves the invoice (`E01CCOM`, `E02DCOM`). The original screen saved nothing.
- ERP6010 to ERP6030 take the order total from the database and save the due date.
  The original screens took the total from the form and ignored the date. An order
  with installments cannot get a second plan.
- ERP7010 and ERP7020 add up the lines of each invoice. The original amount added up every purchase.
- ERP9010 checks the ERP9010 permission. Migration `Version20261115000001` creates
  the option and grants it to profile A. The original check was commented out.

## Verification

```console
docker compose exec -T app php bin/console lint:container
docker compose exec -T app php bin/console lint:twig templates
docker compose exec -T app php tests/php/interface.php
docker compose exec -T -e ERP_TEST_DATABASE_NAME=textileria_test app php tests/php/maintenance.php
docker compose exec -T -e ERP_TEST_DATABASE_NAME=textileria_test app php tests/php/business.php
node --test tests/
node --test tests/workspace.browser.cjs
```

The database suites need an empty database whose name ends in `_test`. Create it again before each suite:

```console
docker compose exec -T db mariadb -uroot -proot -e "DROP DATABASE IF EXISTS textileria_test; CREATE DATABASE textileria_test CHARACTER SET utf8mb4;"
```

`tests/php/fixtures/order-document.rtf` was produced by the original RTF code.
`business.php` checks that `OrderDocumentRtf` produces the same output.

On 2026-11-15 these checks passed in the Docker `app` service with PHP 8.4 and
MariaDB 10.11: interface 9, maintenance 48, business 14, Node 10. Performance has not been measured.
