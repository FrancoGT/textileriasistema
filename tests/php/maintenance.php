<?php

declare(strict_types=1);

use App\Controller\MachineController;
use App\Controller\PersonnelController;
use App\Controller\SupplierController;
use App\Http\MaintenanceInput;
use App\Http\MaintenanceInputResolver;
use App\Legacy\LegacySession;
use App\Legacy\LegacyTemplateRenderer;
use App\Maintenance\MachineData;
use App\Maintenance\MachineService;
use App\Maintenance\PersonnelData;
use App\Maintenance\PersonnelService;
use App\Maintenance\SupplierData;
use App\Maintenance\SupplierService;
use App\Repository\CatalogRepository;
use App\Repository\DbalMachineRepository;
use App\Repository\DbalPersonnelRepository;
use App\Repository\DbalSupplierRepository;
use App\Repository\ModulePermissionRepository;
use App\Repository\TransactionalTables;
use App\Security\DatabaseModulePermission;
use Doctrine\DBAL\DriverManager;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

require dirname(__DIR__, 2).'/vendor/autoload.php';
(new Dotenv())->bootEnv(dirname(__DIR__, 2).'/.env');

$database = $_SERVER['ERP_TEST_DATABASE_NAME'] ?? $_ENV['ERP_TEST_DATABASE_NAME'] ?? getenv('ERP_TEST_DATABASE_NAME');
if (!\is_string($database) || !preg_match('/^[a-zA-Z0-9_]+_test$/D', $database)) {
    fwrite(STDERR, "Set ERP_TEST_DATABASE_NAME to a dedicated empty database ending in _test.\n");
    exit(2);
}
$env = static fn (string $name, string $default = ''): string => (string) ($_SERVER[$name] ?? $_ENV[$name] ?? (getenv($name) ?: $default));
$connection = DriverManager::getConnection([
    'driver' => 'pdo_mysql', 'host' => $env('DATABASE_HOST', '127.0.0.1'), 'port' => (int) $env('DATABASE_PORT', '3306'),
    'dbname' => $database, 'user' => $env('DATABASE_USER'), 'password' => $env('DATABASE_PASSWORD'), 'charset' => 'utf8mb4',
]);
if ([] !== $connection->createSchemaManager()->listTableNames()) {
    throw new RuntimeException('The test database must be empty. No existing tables will be changed.');
}
$same = static function (mixed $expected, mixed $actual): void {
    if ($expected !== $actual) {
        throw new RuntimeException('Expected '.var_export($expected, true).', got '.var_export($actual, true));
    }
};
$throws = static function (string $type, callable $action): void {
    try {
        $action();
    } catch (Throwable $exception) {
        if ($exception instanceof $type) {
            return;
        }
        throw $exception;
    }
    throw new RuntimeException('Expected exception '.$type);
};
$ownedTables = [];
$viewCreated = false;
$triggerCreated = false;
$failures = [];
$cases = [];
try {
    $dump = file_get_contents(dirname(__DIR__, 2).'/docker/mysql/init/01-textileria.sql');
    foreach (['E01MPER', 'E01MPRV', 'E01DPRV', 'E01TUSU', 'E01PPRF', 'E01MMAQ'] as $table) {
        if (!preg_match('/CREATE TABLE `'.preg_quote($table, '/').'` \([\s\S]*?\) ENGINE=[^;]+;/', $dump, $match)) {
            throw new RuntimeException('Missing schema for '.$table);
        }
        $connection->executeStatement(str_replace('ENGINE=MyISAM', 'ENGINE=InnoDB', $match[0]));
        $ownedTables[] = $table;
    }
    $connection->executeStatement("CREATE VIEW V_E01TTAB AS SELECT '001' AS cCodTab, 'A' AS cCodigo, 'Activo' AS cDescri UNION ALL SELECT '017', 'M', 'Masculino' UNION ALL SELECT '018', 'S', 'Soltero' UNION ALL SELECT '098', 'B', 'Empresa' UNION ALL SELECT '011', '01', 'Hilos' UNION ALL SELECT '011', '02', 'Tintes' UNION ALL SELECT '005', 'A1', 'Hiladora'");
    $viewCreated = true;
    foreach (['1000' => 'A', '2000' => 'B', '3000' => 'C'] as $user => $profile) {
        $connection->insert('E01TUSU', ['CCODUSU' => (string) $user, 'CPERFIL' => $profile]);
    }
    $connection->insert('E01PPRF', ['CCODOPC' => 'ERP1000', 'CPERFIL' => 'A', 'CESTADO' => 'A']);
    $connection->insert('E01PPRF', ['CCODOPC' => 'ERP1000', 'CPERFIL' => 'C', 'CESTADO' => 'I']);
    $connection->insert('E01PPRF', ['CCODOPC' => 'ERP1060', 'CPERFIL' => 'B', 'CESTADO' => 'A']);
    $connection->insert('E01PPRF', ['CCODOPC' => 'ERP1040', 'CPERFIL' => 'A', 'CESTADO' => 'A']);
    $tables = new TransactionalTables($connection);
    $permissions = new DatabaseModulePermission(new ModulePermissionRepository($connection));
    $personRepository = new DbalPersonnelRepository($connection, $tables);
    $supplierRepository = new DbalSupplierRepository($connection, $tables);
    $people = new PersonnelService($personRepository, $permissions);
    $suppliers = new SupplierService($supplierRepository, $permissions);
    $machines = new MachineService(new DbalMachineRepository($connection, $tables), new CatalogRepository($connection), $permissions);
    $person = new PersonnelData("O'NEIL", 'Av. Central', 'oneil@example.test', '12345678', 'M', 'S', 'A');
    $supplier = new SupplierData("O'REILLY SAC", 'Av. Sur', '', '12345678901', 'B', '2024-01-02', 'Initial notes', ['01']);
    $personCode = '';
    $supplierCode = '';

    $cases['permissions deny reads and writes without a matching active profile'] = static function () use ($throws, $people, $suppliers, $person, $supplier, $connection, $same): void {
        foreach (['', '2000', '3000', "1000' OR '1'='1"] as $user) {
            $throws(AccessDeniedHttpException::class, static fn () => $people->choices($user));
            $throws(AccessDeniedHttpException::class, static fn () => $people->save($user, null, $person));
            $throws(AccessDeniedHttpException::class, static fn () => $suppliers->all($user));
            $throws(AccessDeniedHttpException::class, static fn () => $suppliers->save($user, null, $supplier));
        }
        $same(0, (int) $connection->fetchOne('SELECT COUNT(*) FROM E01MPER'));
        $same(0, (int) $connection->fetchOne('SELECT COUNT(*) FROM E01MPRV'));
    };
    $cases['personnel create and update persist parameterized values and audit user'] = static function () use ($people, $person, &$personCode, $same, $connection): void {
        $personCode = $people->save('1000', null, $person);
        $same('000001', $personCode);
        $same("O'NEIL", $people->find('1000', $personCode)['name']);
        $updated = new PersonnelData('Updated name', "O'Brien Street", 'updated@example.test', '87654321', 'M', 'S', 'A');
        $people->save('1000', $personCode, $updated);
        $same("O'BRIEN STREET", $people->find('1000', $personCode)['address']);
        $same('1000', $connection->fetchOne('SELECT CCODUSU FROM E01MPER WHERE CCODPER = :code', ['code' => $personCode]));
    };
    $cases['validation and unknown identifiers do not write personnel data'] = static function () use ($people, $throws, $connection, $same): void {
        $throws(DomainException::class, static fn () => $people->save('1000', null, new PersonnelData('Name', 'Address', 'bad-email', '123', 'M', 'S', 'A')));
        $throws(DomainException::class, static fn () => $people->find('1000', "' OR 1=1 --"));
        $same(1, (int) $connection->fetchOne('SELECT COUNT(*) FROM E01MPER'));
    };
    $cases['deleted supplier codes do not collide with the next identifier'] = static function () use ($connection, $suppliers, $supplier, $same): void {
        $first = $suppliers->save('1000', null, $supplier);
        $second = $suppliers->save('1000', null, $supplier);
        $connection->delete('E01DPRV', ['cCodPrv' => $first]);
        $connection->delete('E01MPRV', ['cCodPrv' => $first]);
        $next = $suppliers->save('1000', null, $supplier);
        $same((int) $second + 1, (int) $next);
        foreach ([$second, $next] as $code) {
            $connection->delete('E01DPRV', ['cCodPrv' => $code]);
            $connection->delete('E01MPRV', ['cCodPrv' => $code]);
        }
    };
    $cases['supplier create and update preserve immutable identity and replace production lines'] = static function () use ($suppliers, $supplier, &$supplierCode, $same): void {
        $supplierCode = $suppliers->save('1000', null, $supplier);
        $same('000001', $supplierCode);
        $data = new SupplierData('Ignored identity', 'Updated address', '', '', '', '', 'Updated notes', ['02']);
        $suppliers->save('1000', $supplierCode, $data);
        $saved = $suppliers->find('1000', $supplierCode);
        $same("O'REILLY SAC", $saved['name']);
        $same('UPDATED ADDRESS', $saved['address']);
        $same(['02'], $saved['production_lines']);
    };
    $cases['natural suppliers retain DNI and use the existing RUC placeholder'] = static function () use ($suppliers, $same): void {
        $code = $suppliers->save('1000', null, new SupplierData('Natural supplier', 'Address', '12345678', '', 'A', '2024-01-02', '', []));
        $saved = $suppliers->find('1000', $code);
        $same('12345678', $saved['dni']);
        $same('99999999999', $saved['ruc']);
    };
    $cases['supplier failure rolls back header and all detail changes'] = static function () use ($connection, &$triggerCreated, $suppliers, &$supplierCode, $throws, $same): void {
        $before = $suppliers->find('1000', $supplierCode);
        $connection->executeStatement("CREATE TRIGGER fail_supplier_detail BEFORE INSERT ON E01DPRV FOR EACH ROW BEGIN IF NEW.cTipArs = '01' THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Test detail failure'; END IF; END");
        $triggerCreated = true;
        try {
            $throws(\Doctrine\DBAL\Exception::class, static fn () => $suppliers->save('1000', $supplierCode,
                new SupplierData('', 'Must roll back', '', '', '', '', 'Must roll back', ['02', '01'])));
            $same($before, $suppliers->find('1000', $supplierCode));
        } finally {
            $connection->executeStatement('DROP TRIGGER fail_supplier_detail');
            $triggerCreated = false;
        }
    };
    $cases['nontransactional tables reject writes before changing data'] = static function () use ($connection, $suppliers, &$supplierCode, $throws, $same): void {
        $before = $suppliers->find('1000', $supplierCode);
        $connection->executeStatement('ALTER TABLE E01MPRV ENGINE = MyISAM');
        try {
            $throws(RuntimeException::class, static fn () => $suppliers->save('1000', $supplierCode,
                new SupplierData('', 'Must not save', '', '', '', '', '', [])));
            $same($before, $suppliers->find('1000', $supplierCode));
        } finally {
            $connection->executeStatement('ALTER TABLE E01MPRV ENGINE = InnoDB');
        }
    };
    $cases['form mapping rejects arrays in scalar fields and deduplicates production lines'] = static function () use ($throws, $same): void {
        $resolver = new MaintenanceInputResolver();
        $argument = new ArgumentMetadata('input', MaintenanceInput::class, false, false, null);
        $throws(BadRequestHttpException::class, static fn () => iterator_to_array($resolver->resolve(Request::create('/', 'POST', ['pcNombre' => ['bad']]), $argument)));
        $result = iterator_to_array($resolver->resolve(Request::create('/', 'POST', ['Boton1' => 'Grabar', 'paTipArs' => ['01', '01']]), $argument));
        $same('Grabar', $result[0]->action);
        $same(['01'], $result[0]->productionLines);
    };
    $cases['controllers enforce login, permissions and session-bound form tokens'] = static function () use ($people, $suppliers, $connection, $throws, $same): void {
        $session = new LegacySession();
        $views = new LegacyTemplateRenderer(dirname(__DIR__, 2).'/legacy');
        $catalogs = new CatalogRepository($connection);
        $personnel = new PersonnelController($people, $catalogs, $session, $views);
        $providers = new SupplierController($suppliers, $catalogs, $session, $views);
        $input = new MaintenanceInput('', [], []);
        $session->logout();
        $same('/index.php', $personnel(Request::create('/ERP1000.php'), $input)->headers->get('Location'));
        $session->set('gcCodUsu', '2000');
        $throws(AccessDeniedHttpException::class, static fn () => $providers(Request::create('/ERP1060.php'), $input));
        $session->set('gcCodUsu', '1000');
        $same(200, $personnel(Request::create('/ERP1000.php'), $input)->getStatusCode());
        $same(200, $providers(Request::create('/ERP1060.php'), $input)->getStatusCode());
        $throws(AccessDeniedHttpException::class, static fn () => $personnel(Request::create('/ERP1000.php', 'POST'), new MaintenanceInput('Nuevo', [], [])));
        $valid = new MaintenanceInput('Nuevo', ['_token' => $session->token('personnel')], []);
        $same(200, $personnel(Request::create('/ERP1000.php', 'POST'), $valid)->getStatusCode());
        $same(['code' => null], $session->get('_maintenance_personnel_edit'));
        $tampered = new MaintenanceInput('Grabar', ['_token' => $session->token('personnel'), 'pcCodPer' => '000999'], []);
        $same(422, $personnel(Request::create('/ERP1000.php', 'POST'), $tampered)->getStatusCode());
        $same(false, $session->verifyToken('personnel', $session->token('suppliers')));
        $session->logout();
    };
    $cases['machine permissions deny reads and writes without ERP1040'] = static function () use ($throws, $machines, $connection, $same): void {
        foreach (['', '2000', '3000'] as $user) {
            $throws(AccessDeniedHttpException::class, static fn () => $machines->choices($user));
            $throws(AccessDeniedHttpException::class, static fn () => $machines->save($user, null, new MachineData('Cortadora', 'A', 'A')));
        }
        $same(0, (int) $connection->fetchOne('SELECT COUNT(*) FROM E01MMAQ'));
    };
    $cases['machine create and update store uppercase values, initial flag and audit user'] = static function () use ($machines, $connection, $same): void {
        $code = $machines->save('1000', null, new MachineData("o'brien cortadora", 'A', 'A'));
        $same('000001', $code);
        $same(['code' => '000001', 'description' => "O'BRIEN CORTADORA", 'status' => 'A', 'type' => 'A'], $machines->find('1000', $code));
        $same('L', $connection->fetchOne('SELECT cFlag FROM E01MMAQ WHERE cCodMaq = :code', ['code' => $code]));
        $machines->save('1000', $code, new MachineData('Hiladora', 'A', 'A'));
        $same('HILADORA', $machines->find('1000', $code)['description']);
        $same([['000001', 'HILADORA']], $machines->choices('1000'));
        $same('1000', $connection->fetchOne('SELECT cCodUsu FROM E01MMAQ WHERE cCodMaq = :code', ['code' => $code]));
    };
    $cases['machine validation rejects unknown catalog values, long names and missing codes'] = static function () use ($machines, $throws, $connection, $same): void {
        $throws(DomainException::class, static fn () => $machines->save('1000', null, new MachineData('', 'A', 'A')));
        $throws(DomainException::class, static fn () => $machines->save('1000', null, new MachineData(str_repeat('X', 51), 'A', 'A')));
        $throws(DomainException::class, static fn () => $machines->save('1000', null, new MachineData('Name', 'Z', 'A')));
        $throws(DomainException::class, static fn () => $machines->save('1000', null, new MachineData('Name', 'A', 'Z')));
        $throws(DomainException::class, static fn () => $machines->save('1000', '999999', new MachineData('Name', 'A', 'A')));
        $throws(DomainException::class, static fn () => $machines->find('1000', "' OR 1=1 --"));
        $same(1, (int) $connection->fetchOne('SELECT COUNT(*) FROM E01MMAQ'));
    };
    $cases['machine controller enforces tokens and saves only the opened record'] = static function () use ($machines, $throws, $same, $connection): void {
        $session = new LegacySession();
        $controller = new MachineController($machines, $session, new LegacyTemplateRenderer(dirname(__DIR__, 2).'/legacy'));
        $session->logout();
        $same('/index.php', $controller(Request::create('/ERP1040.php'), new MaintenanceInput('', [], []))->headers->get('Location'));
        $session->set('gcCodUsu', '2000');
        $throws(AccessDeniedHttpException::class, static fn () => $controller(Request::create('/ERP1040.php'), new MaintenanceInput('', [], [])));
        $session->set('gcCodUsu', '1000');
        $same(200, $controller(Request::create('/ERP1040.php'), new MaintenanceInput('', [], []))->getStatusCode());
        $throws(AccessDeniedHttpException::class, static fn () => $controller(Request::create('/ERP1040.php', 'POST'), new MaintenanceInput('Nuevo', [], [])));
        $token = $session->token('machines');
        $same(200, $controller(Request::create('/ERP1040.php', 'POST'), new MaintenanceInput('Aplicar', ['_token' => $token, 'pcCodMaq' => '000001'], []))->getStatusCode());
        $tampered = new MaintenanceInput('Grabar', ['_token' => $token, 'pcCodMaq' => '000999', 'pcNomMaq' => 'X', 'pcEstado' => 'A', 'pcTipo' => 'A'], []);
        $same(422, $controller(Request::create('/ERP1040.php', 'POST'), $tampered)->getStatusCode());
        $valid = new MaintenanceInput('Grabar', ['_token' => $token, 'pcCodMaq' => '000001', 'pcNomMaq' => 'Tejedora', 'pcEstado' => 'A', 'pcTipo' => 'A'], []);
        $same(303, $controller(Request::create('/ERP1040.php', 'POST'), $valid)->getStatusCode());
        $same('TEJEDORA', $connection->fetchOne("SELECT cDescri FROM E01MMAQ WHERE cCodMaq = '000001'"));
        $same(422, $controller(Request::create('/ERP1040.php', 'POST'), $valid)->getStatusCode());
        $session->logout();
        $same(null, $session->get('_maintenance_machines_edit'));
    };
    foreach ($cases as $name => $case) {
        try {
            $case();
        } catch (Throwable $exception) {
            $failures[] = $name.': '.$exception->getMessage();
        }
    }
} finally {
    if ($triggerCreated) {
        $connection->executeStatement('DROP TRIGGER fail_supplier_detail');
    }
    if ($viewCreated) {
        $connection->executeStatement('DROP VIEW V_E01TTAB');
    }
    foreach (array_reverse($ownedTables) as $table) {
        $connection->executeStatement('DROP TABLE '.$table);
    }
    $connection->close();
}
foreach ($failures as $failure) {
    fwrite(STDERR, $failure."\n");
}
echo \count($cases).' checks, '.\count($failures)." failures.\n";
exit([] === $failures ? 0 : 1);
