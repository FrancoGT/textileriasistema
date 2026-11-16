<?php

declare(strict_types=1);

use App\Controller\BankAccountController;
use App\Controller\BankController;
use App\Controller\CatalogTableController;
use App\Controller\ClientController;
use App\Controller\MachineController;
use App\Controller\PersonnelController;
use App\Controller\ProductController;
use App\Controller\ProfileController;
use App\Controller\StockTransferController;
use App\Controller\UserController;
use App\Inventory\StockTransferService;
use App\Controller\SupplierController;
use App\Controller\SupplyController;
use App\Controller\WarehouseController;
use App\Http\MaintenanceInput;
use App\Http\MaintenanceInputResolver;
use App\Session\ErpSession;
use App\View\ErpView;
use App\Maintenance\AccountOwner;
use App\Maintenance\BankAccountService;
use App\Maintenance\BankData;
use App\Maintenance\BankService;
use App\Maintenance\CatalogEntry;
use App\Maintenance\CatalogTableService;
use App\Maintenance\ClientData;
use App\Maintenance\ClientService;
use App\Maintenance\ClientType;
use App\Maintenance\MachineData;
use App\Maintenance\MachineService;
use App\Maintenance\PersonnelData;
use App\Maintenance\PersonnelService;
use App\Maintenance\ProductService;
use App\Maintenance\SupplierData;
use App\Maintenance\SupplierService;
use App\Maintenance\SupplyData;
use App\Maintenance\SupplyService;
use App\Maintenance\WarehouseData;
use App\Maintenance\WarehouseService;
use App\Repository\CatalogRepository;
use App\Repository\DbalBankAccountRepository;
use App\Repository\DbalBankRepository;
use App\Repository\DbalCatalogTableRepository;
use App\Repository\DbalClientRepository;
use App\Repository\DbalMachineRepository;
use App\Repository\DbalPersonnelRepository;
use App\Repository\DbalProductRepository;
use App\Repository\DbalProfileRepository;
use App\Repository\DbalStockRepository;
use App\Repository\DbalUserRepository;
use App\Repository\DbalSupplierRepository;
use App\Repository\DbalSupplyRepository;
use App\Repository\DbalWarehouseRepository;
use App\Repository\ModulePermissionRepository;
use App\Repository\TransactionalTables;
use App\Security\DatabaseModulePermission;
use App\Security\ProfileService;
use App\Security\UserService;
use App\Controller\LoginController;
use App\Controller\PasswordChangeController;
use App\Migrations\Version20261114000001;
use App\Migrations\Version20261114000002;
use App\Migrations\Version20261114000003;
use App\Repository\DbalCredentialRepository;
use App\Security\LoginService;
use App\Security\UserPasswordHasher;
use Doctrine\DBAL\Schema\Schema;
use Psr\Log\NullLogger;
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
$usageTableCreated = false;
$failures = [];
$cases = [];
try {
    $dump = file_get_contents(dirname(__DIR__, 2).'/docker/mysql/init/01-textileria.sql');
    foreach (['E01MPER', 'E01MPRV', 'E01DPRV', 'E01TUSU', 'E01PPRF', 'E01MMAQ', 'E01MCLI', 'E01MINS', 'E01DCOM', 'E01DPOG', 'E01TTAB', 'E01MALM', 'E01MBAN', 'E01PCTA', 'E01PALM', 'E01MPROD', 'E01PPROD', 'E01TOPC'] as $table) {
        if (!preg_match('/CREATE TABLE `'.preg_quote($table, '/').'` \([\s\S]*?\) ENGINE=[^;]+;/', $dump, $match)) {
            throw new RuntimeException('Missing schema for '.$table);
        }
        // The migrations convert their own tables, so those keep the engine of the dump.
        $connection->executeStatement(in_array($table, Version20261114000001::TABLES, true) ? $match[0] : str_replace('ENGINE=MyISAM', 'ENGINE=InnoDB', $match[0]));
        $ownedTables[] = $table;
    }
    $connection->executeStatement("CREATE VIEW V_E01TTAB AS SELECT '001' AS cCodTab, 'A' AS cCodigo, 'Activo' AS cDescri UNION ALL SELECT '017', 'M', 'Masculino' UNION ALL SELECT '018', 'S', 'Soltero' UNION ALL SELECT '098', 'B', 'Empresa' UNION ALL SELECT '011', '01', 'Hilos' UNION ALL SELECT '011', '02', 'Tintes' UNION ALL SELECT '005', 'A1', 'Hiladora' UNION ALL SELECT '098', 'A', 'Natural' UNION ALL SELECT '098', 'C', 'Empresa' UNION ALL SELECT '002', 'A', 'Alta' UNION ALL SELECT '099', '1721', 'Tejidos' UNION ALL SELECT '007', 'A', 'Bolsas' UNION ALL SELECT '003', 'I', 'Insumos' UNION ALL SELECT '013', 'AB', 'Lima' UNION ALL SELECT '049', 'A', 'Administrador' UNION ALL SELECT '049', 'B', 'Pedidos' UNION ALL SELECT '004', 'A', 'Con receta'");
    $viewCreated = true;
    // Before the migrations: the dump keeps MyISAM and the 36-character password column.
    $passwords = new UserPasswordHasher();
    $narrowCredentials = new DbalCredentialRepository($connection);
    $connection->insert('E01TUSU', ['CCODUSU' => '4000', 'CCODPER' => '004000', 'CESTADO' => 'A', 'CCLAVE' => 'Clave.Antigua']);
    $connection->insert('E01TUSU', ['CCODUSU' => '4001', 'CESTADO' => 'I', 'CCLAVE' => 'secreta1', 'DFECFIN' => '2014-12-04']);
    $same(false, $narrowCredentials->canStoreHashes());
    $same('4000', (new LoginService($narrowCredentials, $passwords, new NullLogger()))->authenticate('4000', 'Clave.Antigua')['code']);
    $same('Clave.Antigua', $connection->fetchOne("SELECT CCLAVE FROM E01TUSU WHERE CCODUSU = '4000'"));
    $migrate = static function () use ($connection): int {
        $statements = 0;
        foreach ([Version20261114000001::class, Version20261114000002::class, Version20261114000003::class] as $class) {
            $migration = new $class($connection, new NullLogger());
            $migration->up(new Schema());
            foreach ($migration->getSql() as $query) {
                $connection->executeStatement($query->getStatement(), $query->getParameters(), $query->getTypes());
                ++$statements;
            }
        }

        return $statements;
    };
    $passwordColumn = $connection->fetchNumeric("SELECT CHARACTER_SET_NAME, COLLATION_NAME, CHARACTER_MAXIMUM_LENGTH FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND UPPER(TABLE_NAME) = 'E01TUSU' AND UPPER(COLUMN_NAME) = 'CCLAVE'");
    $migrate();
    $login = new LoginService(new DbalCredentialRepository($connection), $passwords, new NullLogger());
    foreach (['1000' => 'A', '2000' => 'B', '3000' => 'C'] as $user => $profile) {
        $connection->insert('E01TUSU', ['CCODUSU' => (string) $user, 'CPERFIL' => $profile]);
    }
    $connection->insert('E01PPRF', ['CCODOPC' => 'ERP1000', 'CPERFIL' => 'A', 'CESTADO' => 'A']);
    $connection->insert('E01PPRF', ['CCODOPC' => 'ERP1000', 'CPERFIL' => 'C', 'CESTADO' => 'I']);
    $connection->insert('E01PPRF', ['CCODOPC' => 'ERP1060', 'CPERFIL' => 'B', 'CESTADO' => 'A']);
    $connection->insert('E01PPRF', ['CCODOPC' => 'ERP1040', 'CPERFIL' => 'A', 'CESTADO' => 'A']);
    $connection->insert('E01PPRF', ['CCODOPC' => 'ERP1030', 'CPERFIL' => 'A', 'CESTADO' => 'A']);
    $connection->insert('E01PPRF', ['CCODOPC' => 'ERP1050', 'CPERFIL' => 'A', 'CESTADO' => 'A']);
    $connection->insert('E01PPRF', ['CCODOPC' => 'ERP1080', 'CPERFIL' => 'A', 'CESTADO' => 'A']);
    $connection->insert('E01PPRF', ['CCODOPC' => 'ERP1090', 'CPERFIL' => 'A', 'CESTADO' => 'A']);
    $connection->insert('E01PPRF', ['CCODOPC' => 'ERP1093', 'CPERFIL' => 'A', 'CESTADO' => 'A']);
    $connection->insert('E01PPRF', ['CCODOPC' => 'ERP1094', 'CPERFIL' => 'A', 'CESTADO' => 'A']);
    foreach (['ERP1091', 'ERP1070', 'ERP1010', 'ERP1020'] as $option) {
        $connection->insert('E01PPRF', ['CCODOPC' => $option, 'CPERFIL' => 'A', 'CESTADO' => 'A']);
    }
    $tables = new TransactionalTables($connection);
    $permissions = new DatabaseModulePermission(new ModulePermissionRepository($connection));
    $personRepository = new DbalPersonnelRepository($connection, $tables);
    $supplierRepository = new DbalSupplierRepository($connection, $tables);
    $people = new PersonnelService($personRepository, $permissions);
    $suppliers = new SupplierService($supplierRepository, $permissions);
    $clients = new ClientService(new DbalClientRepository($connection, $tables), new CatalogRepository($connection), $permissions);
    $supplies = new SupplyService(new DbalSupplyRepository($connection, $tables), new CatalogRepository($connection), $permissions);
    $catalogTables = new CatalogTableService(new DbalCatalogTableRepository($connection, $tables), $permissions);
    $warehouses = new WarehouseService(new DbalWarehouseRepository($connection, $tables), new CatalogRepository($connection), $permissions);
    $banks = new BankService(new DbalBankRepository($connection, $tables), new CatalogRepository($connection), $permissions);
    $bankAccounts = new BankAccountService(new DbalBankAccountRepository($connection, $tables), $permissions);
    $stock = new DbalStockRepository($connection, $tables);
    $transfers = new StockTransferService($stock, $permissions);
    $products = new ProductService(new DbalProductRepository($connection, $tables), $stock, new CatalogRepository($connection), $permissions);
    $users = new UserService(new DbalUserRepository($connection, $tables, new DbalCredentialRepository($connection), $passwords), new CatalogRepository($connection), $permissions);
    $profiles = new ProfileService(new DbalProfileRepository($connection, $tables), new CatalogRepository($connection), $permissions);
    $machines = new MachineService(new DbalMachineRepository($connection, $tables), new CatalogRepository($connection), $permissions);
    $twig = new Twig\Environment(new Twig\Loader\FilesystemLoader(dirname(__DIR__, 2).'/templates'), ['strict_variables' => true]);
    $twig->addExtension(new Symfony\Bridge\Twig\Extension\AssetExtension(new Symfony\Component\Asset\Packages(
        new Symfony\Component\Asset\PathPackage('/', new Symfony\Component\Asset\VersionStrategy\EmptyVersionStrategy()),
    )));
    $erpView = new ErpView($twig);
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
    $cases['controllers enforce login, permissions and session-bound form tokens'] = static function () use ($people, $suppliers, $connection, $throws, $same, $erpView): void {
        $session = new ErpSession();
        $views = $erpView;
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
    $cases['machine controller enforces tokens and saves only the opened record'] = static function () use ($machines, $throws, $same, $connection, $erpView): void {
        $session = new ErpSession();
        $controller = new MachineController($machines, $session, $erpView);
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
    $cases['client permissions deny reads and writes without ERP1030'] = static function () use ($throws, $clients, $connection, $same): void {
        $company = new ClientData(ClientType::Company, 'Acme', '', '12345678901', 'Av. Lima', '012345', 'A', '1721', 'acm', 'A');
        foreach (['', '2000', '3000'] as $user) {
            $throws(AccessDeniedHttpException::class, static fn () => $clients->choices($user, ClientType::Company));
            $throws(AccessDeniedHttpException::class, static fn () => $clients->save($user, null, $company));
        }
        $same(0, (int) $connection->fetchOne('SELECT COUNT(*) FROM E01MCLI'));
    };
    $cases['clients store placeholders by person type and keep identity on update'] = static function () use ($clients, $connection, $same): void {
        $natural = $clients->save('1000', null, new ClientData(ClientType::Natural, "o'higgins", '12345678', 'ignored', "Jr. O'Brien", '987 654', 'A', 'ignored', 'ignored', 'A'));
        $company = $clients->save('1000', null, new ClientData(ClientType::Company, 'Acme', 'ignored', '12345678901', 'Av. Lima', '012345', 'A', '1721', 'acm', 'A'));
        $same(['000001', '000002'], [$natural, $company]);
        $row = $clients->find('1000', $natural);
        $same(["O'HIGGINS", '12345678', '99999999999', '9999', 'S/S', "JR. O'BRIEN"], [$row['name'], $row['dni'], $row['ruc'], $row['ciiu'], $row['acronym'], $row['address']]);
        $row = $clients->find('1000', $company);
        $same(['99999999', '12345678901', '1721', 'ACM'], [$row['dni'], $row['ruc'], $row['ciiu'], $row['acronym']]);
        $clients->save('1000', $company, new ClientData(ClientType::Natural, 'Changed', '11111111', '22222222222', 'Av. Sur', '1', 'A', '1721', 'NEW', 'A'));
        $row = $clients->find('1000', $company);
        $same(['C', 'ACME', '12345678901', 'AV. SUR', 'NEW'], [$row['type'], $row['name'], $row['ruc'], $row['address'], $row['acronym']]);
        $same([['000002', 'ACME']], $clients->choices('1000', ClientType::Company));
        $connection->delete('E01MCLI', ['cCodCli' => $natural]);
        $same('000003', $clients->save('1000', null, new ClientData(ClientType::Natural, 'Next', '87654321', '', 'Av. Este', '1', 'A', '', '', 'A')));
    };
    $cases['client validation rejects identity, catalog and length errors'] = static function () use ($clients, $throws, $connection, $same): void {
        $before = (int) $connection->fetchOne('SELECT COUNT(*) FROM E01MCLI');
        foreach ([
            new ClientData(ClientType::Natural, 'Name', '123', '', 'Address', '1', 'A', '', '', 'A'),
            new ClientData(ClientType::Company, 'Name', '', '123', 'Address', '1', 'A', '1721', 'X', 'A'),
            new ClientData(ClientType::Company, 'Name', '', '12345678901', 'Address', '1', 'A', '0000', 'X', 'A'),
            new ClientData(ClientType::Company, 'Name', '', '12345678901', 'Address', '1', 'A', '1721', 'TOOLONG', 'A'),
            new ClientData(ClientType::Natural, 'Name', '12345678', '', 'Address', 'abc', 'A', '', '', 'A'),
            new ClientData(ClientType::Natural, 'Name', '12345678', '', 'Address', '1', 'Z', '', '', 'A'),
            new ClientData(ClientType::Natural, 'Name', '12345678', '', 'Address', '1', 'A', '', '', 'Z'),
            new ClientData(ClientType::Natural, '', '12345678', '', 'Address', '1', 'A', '', '', 'A'),
        ] as $invalid) {
            $throws(DomainException::class, static fn () => $clients->save('1000', null, $invalid));
        }
        $same($before, (int) $connection->fetchOne('SELECT COUNT(*) FROM E01MCLI'));
    };
    $cases['client controller follows type, list, edit and save steps'] = static function () use ($clients, $throws, $same, $connection, $erpView): void {
        $session = new ErpSession();
        $controller = new ClientController($clients, $session, $erpView);
        $post = static fn (string $action, array $fields) => $controller(Request::create('/ERP1030.php', 'POST'), new MaintenanceInput($action, $fields + ['_token' => $session->token('clients')], []));
        $session->logout();
        $same('/index.php', $controller(Request::create('/ERP1030.php'), new MaintenanceInput('', [], []))->headers->get('Location'));
        $session->set('gcCodUsu', '2000');
        $throws(AccessDeniedHttpException::class, static fn () => $controller(Request::create('/ERP1030.php'), new MaintenanceInput('', [], [])));
        $session->set('gcCodUsu', '1000');
        $throws(AccessDeniedHttpException::class, static fn () => $controller(Request::create('/ERP1030.php', 'POST'), new MaintenanceInput('Aceptar', ['pcTipCli' => 'C'], [])));
        $same(422, $post('Nuevo', [])->getStatusCode());
        $same(200, $post('Aceptar', ['pcTipCli' => 'C'])->getStatusCode());
        $same(200, $post('Nuevo', [])->getStatusCode());
        $fields = ['pcNombre' => 'Beta SAC', 'pcNroRUC' => '10987654321', 'pcDirec' => 'Av. Norte', 'pcTelef' => '555', 'pcEstado' => 'A', 'pcCIIU' => '1721', 'pcSiglas' => 'BET', 'pcPriori' => 'A'];
        $same(422, $post('Grabar', ['pcNroRUC' => '1'] + $fields)->getStatusCode());
        $same(303, $post('Grabar', $fields)->getStatusCode());
        $code = $connection->fetchOne("SELECT cCodCli FROM E01MCLI WHERE cNroRuc = '10987654321'");
        $same('C', $connection->fetchOne('SELECT cTipo FROM E01MCLI WHERE cCodCli = :code', ['code' => $code]));
        $same(200, $post('Aplicar', ['pcCodCli' => $code])->getStatusCode());
        $same(422, $post('Grabar', ['pcCodCli' => '000999'] + $fields)->getStatusCode());
        $same(303, $post('Grabar', ['pcCodCli' => $code, 'pcDirec' => 'Av. Oeste'] + $fields)->getStatusCode());
        $same('AV. OESTE', $connection->fetchOne('SELECT cDirec FROM E01MCLI WHERE cCodCli = :code', ['code' => $code]));
        $same('/Mnu0000.php?Id=1000', $post('Salir', ['Salir' => 'Salir'])->headers->get('Location'));
        $same(200, $post('Salir', ['Boton5' => 'Salir'])->getStatusCode());
        $session->logout();
    };
    $cases['form mapping accepts legacy button names and rejects unknown actions'] = static function () use ($throws, $same): void {
        $resolver = new MaintenanceInputResolver();
        $argument = new ArgumentMetadata('input', MaintenanceInput::class, false, false, null);
        $same('Aceptar', iterator_to_array($resolver->resolve(Request::create('/', 'POST', ['Aceptar' => 'Aceptar', 'pcTipCli' => 'A']), $argument))[0]->action);
        $same('Nuevo', iterator_to_array($resolver->resolve(Request::create('/', 'POST', ['Boton' => 'Nuevo']), $argument))[0]->action);
        $throws(BadRequestHttpException::class, static fn () => iterator_to_array($resolver->resolve(Request::create('/', 'POST', ['Boton1' => 'Borrar todo']), $argument)));
        $throws(BadRequestHttpException::class, static fn () => iterator_to_array($resolver->resolve(Request::create('/', 'POST', ['pcTipCli' => ['A']]), $argument)));
    };
    $cases['supply permissions deny reads, writes and deletions without ERP1050'] = static function () use ($throws, $supplies, $connection, $same): void {
        foreach (['', '2000', '3000'] as $user) {
            $throws(AccessDeniedHttpException::class, static fn () => $supplies->all($user));
            $throws(AccessDeniedHttpException::class, static fn () => $supplies->save($user, null, new SupplyData('01', 'Lana', 'A', '1', '1')));
            $throws(AccessDeniedHttpException::class, static fn () => $supplies->delete($user, '010001'));
        }
        $same(0, (int) $connection->fetchOne('SELECT COUNT(*) FROM E01MINS'));
    };
    $cases['supply codes use the subtype prefix and updates keep the description'] = static function () use ($supplies, $connection, $same): void {
        $first = $supplies->save('1000', null, new SupplyData('01', "lana o'neil", 'A', '10.50', '3'));
        $second = $supplies->save('1000', null, new SupplyData('01', 'Lana fina', 'A', '2', '1'));
        $other = $supplies->save('1000', null, new SupplyData('02', 'Tinte', 'A', '2', '1'));
        $same(['010001', '010002', '020001'], [$first, $second, $other]);
        $connection->delete('E01MINS', ['cCodIns' => $first]);
        $same('010003', $supplies->save('1000', null, new SupplyData('01', 'Lana gruesa', 'A', '2', '1')));
        $supplies->save('1000', $second, new SupplyData('99', 'Ignored', 'A', '7.25', '40'));
        $same(['code' => '010002', 'description' => 'LANA FINA', 'unit' => 'A', 'price' => '7.25', 'granularity' => '40'], $supplies->find('1000', $second));
        $same(['010002', 'LANA FINA', 'Hilos', 'Bolsas', '40', '7.25'], array_values(array_filter($supplies->all('1000'), static fn (array $row): bool => '010002' === $row[0]))[0]);
    };
    $cases['supply validation rejects bad catalog values, prices and granularity'] = static function () use ($supplies, $throws, $connection, $same): void {
        $before = (int) $connection->fetchOne('SELECT COUNT(*) FROM E01MINS');
        foreach ([['99', 'X', 'A', '1', '1'], ['01', '', 'A', '1', '1'], ['01', 'X', 'Z', '1', '1'], ['01', 'X', 'A', '0', '1'],
            ['01', 'X', 'A', '1.234', '1'], ['01', 'X', 'A', '1; DROP', '1'], ['01', 'X', 'A', '1', '0'], ['01', 'X', 'A', '1', '1.5']] as $values) {
            $throws(DomainException::class, static fn () => $supplies->save('1000', null, new SupplyData(...$values)));
        }
        $throws(DomainException::class, static fn () => $supplies->save('1000', '999999', new SupplyData('01', 'X', 'A', '1', '1')));
        $same($before, (int) $connection->fetchOne('SELECT COUNT(*) FROM E01MINS'));
    };
    $cases['supply deletion is refused while another table references the code'] = static function () use ($supplies, $throws, $connection, $same, &$usageTableCreated): void {
        $code = $supplies->save('1000', null, new SupplyData('02', 'En compra', 'A', '1', '1'));
        $connection->insert('E01DCOM', ['cNumero' => '000001', 'cCodIns' => $code, 'nCantid' => 1, 'nCanGra' => 1, 'nPrecio' => 1, 'cEstado' => 'A', 'cCodUsu' => '1000']);
        $throws(DomainException::class, static fn () => $supplies->delete('1000', $code));
        $connection->delete('E01DCOM', ['cCodIns' => $code]);
        $connection->executeStatement('CREATE TABLE test_supply_usage (cCodIns CHAR(6) COLLATE utf8_unicode_ci, FOREIGN KEY (cCodIns) REFERENCES E01MINS (cCodIns)) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci');
        $usageTableCreated = true;
        $connection->insert('test_supply_usage', ['cCodIns' => $code]);
        $throws(DomainException::class, static fn () => $supplies->delete('1000', $code));
        $same(1, (int) $connection->fetchOne('SELECT COUNT(*) FROM E01MINS WHERE cCodIns = :code', ['code' => $code]));
        $connection->executeStatement('DELETE FROM test_supply_usage');
        $supplies->delete('1000', $code);
        $same(0, (int) $connection->fetchOne('SELECT COUNT(*) FROM E01MINS WHERE cCodIns = :code', ['code' => $code]));
        $throws(DomainException::class, static fn () => $supplies->delete('1000', $code));
    };
    $cases['supply controller edits, saves and deletes with a form token'] = static function () use ($supplies, $throws, $same, $connection, $erpView): void {
        $session = new ErpSession();
        $controller = new SupplyController($supplies, $session, $erpView);
        $post = static fn (string $action, array $fields) => $controller(Request::create('/ERP1050.php', 'POST'), new MaintenanceInput($action, $fields + ['_token' => $session->token('supplies')], []));
        $session->logout();
        $same('/index.php', $controller(Request::create('/ERP1050.php'), new MaintenanceInput('', [], []))->headers->get('Location'));
        $session->set('gcCodUsu', '2000');
        $throws(AccessDeniedHttpException::class, static fn () => $controller(Request::create('/ERP1050.php'), new MaintenanceInput('', [], [])));
        $session->set('gcCodUsu', '1000');
        $same(200, $controller(Request::create('/ERP1050.php'), new MaintenanceInput('', [], []))->getStatusCode());
        $throws(AccessDeniedHttpException::class, static fn () => $controller(Request::create('/ERP1050.php', 'POST'), new MaintenanceInput('Eliminar', ['pcCodArs' => '010002'], [])));
        $same(200, $post('Nuevo', [])->getStatusCode());
        $fields = ['pcSubTip' => '02', 'pcDescri' => 'Aguja', 'pcUniMed' => 'A', 'pnCanGra' => '5', 'pnPrecio' => '3.5'];
        $same(422, $post('Grabar', ['pnPrecio' => 'x'] + $fields)->getStatusCode());
        $same(303, $post('Grabar', $fields)->getStatusCode());
        $code = $connection->fetchOne("SELECT cCodIns FROM E01MINS WHERE cDescri = 'AGUJA'");
        $same(200, $post('Editar', ['pcCodArs' => $code])->getStatusCode());
        $same(422, $post('Grabar', ['pcCodIns' => '010002'] + $fields)->getStatusCode());
        $same(303, $post('Grabar', ['pcCodIns' => $code, 'pnPrecio' => '9'] + $fields)->getStatusCode());
        $same('9.00', $connection->fetchOne('SELECT nPrecio FROM E01MINS WHERE cCodIns = :code', ['code' => $code]));
        $same(303, $post('Eliminar', ['pcCodArs' => $code])->getStatusCode());
        $same(false, $connection->fetchOne('SELECT cCodIns FROM E01MINS WHERE cCodIns = :code', ['code' => $code]));
        $same(422, $post('Eliminar', [])->getStatusCode());
        $session->logout();
    };
    $catalogRows = static function () use ($connection): array {
        return $connection->fetchAllNumeric("SELECT cCodTab, nOrden, cCodigo, cDescri, mTexto FROM E01TTAB WHERE cTipReg = '1' ORDER BY cCodTab, cCodigo");
    };
    $connection->insert('E01TTAB', ['cCodTab' => '099', 'nOrden' => 0, 'cTipReg' => '0', 'cCodigo' => 'AAAA', 'cDescri' => '*CIIU']);
    $connection->insert('E01TTAB', ['cCodTab' => '099', 'nOrden' => 1, 'cTipReg' => '1', 'cCodigo' => '0111', 'cDescri' => 'Cereales', 'mTexto' => 'Long text']);
    $connection->insert('E01TTAB', ['cCodTab' => '021', 'nOrden' => 0, 'cTipReg' => '0', 'cCodigo' => '9.9.9', 'cDescri' => '*PK']);
    $connection->insert('E01TTAB', ['cCodTab' => '021', 'nOrden' => 1, 'cTipReg' => '1', 'cCodigo' => '1.1', 'cDescri' => 'Uno']);
    $cases['catalog table permissions deny reads and writes without ERP1080'] = static function () use ($throws, $catalogTables, $catalogRows, $same): void {
        $before = $catalogRows();
        foreach (['', '2000', '3000'] as $user) {
            $throws(AccessDeniedHttpException::class, static fn () => $catalogTables->headers($user));
            $throws(AccessDeniedHttpException::class, static fn () => $catalogTables->save($user, '099', ['0111'], [new CatalogEntry('1', '0111', 'X')]));
        }
        $same($before, $catalogRows());
    };
    $cases['catalog table save updates rows in place, keeps mTexto and inserts new codes'] = static function () use ($catalogTables, $catalogRows, $same): void {
        $same([['021', '*PK'], ['099', '*CIIU']], $catalogTables->headers('1000'));
        $opened = $catalogTables->open('1000', '099');
        $same([['1', '0111', 'Cereales']], array_map(static fn (CatalogEntry $entry): array => [$entry->order, $entry->code, $entry->description], $opened['entries']));
        $catalogTables->save('1000', '099', ['0111'], [new CatalogEntry('2', '9999', "Cereales O'Neil"), new CatalogEntry('', 'ab12', 'Nuevo')]);
        $same([['021', 1, '1.1', 'Uno', null], ['099', 2, '0111', "Cereales O'Neil", 'Long text'], ['099', 0, 'AB12', 'Nuevo', null]], $catalogRows());
    };
    $cases['catalog table validation keeps existing rows and code formats'] = static function () use ($catalogTables, $catalogRows, $throws, $same): void {
        $before = $catalogRows();
        $original = ['0111', 'AB12'];
        foreach ([
            [new CatalogEntry('1', '0111', 'Only one')],
            [new CatalogEntry('1', '0111', 'A'), new CatalogEntry('1', 'AB12', 'B'), new CatalogEntry('1', 'ab12', 'C')],
            [new CatalogEntry('1', '0111', 'A'), new CatalogEntry('1', 'AB12', 'B'), new CatalogEntry('1', 'TOOLONG', 'C')],
            [new CatalogEntry('1', '0111', 'A'), new CatalogEntry('1', 'AB12', 'B'), new CatalogEntry('1', 'XY99', '')],
            [new CatalogEntry('1', '0111', 'A'), new CatalogEntry('1', 'AB12', 'B'), new CatalogEntry('1000', 'XY99', 'C')],
        ] as $entries) {
            $throws(DomainException::class, static fn () => $catalogTables->save('1000', '099', $original, $entries));
        }
        $throws(DomainException::class, static fn () => $catalogTables->save('1000', '099', ['0111'], [new CatalogEntry('1', '0111', 'Stale'), new CatalogEntry('1', 'XY99', 'Stale')]));
        $throws(DomainException::class, static fn () => $catalogTables->save('1000', '021', ['1.1'], [new CatalogEntry('1', '1.A', 'Bad')]));
        $throws(DomainException::class, static fn () => $catalogTables->open('1000', '777'));
        $same($before, $catalogRows());
        $catalogTables->save('1000', '021', ['1.1'], [new CatalogEntry('1', '1.2.3', 'Uno')]);
        $same('1.2.3', $catalogRows()[0][2]);
    };
    $cases['catalog table controller adds and removes only new rows before saving'] = static function () use ($catalogTables, $catalogRows, $throws, $same, $erpView): void {
        $session = new ErpSession();
        $controller = new CatalogTableController($catalogTables, $session, $erpView);
        $post = static fn (string $action, array $fields, array $grids = []) => $controller(Request::create('/ERP1080.php', 'POST'), new MaintenanceInput($action, $fields + ['_token' => $session->token('catalog_tables')], [], $grids));
        $session->logout();
        $same('/index.php', $controller(Request::create('/ERP1080.php'), new MaintenanceInput('', [], []))->headers->get('Location'));
        $session->set('gcCodUsu', '2000');
        $throws(AccessDeniedHttpException::class, static fn () => $controller(Request::create('/ERP1080.php'), new MaintenanceInput('', [], [])));
        $session->set('gcCodUsu', '1000');
        $throws(AccessDeniedHttpException::class, static fn () => $controller(Request::create('/ERP1080.php', 'POST'), new MaintenanceInput('Editar', [], [], ['paCheck' => ['099']])));
        $same(422, $post('Grabar', ['pcCodTab' => '099'], ['paData' => []])->getStatusCode());
        $same(200, $post('Editar', [], ['paCheck' => ['099']])->getStatusCode());
        $rows = [1 => ['1', '1', '0111', 'Cereales'], 2 => ['2', '0', 'AB12', 'Nuevo']];
        $same(200, $post('Nuevo', ['pcCodTab' => '099'], ['paData' => $rows])->getStatusCode());
        $same(422, $post('Eliminar', ['pcCodTab' => '099'], ['paData' => $rows, 'paCheck' => ['2']])->getStatusCode());
        $withNew = $rows + [3 => ['3', '5', 'zz01', 'Agregado'], 4 => ['4', '0', 'QQ00', 'Descartado']];
        $same(200, $post('Eliminar', ['pcCodTab' => '099'], ['paData' => $withNew, 'paCheck' => ['4']])->getStatusCode());
        $same(422, $post('Grabar', ['pcCodTab' => '005'], ['paData' => $withNew])->getStatusCode());
        $same(303, $post('Grabar', ['pcCodTab' => '099'], ['paData' => array_slice($withNew, 0, 3, true)])->getStatusCode());
        $same(['0111', 'AB12', 'ZZ01'], array_column(array_filter($catalogRows(), static fn (array $row): bool => '099' === $row[0]), 2));
        $session->logout();
    };
    $cases['warehouse permissions deny reads and writes without ERP1090'] = static function () use ($throws, $warehouses, $connection, $same): void {
        foreach (['', '2000', '3000'] as $user) {
            $throws(AccessDeniedHttpException::class, static fn () => $warehouses->choices($user));
            $throws(AccessDeniedHttpException::class, static fn () => $warehouses->save($user, null, new WarehouseData('Central', 'Av. Lima', 'A', 'I')));
        }
        $same(0, (int) $connection->fetchOne('SELECT COUNT(*) FROM E01MALM'));
    };
    $cases['warehouse create, update and validation'] = static function () use ($warehouses, $throws, $connection, $same): void {
        $code = $warehouses->save('1000', null, new WarehouseData("almacén o'neil", 'Av. Lima', 'A', 'I'));
        $same('000001', $code);
        $warehouses->save('1000', $code, new WarehouseData('Central', 'Jr. Cusco', 'A', 'I'));
        $same(['code' => '000001', 'name' => 'CENTRAL', 'address' => 'JR. CUSCO', 'status' => 'A', 'type' => 'I'], $warehouses->find('1000', $code));
        foreach ([['', 'X', 'A', 'I'], ['X', '', 'A', 'I'], ['X', 'X', 'Z', 'I'], ['X', 'X', 'A', 'P'], [str_repeat('X', 101), 'X', 'A', 'I']] as $values) {
            $throws(DomainException::class, static fn () => $warehouses->save('1000', null, new WarehouseData(...$values)));
        }
        $throws(DomainException::class, static fn () => $warehouses->save('1000', '999999', new WarehouseData('X', 'X', 'A', 'I')));
        $same(1, (int) $connection->fetchOne('SELECT COUNT(*) FROM E01MALM'));
    };
    $cases['warehouse controller saves only the opened record'] = static function () use ($warehouses, $throws, $same, $connection, $erpView): void {
        $session = new ErpSession();
        $controller = new WarehouseController($warehouses, $session, $erpView);
        $post = static fn (string $action, array $fields) => $controller(Request::create('/ERP1090.php', 'POST'), new MaintenanceInput($action, $fields + ['_token' => $session->token('warehouses')], []));
        $session->logout();
        $same('/index.php', $controller(Request::create('/ERP1090.php'), new MaintenanceInput('', [], []))->headers->get('Location'));
        $session->set('gcCodUsu', '2000');
        $throws(AccessDeniedHttpException::class, static fn () => $controller(Request::create('/ERP1090.php'), new MaintenanceInput('', [], [])));
        $session->set('gcCodUsu', '1000');
        $throws(AccessDeniedHttpException::class, static fn () => $controller(Request::create('/ERP1090.php', 'POST'), new MaintenanceInput('Nuevo', [], [])));
        $fields = ['pcNomAlm' => 'Norte', 'pcDirec' => 'Av. Norte', 'pcEstado' => 'A', 'pcTipo' => 'I'];
        $same(200, $post('Nuevo', [])->getStatusCode());
        $same(303, $post('Grabar', $fields)->getStatusCode());
        $same(200, $post('Aplicar', ['pcCodAlm' => '000002'])->getStatusCode());
        $same(422, $post('Grabar', ['pcCodAlm' => '000001'] + $fields)->getStatusCode());
        $same(303, $post('Grabar', ['pcCodAlm' => '000002', 'pcDirec' => 'Av. Sur'] + $fields)->getStatusCode());
        $same('AV. SUR', $connection->fetchOne("SELECT cDirec FROM E01MALM WHERE cCodAlm = '000002'"));
        $session->logout();
    };
    $cases['bank permissions deny reads and writes without ERP1093 or ERP1094'] = static function () use ($throws, $banks, $bankAccounts, $connection, $same): void {
        foreach (['', '2000', '3000'] as $user) {
            $throws(AccessDeniedHttpException::class, static fn () => $banks->choices($user));
            $throws(AccessDeniedHttpException::class, static fn () => $banks->save($user, null, new BankData('Banco', 'A', 'AB')));
            $throws(AccessDeniedHttpException::class, static fn () => $bankAccounts->add($user, AccountOwner::Bank, '000001', '12345678901234'));
        }
        $same(0, (int) $connection->fetchOne('SELECT COUNT(*) FROM E01MBAN') + (int) $connection->fetchOne('SELECT COUNT(*) FROM E01PCTA'));
    };
    $cases['new banks receive consecutive codes and validation prevents writes'] = static function () use ($banks, $throws, $connection, $same): void {
        $same(['000001', '000002'], [$banks->save('1000', null, new BankData("banco o'neil", 'A', 'AB')), $banks->save('1000', null, new BankData('Interbank', 'A', 'AB'))]);
        $banks->save('1000', '000002', new BankData('Interbank Peru', 'A', 'AB'));
        $same(['code' => '000002', 'name' => 'INTERBANK PERU', 'status' => 'A', 'city' => 'AB'], $banks->find('1000', '000002'));
        foreach ([['', 'A', 'AB'], [str_repeat('X', 51), 'A', 'AB'], ['X', 'Z', 'AB'], ['X', 'A', 'ZZ']] as $values) {
            $throws(DomainException::class, static fn () => $banks->save('1000', null, new BankData(...$values)));
        }
        $throws(DomainException::class, static fn () => $banks->save('1000', '999999', new BankData('X', 'A', 'AB')));
        $same(2, (int) $connection->fetchOne('SELECT COUNT(*) FROM E01MBAN'));
    };
    $cases['bank accounts store the owner, placeholder and audit user'] = static function () use ($bankAccounts, $suppliers, $supplier, $throws, $connection, $same): void {
        $supplierCode = $suppliers->save('1000', null, $supplier);
        $bankAccounts->add('1000', AccountOwner::Bank, '000001', '12345678901234');
        $bankAccounts->add('1000', AccountOwner::Supplier, $supplierCode, '22345678901234');
        $same([['000001', '999999', '12345678901234', '1000'], ['999999', $supplierCode, '22345678901234', '1000']],
            $connection->fetchAllNumeric('SELECT cCodBan, cCodPrv, nCuenta, cCodUsu FROM E01PCTA ORDER BY nCuenta'));
        $throws(DomainException::class, static fn () => $bankAccounts->add('1000', AccountOwner::Bank, '000001', '12345678901234'));
        $throws(DomainException::class, static fn () => $bankAccounts->add('1000', AccountOwner::Bank, '000001', '1234'));
        $throws(DomainException::class, static fn () => $bankAccounts->add('1000', AccountOwner::Bank, '000999', '32345678901234'));
        $throws(DomainException::class, static fn () => $bankAccounts->add('1000', AccountOwner::Supplier, '000001x', '32345678901234'));
        $same(2, (int) $connection->fetchOne('SELECT COUNT(*) FROM E01PCTA'));
    };
    $cases['bank and account controllers require tokens and save selected values'] = static function () use ($banks, $bankAccounts, $throws, $same, $connection, $erpView): void {
        $session = new ErpSession();
        $views = $erpView;
        $bankController = new BankController($banks, $session, $views);
        $accountController = new BankAccountController($bankAccounts, $session, $views);
        $session->logout();
        $same('/index.php', $bankController(Request::create('/ERP1093.php'), new MaintenanceInput('', [], []))->headers->get('Location'));
        $session->set('gcCodUsu', '2000');
        $throws(AccessDeniedHttpException::class, static fn () => $accountController(Request::create('/ERP1094.php'), new MaintenanceInput('', [], [])));
        $session->set('gcCodUsu', '1000');
        $throws(AccessDeniedHttpException::class, static fn () => $bankController(Request::create('/ERP1093.php', 'POST'), new MaintenanceInput('Nuevo', [], [])));
        $bank = static fn (string $action, array $fields) => $bankController(Request::create('/ERP1093.php', 'POST'), new MaintenanceInput($action, $fields + ['_token' => $session->token('banks')], []));
        $same(200, $bank('Nuevo', [])->getStatusCode());
        $same(303, $bank('Grabar', ['pcNomBan' => 'Scotiabank', 'pcEstado' => 'A', 'pcCiudad' => 'AB'])->getStatusCode());
        $same('SCOTIABANK', $connection->fetchOne("SELECT cNomBan FROM E01MBAN WHERE cCodBan = '000003'"));
        $account = static fn (string $action, array $fields) => $accountController(Request::create('/ERP1094.php', 'POST'), new MaintenanceInput($action, $fields + ['_token' => $session->token('bank_accounts')], []));
        $same(422, $account('Grabar', ['pcCodBan' => '000003', 'pcNroCta' => '42345678901234'])->getStatusCode());
        $same(422, $account('Nuevo', [])->getStatusCode());
        $same(200, $account('Nuevo', ['pcTipo' => '2'])->getStatusCode());
        $same(422, $account('Grabar', ['pcCodBan' => '000003', 'pcNroCta' => 'x'])->getStatusCode());
        $same(303, $account('Grabar', ['pcCodBan' => '000003', 'pcNroCta' => '42345678901234'])->getStatusCode());
        $same('000003', $connection->fetchOne("SELECT cCodBan FROM E01PCTA WHERE nCuenta = '42345678901234'"));
        $same('/Mnu0000.php?Id=1000', $account('Salir', [])->headers->get('Location'));
        $session->logout();
    };
    $stockOf = static fn (string $warehouse, string $supply): mixed => $connection->fetchOne('SELECT nCant FROM E01PALM WHERE cCodAlm = :w AND cCodIns = :s', ['w' => $warehouse, 's' => $supply]);
    $cases['new modules deny access without their permission'] = static function () use ($throws, $transfers, $products, $users, $profiles): void {
        foreach (['', '2000', '3000'] as $user) {
            $throws(AccessDeniedHttpException::class, static fn () => $transfers->warehouses($user));
            $throws(AccessDeniedHttpException::class, static fn () => $transfers->transfer($user, '9999', 'W00001', ['Z00001' => '1']));
            $throws(AccessDeniedHttpException::class, static fn () => $products->create($user, 'Chompa', 'A'));
            $throws(AccessDeniedHttpException::class, static fn () => $users->create($user, '000777', 'A'));
            $throws(AccessDeniedHttpException::class, static fn () => $profiles->grant($user, 'B', 'ERP1030', 'A'));
            $throws(AccessDeniedHttpException::class, static fn () => $profiles->revoke($user, 'A', 'ERP1030'));
        }
    };
    $cases['stock transfers add from supplier, move between warehouses and roll back on shortage'] = static function () use ($transfers, $connection, $stockOf, $throws, $same): void {
        $connection->insert('E01MINS', ['cCodIns' => 'Z00001', 'cDescri' => 'LANA FINA', 'cUniMed' => 'A', 'nPrecio' => '7.25']);
        $connection->insert('E01MINS', ['cCodIns' => 'Z00002', 'cDescri' => 'TINTE AZUL', 'cUniMed' => 'A', 'nPrecio' => '3.00']);
        $connection->insert('E01MALM', ['CCODALM' => 'W00001', 'CNOMALM' => 'PRIMERO', 'CESTADO' => 'A', 'CTIPO' => 'I']);
        $connection->insert('E01MALM', ['CCODALM' => 'W00002', 'CNOMALM' => 'TERCERO', 'CESTADO' => 'A', 'CTIPO' => 'I']);
        $third = 'W00002';
        $transfers->transfer('1000', '9999', 'W00001', ['Z00001' => '10', 'Z00002' => '4']);
        $transfers->transfer('1000', '9999', 'W00001', ['Z00001' => '5']);
        $same(['15', '4'], [$stockOf('W00001', 'Z00001'), $stockOf('W00001', 'Z00002')]);
        $transfers->transfer('1000', 'W00001', $third, ['Z00001' => '6']);
        $same(['9', '6'], [$stockOf('W00001', 'Z00001'), $stockOf($third, 'Z00001')]);
        $throws(DomainException::class, static fn () => $transfers->transfer('1000', 'W00001', $third, ['Z00002' => '1', 'Z00001' => '100']));
        $same(['4', false], [$stockOf('W00001', 'Z00002'), $stockOf($third, 'Z00002')]);
        foreach ([['W00001', 'W00001', ['Z00001' => '1']], ['W00001', '9999', ['Z00001' => '1']], ['999998', $third, ['Z00001' => '1']],
            ['9999', $third, []], ['9999', $third, ['999999' => '1']], ['9999', $third, ['Z00001' => '0']], ['9999', $third, ['Z00001' => '1.5']]] as [$origin, $destination, $lines]) {
            $throws(DomainException::class, static fn () => $transfers->transfer('1000', $origin, $destination, $lines));
        }
        $same('9', $stockOf('W00001', 'Z00001'));
        $same(['Z00001', 'LANA FINA', 'Bolsas', '7.25', '9'], $transfers->stock('1000', 'W00001')['stock'][0]);
    };
    $cases['stock transfer controller keeps lines and saves with a token'] = static function () use ($transfers, $stockOf, $throws, $same, $erpView): void {
        $session = new ErpSession();
        $controller = new StockTransferController($transfers, $session, $erpView);
        $post = static fn (string $action, array $fields, array $grids = []) => $controller(Request::create('/ERP1091.php', 'POST'), new MaintenanceInput($action, $fields + ['_token' => $session->token('stock_transfer')], [], $grids));
        $session->logout();
        $same('/index.php', $controller(Request::create('/ERP1091.php'), new MaintenanceInput('', [], []))->headers->get('Location'));
        $session->set('gcCodUsu', '1000');
        $throws(AccessDeniedHttpException::class, static fn () => $controller(Request::create('/ERP1091.php', 'POST'), new MaintenanceInput('Grabar', [], [])));
        $same(200, $post('Detalle', ['pcCodAlm' => 'W00001'])->getStatusCode());
        $same(422, $post('Detalle', ['pcCodAlm' => '999999'])->getStatusCode());
        $same(200, $post('Asignar Insumos', [])->getStatusCode());
        $same(200, $post('Agregar', ['pcCodIns' => 'Z00002', 'pcOrigen' => '9999', 'pcDestino' => 'W00001'])->getStatusCode());
        $lines = ['paDataI' => ['Z00002' => ['Z00002', 'tampered', 'x', 'x', '2'], 'Z00001' => ['Z00001', '', '', '', '1']]];
        $same(200, $post('Eliminar', ['pcOrigen' => '9999', 'pcDestino' => 'W00001'], $lines + ['paCheck' => ['Z00001']])->getStatusCode());
        $same(422, $post('Grabar', ['pcOrigen' => '9999', 'pcDestino' => '9999'], $lines)->getStatusCode());
        $same(303, $post('Grabar', ['pcOrigen' => '9999', 'pcDestino' => 'W00001'], $lines)->getStatusCode());
        $same(['6', '10'], [$stockOf('W00001', 'Z00002'), $stockOf('W00001', 'Z00001')]);
        $session->logout();
    };
    $cases['products are created without recipe and recipes replace lines in one transaction'] = static function () use ($products, $connection, $throws, $same): void {
        $connection->insert('E01MPROD', ['cCodProd' => '900001', 'cDescri' => 'Reservado', 'cEstado' => 'A']);
        $code = $products->create('1000', "chompa o'neil", 'A');
        $same(['000001', "CHOMPA O'NEIL", 'N'], array_values($connection->fetchNumeric('SELECT cCodProd, cDescri, cTipo FROM E01MPROD WHERE cCodProd = :c', ['c' => $code])));
        $throws(DomainException::class, static fn () => $products->create('1000', '', 'A'));
        $throws(DomainException::class, static fn () => $products->create('1000', 'X', 'Z'));
        $products->saveRecipe('1000', $code, 'Chompa', ['Z00001', 'Z00002', 'Z00001']);
        $same(['code' => '000001', 'description' => 'CHOMPA', 'status' => 'A', 'recipe' => ['Z00001', 'Z00002']], $products->find('1000', $code));
        $same('A', $connection->fetchOne('SELECT cTipo FROM E01MPROD WHERE cCodProd = :c', ['c' => $code]));
        $throws(DomainException::class, static fn () => $products->saveRecipe('1000', $code, 'Chompa', []));
        $throws(DomainException::class, static fn () => $products->saveRecipe('1000', $code, 'Chompa', ['999999']));
        $throws(DomainException::class, static fn () => $products->saveRecipe('1000', '000999', 'Chompa', ['Z00001']));
        $same(['Z00001', 'Z00002'], $products->find('1000', $code)['recipe']);
    };
    $cases['product controller edits the recipe kept in the form state'] = static function () use ($products, $same, $connection, $erpView): void {
        $session = new ErpSession();
        $controller = new ProductController($products, $session, $erpView);
        $post = static fn (string $action, array $fields, array $grids = []) => $controller(Request::create('/ERP1070.php', 'POST'), new MaintenanceInput($action, $fields + ['_token' => $session->token('products')], [], $grids));
        $session->set('gcCodUsu', '1000');
        $same(200, $controller(Request::create('/ERP1070.php'), new MaintenanceInput('', [], []))->getStatusCode());
        $same(200, $post('Nuevo', ['Boton0' => 'Nuevo'])->getStatusCode());
        $same(303, $post('Grabar', ['Boton2' => 'Grabar', 'pcCodProd' => '', 'pcDescri' => 'Bufanda', 'pcEstado' => 'A'])->getStatusCode());
        $code = $connection->fetchOne("SELECT cCodProd FROM E01MPROD WHERE cDescri = 'BUFANDA'");
        $same(200, $post('Detalle', ['Boton0' => 'Detalle', 'pcCodProd' => $code])->getStatusCode());
        $same(422, $post('Grabar', ['Boton1' => 'Grabar', 'pcCodProd' => $code, 'pcDescri' => 'Bufanda'])->getStatusCode());
        $same(200, $post('Agregar', ['Boton1' => 'Agregar', 'pcCodProd' => $code, 'pcCodIns1' => 'Z00001'])->getStatusCode());
        $same(200, $post('Agregar', ['Boton1' => 'Agregar', 'pcCodProd' => $code, 'pcCodIns1' => 'Z00002'])->getStatusCode());
        $same(200, $post('Agregar', ['Boton1' => 'Agregar', 'pcCodProd' => $code, 'pcCodIns1' => '999999'])->getStatusCode());
        $same(200, $post('Eliminar', ['Boton1' => 'Eliminar', 'pcCodProd' => $code], ['paCheck' => ['Z00001']])->getStatusCode());
        $same(303, $post('Grabar', ['Boton1' => 'Grabar', 'pcCodProd' => $code, 'pcDescri' => 'Bufanda larga'])->getStatusCode());
        $same(['Z00002'], $products->find('1000', $code)['recipe']);
        $same('BUFANDA LARGA', $products->find('1000', $code)['description']);
        $session->logout();
    };
    $cases['users are created with the legacy login contract and updated with validation'] = static function () use ($users, $connection, $passwords, $throws, $same): void {
        $connection->insert('E01MPER', ['cCodPer' => '000777', 'cNombre' => 'ANA', 'cNroDni' => '44556677', 'cEstado' => 'A']);
        $connection->insert('E01MPER', ['cCodPer' => '010777', 'cNombre' => 'LUIS', 'cNroDni' => '11223344', 'cEstado' => 'A']);
        $connection->insert('E01MPER', ['cCodPer' => '000888', 'cNombre' => 'CESADO', 'cNroDni' => '99887766', 'cEstado' => 'I']);
        $same(['000777', '010777'], array_values(array_intersect(['000777', '010777', '000888'], array_column($users->candidates('1000'), 0))));
        $same('0777', $users->create('1000', '000777', 'B'));
        $row = $connection->fetchNumeric("SELECT cClave, dFecFin, cEstado, cPerfil, cUsuCod FROM E01TUSU WHERE cCodUsu = '0777'");
        $same([true, null, 'A', 'B', '1000'], [$passwords->verify((string) $row[0], '44556677'), ...array_slice($row, 1)]);
        $throws(DomainException::class, static fn () => $users->create('1000', '010777', 'B'));
        $throws(DomainException::class, static fn () => $users->create('1000', '000777', 'B'));
        $throws(DomainException::class, static fn () => $users->create('1000', '000888', 'B'));
        $throws(DomainException::class, static fn () => $users->create('1000', '000777', 'Z'));
        $users->update('1000', '0777', 'A', 'A', '2027-01-31');
        $same(['code' => '0777', 'name' => 'ANA', 'status' => 'A', 'end_date' => '2027-01-31', 'profile' => 'A'], $users->find('1000', '0777'));
        $throws(DomainException::class, static fn () => $users->update('1000', '0777', 'A', 'A', '2027-02-30'));
        $throws(DomainException::class, static fn () => $users->update('1000', '0777', 'Z', 'A', '2027-01-31'));
        $throws(DomainException::class, static fn () => $users->update('1000', '9999', 'A', 'A', '2027-01-31'));
        $same([['0777', 'ANA', 'Administrador']], array_values(array_filter($users->all('1000'), static fn (array $row): bool => '0777' === $row[0])));
    };
    $cases['user controller creates and updates through the opened form'] = static function () use ($users, $same, $connection, $erpView): void {
        $session = new ErpSession();
        $controller = new UserController($users, $session, $erpView);
        $post = static fn (string $action, array $fields) => $controller(Request::create('/ERP1010.php', 'POST'), new MaintenanceInput($action, $fields + ['_token' => $session->token('users')], []));
        $session->set('gcCodUsu', '1000');
        $same(200, $controller(Request::create('/ERP1010.php'), new MaintenanceInput('', [], []))->getStatusCode());
        $same(422, $post('Grabar', ['pcCodPer' => '010777', 'pcPerfil' => 'B'])->getStatusCode());
        $connection->delete('E01MPER', ['cCodPer' => '010777']);
        $connection->insert('E01MPER', ['cCodPer' => '000999', 'cNombre' => 'EVA', 'cNroDni' => '55667788', 'cEstado' => 'A']);
        $same(200, $post('Nuevo', [])->getStatusCode());
        $same(303, $post('Grabar', ['pcCodPer' => '000999', 'pcPerfil' => 'B'])->getStatusCode());
        $same(200, $post('Editar', ['pcCodUsu' => '0999'])->getStatusCode());
        $same(422, $post('Actualizar', ['pcCodUsu' => '0777', 'pcEstado' => 'A', 'pcPerfil' => 'B', 'pdFecFin' => '2027-01-31'])->getStatusCode());
        $same(303, $post('Actualizar', ['pcCodUsu' => '0999', 'pcEstado' => 'A', 'pcPerfil' => 'A', 'pdFecFin' => '2027-03-01'])->getStatusCode());
        $same('2027-03-01', $connection->fetchOne("SELECT dFecFin FROM E01TUSU WHERE cCodUsu = '0999'"));
        $session->logout();
    };
    $cases['profiles grant and revoke options without locking out the current user'] = static function () use ($profiles, $connection, $throws, $same): void {
        foreach ([['ERP1030', 'Clientes', 'A'], ['ERP1020', 'Perfiles', 'A'], ['OLD0001', 'Inactiva', 'I']] as [$option, $description, $state]) {
            $connection->insert('E01TOPC', ['cCodOpc' => $option, 'cDescri' => $description, 'cEstado' => $state]);
        }
        $same([['ERP1030', 'Clientes'], ['ERP1020', 'Perfiles']] === $profiles->available('1000', 'B') ? 'sorted wrong' : 'ok', 'ok');
        $same(['ERP1020', 'ERP1030'], array_column($profiles->available('1000', 'B'), 0));
        $profiles->grant('1000', 'B', 'ERP1030', 'A');
        $same(['ERP1030', 'Clientes', 'Activo'], $profiles->open('1000', 'B')['granted'][0]);
        $throws(DomainException::class, static fn () => $profiles->grant('1000', 'B', 'ERP1030', 'A'));
        $throws(DomainException::class, static fn () => $profiles->grant('1000', 'B', 'OLD0001', 'A'));
        $throws(DomainException::class, static fn () => $profiles->grant('1000', 'B', 'ERP1020', 'Z'));
        $throws(DomainException::class, static fn () => $profiles->grant('1000', 'Z', 'ERP1020', 'A'));
        $throws(DomainException::class, static fn () => $profiles->revoke('1000', 'A', 'ERP1020'));
        $same(1, (int) $connection->fetchOne("SELECT COUNT(*) FROM E01PPRF WHERE cPerfil = 'A' AND cCodOpc = 'ERP1020'"));
        $profiles->revoke('1000', 'B', 'ERP1030');
        $same([], $profiles->open('1000', 'B')['granted']);
        $throws(DomainException::class, static fn () => $profiles->revoke('1000', 'B', 'ERP1030'));
    };
    $cases['profile controller keeps the selected profile between steps'] = static function () use ($profiles, $same, $connection, $erpView): void {
        $session = new ErpSession();
        $controller = new ProfileController($profiles, $session, $erpView);
        $post = static fn (string $action, array $fields) => $controller(Request::create('/ERP1020.php', 'POST'), new MaintenanceInput($action, $fields + ['_token' => $session->token('profiles')], []));
        $session->set('gcCodUsu', '1000');
        $same(422, $post('Nuevo', [])->getStatusCode());
        $same(200, $post('Aplicar', ['pcPerfil' => 'B'])->getStatusCode());
        $same(200, $post('Nuevo', [])->getStatusCode());
        $same(200, $post('Grabar', ['pcCodOpc' => 'ERP1030', 'pcEstado' => 'A'])->getStatusCode());
        $same(1, (int) $connection->fetchOne("SELECT COUNT(*) FROM E01PPRF WHERE cPerfil = 'B' AND cCodOpc = 'ERP1030'"));
        $same(200, $post('Eliminar', ['pcCodOpc' => 'ERP1030'])->getStatusCode());
        $same(0, (int) $connection->fetchOne("SELECT COUNT(*) FROM E01PPRF WHERE cPerfil = 'B' AND cCodOpc = 'ERP1030'"));
        $same(422, $post('Aplicar', ['pcPerfil' => 'Z'])->getStatusCode());
        $session->logout();
    };
    $cases['migrations convert the tables, widen the password column and hash plain-text passwords'] = static function () use ($connection, $passwords, $migrate, $passwordColumn, $same): void {
        $engines = $connection->fetchAllKeyValue("SELECT UPPER(TABLE_NAME), ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND UPPER(TABLE_NAME) IN ('E01MPER', 'E01TUSU', 'E01PPRF', 'E01TTAB') ORDER BY 1");
        $same(['E01MPER' => 'InnoDB', 'E01PPRF' => 'InnoDB', 'E01TTAB' => 'InnoDB', 'E01TUSU' => 'InnoDB'], $engines);
        $same(true, (new DbalCredentialRepository($connection))->canStoreHashes());
        $stored = (string) $connection->fetchOne("SELECT CCLAVE FROM E01TUSU WHERE CCODUSU = '4000'");
        $same([false, true, false], [UserPasswordHasher::isPlainText($stored), $passwords->verify($stored, 'Clave.Antigua'), $passwords->verify($stored, 'clave.antigua')]);
        $same([$passwordColumn[0], $passwordColumn[1], 36, 255], [...array_slice($connection->fetchNumeric("SELECT CHARACTER_SET_NAME, COLLATION_NAME, CHARACTER_MAXIMUM_LENGTH FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND UPPER(TABLE_NAME) = 'E01TUSU' AND UPPER(COLUMN_NAME) = 'CCLAVE'"), 0, 2), (int) $passwordColumn[2], (int) $connection->fetchNumeric("SELECT CHARACTER_SET_NAME, COLLATION_NAME, CHARACTER_MAXIMUM_LENGTH FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND UPPER(TABLE_NAME) = 'E01TUSU' AND UPPER(COLUMN_NAME) = 'CCLAVE'")[2]]);
        $same(0, $migrate());
    };
    $cases['login upgrades plain text and rejects injection, inactive users and wrong passwords'] = static function () use ($login, $connection, $passwords, $throws, $same): void {
        $same(['code' => '4000', 'name' => '', 'must_change' => true], $login->authenticate('4000', 'Clave.Antigua'));
        foreach ([['4000', 'clave.antigua'], ['4000', 'Clave.Antigua '], ['4000', ''], ['4000', "' OR '1'='1"], ["4000' OR '1'='1", 'Clave.Antigua'],
            ["' OR 1=1 -- ", 'x'], ['9998', 'Clave.Antigua'], ['4001', 'secreta1'], ['', 'Clave.Antigua']] as [$code, $password]) {
            $throws(DomainException::class, static fn () => $login->authenticate($code, $password));
        }
        // A database restored from an old backup still has plain-text passwords.
        $connection->insert('E01TUSU', ['CCODUSU' => '4002', 'CESTADO' => 'A', 'CCLAVE' => 'legacy-99', 'DFECFIN' => '2027-01-01']);
        $same(['code' => '4002', 'name' => '', 'must_change' => false], $login->authenticate('4002', 'legacy-99'));
        $stored = (string) $connection->fetchOne("SELECT CCLAVE FROM E01TUSU WHERE CCODUSU = '4002'");
        $same([false, true], [UserPasswordHasher::isPlainText($stored), $passwords->verify($stored, 'legacy-99')]);
        $same('4002', $login->authenticate('4002', 'legacy-99')['code']);
    };
    $cases['password change validates input and stores a hash with the next expiration date'] = static function () use ($login, $connection, $passwords, $throws, $same): void {
        foreach ([['mala', 'NuevaClave1', 'NuevaClave1'], ['Clave.Antigua', 'corta', 'corta'], ['Clave.Antigua', 'NuevaClave1', 'NuevaClave2'],
            ['Clave.Antigua', 'Clave.Antigua', 'Clave.Antigua'], ['Clave.Antigua', str_repeat('x', 65), str_repeat('x', 65)]] as [$current, $new, $repeated]) {
            $throws(DomainException::class, static fn () => $login->changePassword('4000', $current, $new, $repeated));
        }
        $throws(DomainException::class, static fn () => $login->changePassword('9998', 'x', 'NuevaClave1', 'NuevaClave1'));
        $login->changePassword('4000', 'Clave.Antigua', 'NuevaClave1', 'NuevaClave1');
        $row = $connection->fetchNumeric("SELECT CCLAVE, DFECFIN = CURRENT_DATE + INTERVAL 45 DAY FROM E01TUSU WHERE CCODUSU = '4000'");
        $same([true, 1], [$passwords->verify((string) $row[0], 'NuevaClave1'), (int) $row[1]]);
        $throws(DomainException::class, static fn () => $login->authenticate('4000', 'Clave.Antigua'));
        $same(false, $login->authenticate('4000', 'NuevaClave1')['must_change']);
    };
    $cases['login and password change controllers keep the legacy session contract'] = static function () use ($login, $connection, $throws, $same, $erpView): void {
        $session = new ErpSession();
        $session->logout();
        $controller = new LoginController($login, $session);
        $post = static fn (array $fields) => $controller(Request::create('/Mnu0000.php', 'POST', $fields, server: ['HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest']));
        $response = $post(['pcCodigo' => '4000', 'pcClave' => 'mala']);
        $same([401, false, ''], [$response->getStatusCode(), json_decode((string) $response->getContent(), true)['ok'], $session->user()]);
        $same(400, $post(['pcCodigo' => ['4000'], 'pcClave' => 'NuevaClave1'])->getStatusCode());
        $same(400, $post(['pcCodigo' => '4000'])->getStatusCode());
        $response = $post(['pcCodigo' => '4000', 'pcClave' => 'NuevaClave1']);
        $same([200, ['ok' => true, 'redirect' => 'Mnu0000.php?Id=0000'], '4000', 'no-store, private'],
            [$response->getStatusCode(), json_decode((string) $response->getContent(), true), $session->user(), $response->headers->get('Cache-Control')]);
        $connection->update('E01TUSU', ['DFECFIN' => null], ['CCODUSU' => '4000']);
        $same('Mnu1000.php', json_decode((string) $post(['pcCodigo' => '4000', 'pcClave' => 'NuevaClave1'])->getContent(), true)['redirect']);
        $response = $controller(Request::create('/Mnu0000.php', 'POST', ['pcCodigo' => '4000', 'pcClave' => 'NuevaClave1']));
        $same([303, '/Mnu1000.php'], [$response->getStatusCode(), $response->headers->get('Location')]);

        $change = new PasswordChangeController($login, $session, $erpView);
        $input = static fn (string $action, array $fields) => new MaintenanceInput($action, $fields, []);
        $same(200, $change(Request::create('/Mnu1000.php'), $input('', []))->getStatusCode());
        $throws(AccessDeniedHttpException::class, static fn () => $change(Request::create('/Mnu1000.php', 'POST'), $input('Grabar', ['pcClave' => 'NuevaClave1', 'pcClave1' => 'OtraClave22', 'pcClave2' => 'OtraClave22'])));
        $token = $session->token('password');
        $same(422, $change(Request::create('/Mnu1000.php', 'POST'), $input('Grabar', ['_token' => $token, 'pcClave' => 'NuevaClave1', 'pcClave1' => 'corta', 'pcClave2' => 'corta']))->getStatusCode());
        $response = $change(Request::create('/Mnu1000.php', 'POST'), $input('Grabar', ['_token' => $token, 'pcClave' => 'NuevaClave1', 'pcClave1' => 'OtraClave22', 'pcClave2' => 'OtraClave22']));
        $same([303, '/Mnu0000.php?Id=0000'], [$response->getStatusCode(), $response->headers->get('Location')]);
        $same(false, $login->authenticate('4000', 'OtraClave22')['must_change']);
        $session->logout();
        $same('/index.php', $change(Request::create('/Mnu1000.php'), $input('', []))->headers->get('Location'));
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
    if ($usageTableCreated) {
        $connection->executeStatement('DROP TABLE test_supply_usage');
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
