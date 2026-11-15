# Interface adjustments

ERP-X uses the existing grey, orange, white and red palette for shared controls.
The menu retains its labels, order, icons and layout. The Aroca layout-d inventory
reference informed spacing, separators and table organization. No template assets
or dependencies were copied from Aroca.

## Scope

This review was made before the unused legacy files were deleted. The `Seg*`
templates, `Index` and `diego` no longer exist. See `docs/MIGRACION.md`.

The source inventory includes 130 Smarty templates and two Twig templates.
The inventory includes these interface families:

| Templates | Interfaces |
| --- | --- |
| ERP1000 through ERP1094 | Personnel, users, profiles, clients, machines, supplies, suppliers, products, catalogs, warehouses, banks and accounts |
| ERP2010, ERP2020, ERP3000 through ERP4070 | Orders, production queries and reports |
| ERP5010 through ERP9010 | Invoicing, receivables, payments, assigned work and production reports |
| Seg12xx through Seg15xx | Projects, companies, courses, standards, personnel, licenses and incident registration |
| Seg21xx through Seg26xx | Evaluations, incidents, attachments, action plans, courses and sanctions |
| Seg41xx through Seg80xx | Queries, reports, profiles and process administration |
| Seg90xx, Seg9901 | Search windows, incident queries and injury registration |
| Index, Mnu0000, Mnu1000 and security/login | Access, menu and password change |
| Seg1510a, diego | Contact dialog and empty placeholder |

This inventory is a source review, not a claim that every workflow was executed.
Ten templates contain local style blocks. These include fixed-width attachment
links, small legacy buttons and an empty style block. Shared selectors cover
control sizing, fixed headers, links, fieldsets and mobile containment.

## Shared implementation

- `InterfaceStylesListener` adds the shared stylesheet, script and missing mobile
  viewport to complete HTML responses. Downloads and partial responses are excluded.
- `ui-feedback.css` defines buttons, fields, form spacing, numeric columns,
  separators, notices, dialog surfaces and responsive action rows.
- `ui-feedback.js` decorates existing controls without replacing submit inputs.
  The decoration keeps button names and values in the request payload. Icons reuse
  the installed jQuery UI sprite. Selection controls receive accessible names.
- The existing menu adapter retains its search, pagination and selection behavior.
  Table scroll regions can receive keyboard focus.

Visual rules use screen media so printed reports retain their existing formatting.
Native alert and confirmation dialogs remain controlled by the browser.

## Verification

- Node tests cover login errors, retry, duplicate protection and menu behavior.
- Chrome fixtures cover Personnel, Supplies and Suppliers at 320, 375, 414 and
  1280 CSS pixels. Checks include action wrapping, local table scrolling, numeric
  alignment, icons, selection names, search, pagination across 25 fixture records,
  loading feedback and keyboard menu use.
- Symfony container and Twig lint pass. Legacy PHP checks pass.
- `tests/php/interface.php` verifies HTML asset inclusion and download exclusions.

Run the available checks:

```text
node --test tests/login.test.cjs tests/menu.test.cjs tests/menu-mobile.test.cjs tests/workspace.browser.cjs
docker compose exec -T app php bin/console lint:container
docker compose exec -T app php bin/console lint:twig templates
docker compose exec -T app php tests/php/legacy.php
docker compose exec -T app php tests/php/interface.php
```

Browser fixtures do not execute database business operations. Registration, edit,
deletion, permission-dependent views, real report downloads and every legacy
dialog still require an authenticated acceptance pass against disposable data.
Client-side search is tested. Full pagination across multiple real data pages
also remains an acceptance check.
