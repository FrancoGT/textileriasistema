# Symfony migration boundaries

The shared legacy bridge now separates URL dispatch, script execution, variable
collection, response conversion, asset delivery and session compatibility. The
business modules still run in `legacy/`. They have not been rewritten as Symfony
controllers or Doctrine repositories.

## Current services

| Service | Responsibility |
| --- | --- |
| `LegacyController` | Dispatch a resolved page or asset. |
| `LegacyPathResolver` | Resolve allowed files within the legacy directory. |
| `LegacyPageExecutor` | Define the execution contract used by the controller. |
| `LegacyScriptRunner` | Adapt native PHP execution and restore process state. |
| `LegacyVariableCollector` | Identify variables required by legacy global functions. |
| `LegacyResponseFactory` | Convert captured content, status and headers to a Symfony response. |
| `LegacyAssetResponseFactory` | Set file MIME types, cache policy and conditional responses. |
| `LegacySession` | Clear identity keys used by the native session bridge. |

Services use constructor injection and the existing service discovery configuration.
Symfony resolves the executor interface from its single implementation. Native
session access stays in the compatibility adapter because legacy identity keys
are outside Symfony's isolated attribute bag.

The migration uses the Strangler Fig pattern: a Symfony route replaces one legacy
screen while the catch-all controller continues to serve the remaining screens.
The bridge is an Adapter. Response creation uses factories. These boundaries
allow controller tests to replace execution without loading a legacy module.

## Migrating a business module

1. Record current behavior: permissions, request fields, redirects, validation,
   session keys, database writes and report output. Add characterization tests.
2. Introduce a repository for the module's actual queries. Use the installed
   Doctrine DBAL connection and bound parameters. Keep the existing schema until
   a separate schema migration is tested.
3. Put the use case and transaction boundary in an application service. Introduce
   a repository interface when the application needs a replaceable persistence
   boundary. Keep HTTP requests and template rendering outside the use case.
4. Map input to a DTO and validate it. Check installed Serializer and Validator
   components before using `MapRequestPayload`; neither is currently required
   by this application's `composer.json`. Use Symfony Form for browser forms
   when the module needs form binding and CSRF protection.
5. Add a thin controller with route attributes and Twig templates. Preserve the
   existing URL and the shared header, navigation and mobile behavior.
6. Test access denial and data persistence as well as successful rendering.
   Replace the route only after these checks pass. Remove the replaced legacy
   implementation only after all callers have migrated.

Authentication and permissions must share identity with remaining legacy screens
until their callers migrate. Do not replace a permission check with a generic
"logged in" check. Do not introduce a generic CRUD layer for modules with
different business rules. Add Messenger, Workflow or caching only for a measured
or documented requirement.

## Verification

Run the bridge checks with PHP 8.4 and the project's installed extensions:

```console
symfony php tests/php/legacy.php
symfony console lint:container
symfony console lint:twig templates/
node --test tests/*.test.cjs
node --test tests/workspace.browser.cjs
```

If the application runs exclusively in Docker, run the PHP commands inside the
existing app service, for example `docker compose exec -T app php tests/php/legacy.php`.
The bridge checks do not connect to the database. The browser checks use actual
templates with fixture data. They do not test legacy business operations.

PHP and container checks were not executed during this refactor because the
available shell has no runnable PHP binary and access to Docker is denied.
Database integration and performance have not been measured.
