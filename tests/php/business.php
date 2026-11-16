<?php

declare(strict_types=1);

use App\Controller\DispatchController;
use App\Controller\OrderDocumentController;
use App\Controller\PayableController;
use App\Controller\PlanningController;
use App\Controller\ProductionController;
use App\Controller\PurchaseController;
use App\Controller\ReceivableController;
use App\Controller\SalesOrderController;
use App\Controller\WorkController;
use App\Dispatch\DispatchService;
use App\Finance\PayableBoard;
use App\Finance\PayableService;
use App\Finance\PurchaseService;
use App\Finance\ReceivableBoard;
use App\Finance\ReceivableService;
use App\Finance\TaxBreakdown;
use App\Report\OrderDocumentRtf;
use App\Report\OrderReportPdf;
use App\Repository\CatalogRepository;
use App\Repository\DbalDispatchRepository;
use App\Repository\DbalPayableRepository;
use App\Repository\DbalPurchaseRepository;
use App\Repository\DbalReceivableRepository;
use App\Repository\DbalSalesOrderRepository;
use App\Sales\OrderDocumentService;
use App\Sales\SalesOrderService;
use App\Http\MaintenanceInput;
use App\Http\MaintenanceInputResolver;
use App\Session\ErpSession;
use App\Production\Boards;
use App\Production\PlanningService;
use App\Production\ProductionService;
use App\Production\ProductionStage;
use App\Production\WorkService;
use App\Repository\DbalOrderRepository;
use App\Repository\DbalPlanningRepository;
use App\Repository\DbalProductionRepository;
use App\Repository\TransactionalTables;
use App\Security\ModulePermission;
use App\View\ErpView;
use Doctrine\DBAL\DriverManager;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/*
 * Business flows of the order, production, finance and dispatch screens.
 * Run with ERP_TEST_DATABASE_NAME set to an empty database whose name ends in _test.
 */

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
$throws = static function (string $type, callable $action, string $message = ''): void {
    try {
        $action();
    } catch (Throwable $exception) {
        if ($exception instanceof $type && str_contains($exception->getMessage(), $message)) {
            return;
        }
        throw $exception;
    }
    throw new RuntimeException('Expected exception '.$type);
};
$value = static fn (string $sql, array $parameters = []): string => (string) $connection->fetchOne($sql, $parameters);

// Grants every option in $granted and records the options that were checked.
$permissions = new class implements ModulePermission {
    /** @var list<string> */
    public array $granted = [];
    /** @var list<string> */
    public array $checked = [];

    public function assertGranted(string $user, string $option): void
    {
        $this->checked[] = $option;
        if (!\in_array($option, $this->granted, true)) {
            throw new AccessDeniedHttpException('No tiene permiso para esta opción.');
        }
    }
};
$resolver = new MaintenanceInputResolver();
$argument = new ArgumentMetadata('input', MaintenanceInput::class, false, false, null);
$post = static fn (array $fields): MaintenanceInput => iterator_to_array($resolver->resolve(Request::create('/', 'POST', $fields), $argument))[0];
$views = new ErpView(new Twig\Environment(new Twig\Loader\FilesystemLoader(dirname(__DIR__, 2).'/templates'), ['strict_variables' => true]));
$session = new ErpSession();

$tables = ['E01TTAB', 'E01MCLI', 'E01MPROD', 'E01MMAQ', 'E01MPER', 'E01CPED', 'E01DPED', 'E01MAQ', 'E01PER', 'E01TRABAJO', 'E01DCRE',
    'E01MPRV', 'E01MINS', 'E01PPROD', 'E01CCOM', 'E02DCOM', 'E01MBAN', 'E01PCTA'];
$views_ = ['V_E01TTAB', 'V_E01CPED', 'V_E01DPED'];
$ownedTables = [];
$ownedViews = [];
$failures = [];
$cases = [];
try {
    $dump = file_get_contents(dirname(__DIR__, 2).'/docker/mysql/init/01-textileria.sql');
    foreach ($tables as $table) {
        if (!preg_match('/CREATE TABLE `'.preg_quote($table, '/').'` \([\s\S]*?\) ENGINE=[^;]+;/', $dump, $match)) {
            throw new RuntimeException('Missing schema for '.$table);
        }
        $connection->executeStatement(str_replace('ENGINE=MyISAM', 'ENGINE=InnoDB', $match[0]));
        $ownedTables[] = $table;
    }
    foreach ($views_ as $view) {
        if (!preg_match('/VIEW `'.$view.'` AS (select [\s\S]*?) \*\/;/', $dump, $match)) {
            throw new RuntimeException('Missing view '.$view);
        }
        $connection->executeStatement('CREATE VIEW '.$view.' AS '.$match[1]);
        $ownedViews[] = $view;
    }
    $catalog = ['002' => ['A' => 'ALTA'], '005' => ['T' => 'TELAR'], '009' => ['C' => 'COMPLETO', 'I' => 'INCOMPLETO'],
        '010' => ['E' => 'EJECUTADO', 'P' => 'PENDIENTE'],
        '012' => ['A' => 'TEÑIDO', 'B' => 'PREPARACION', 'C' => 'TEJIDO', 'D' => 'ENSAMBLAJE', 'E' => 'ACABADO', 'I' => 'INCOMPLETO'],
        '001' => ['A' => 'ACTIVO', 'I' => 'INACTIVO'], '007' => ['U' => 'UNIDAD'], '098' => ['A' => 'PERSONA NATURAL', 'C' => 'EMPRESA']];
    foreach ($catalog as $table => $entries) {
        foreach ($entries as $code => $description) {
            $connection->insert('E01TTAB', ['CCODTAB' => $table, 'CTIPREG' => '1', 'CCODIGO' => $code, 'CDESCRI' => $description]);
        }
    }

    $boards = new Boards();
    $orders = new DbalOrderRepository($connection);
    $innoDb = new TransactionalTables($connection);
    $planning = new PlanningService($orders, new DbalPlanningRepository($connection, $innoDb), $permissions);
    $productionRepository = new DbalProductionRepository($connection, $innoDb);
    $production = new ProductionService($orders, $productionRepository, $permissions);
    $work = new WorkService($productionRepository, $permissions);

    $connection->insert('E01MCLI', ['CCODCLI' => '000001', 'CNOMBRE' => 'CLIENTE UNO']);
    $connection->insert('E01MPROD', ['CCODPROD' => '000001', 'CDESCRI' => 'CHOMPA']);
    $connection->insert('E01MPROD', ['CCODPROD' => '000002', 'CDESCRI' => 'CHALINA']);
    // Machine 000000 comes first so that a job that shows the first machine of the table fails.
    foreach (['000000' => 'OCUPADA', '000001' => 'TELAR UNO', '000002' => 'TELAR DOS'] as $code => $name) {
        $connection->insert('E01MMAQ', ['CCODMAQ' => $code, 'CDESCRI' => $name, 'CTIPO' => 'T', 'CESTADOOP' => '000000' === $code ? 'O' : 'L']);
    }
    foreach (['000014' => 'ANA', '000015' => 'LUIS', '999999' => 'ADMINISTRADOR'] as $code => $name) {
        $connection->insert('E01MPER', ['CCODPER' => $code, 'CNOMBRE' => $name, 'CESTADOOP' => 'L']);
    }
    $connection->insert('E01CPED', ['CCODPED' => '000001', 'CCODCLI' => '000001', 'NTOTAL' => 100, 'DFECLIS' => '2026-12-01',
        'CPRIORI' => 'A', 'CESTADOPL' => 'I', 'CESTADOPR' => 'P', 'CCODUSU' => '9999']);
    foreach (['000001', '000002'] as $product) {
        $connection->insert('E01DPED', ['CCODPED' => '000001', 'CCODPROD' => $product, 'NCANT' => 5, 'CESTADOPL' => 'I',
            'CESTADOPR' => 'P', 'CPROCESO' => 'I', 'CCODUSU' => '9999']);
    }

    $cases['logout removes the user identity and keeps other session data'] = static function () use ($same): void {
        if (\PHP_SESSION_ACTIVE !== session_status()) {
            session_start();
        }
        $saved = $_SESSION;
        $_SESSION = ['gcNombre' => 'User', 'gcCodUsu' => '9999', 'gcTipo' => 'A', 'login_change_password' => true, 'workspace' => 'ERP1000'];
        (new ErpSession())->logout();
        $same(['workspace' => 'ERP1000'], $_SESSION);
        $_SESSION = $saved;
    };

    $cases['the stages follow the legacy process order and stop after finishing'] =static function () use ($same, $throws): void {
        $stages = [];
        for ($stage = ProductionStage::Pending; ProductionStage::Finishing !== $stage; $stage = $stage->next()) {
            $stages[] = $stage->value;
        }
        $same(['I', 'A', 'B', 'C', 'D'], $stages);
        $throws(DomainException::class, static fn () => ProductionStage::Finishing->next());
    };

    $cases['planning reserves machines and staff and completes the order with its last line'] = static function () use ($same, $throws, $value, $planning, $boards, $permissions): void {
        $permissions->granted = ['ERP3000', 'ERP3010'];
        $board = $boards->planning('ERP3000');
        $same([['000001', '000001', 'CLIENTE UNO', 'INCOMPLETO', 'ALTA', '2026-12-01']], $planning->orders('0001', $board));
        $start = $planning->start('0001', $board, '000001', '000001');
        $same([['000001', 'TELAR UNO'], ['000002', 'TELAR DOS']], $start['machines']);
        $same([['000014', 'ANA'], ['000015', 'LUIS']], $start['staff']);
        $same(['000001', 'TELAR UNO', 'TELAR'], $planning->machine('0001', $board, '000001'));
        $throws(DomainException::class, static fn () => $planning->machine('0001', $board, '000000'), 'libre');
        $throws(DomainException::class, static fn () => $planning->person('0001', $board, '999999'), 'libre');
        $throws(DomainException::class, static fn () => $planning->plan('0001', $board, '000001', '000001', [], ['000014']), 'máquina');

        $planning->plan('0001', $board, '000001', '000001', ['000001'], ['000014']);
        $same('O', $value("SELECT CESTADOOP FROM E01MMAQ WHERE CCODMAQ = '000001'"));
        $same('O', $value("SELECT CESTADOOP FROM E01MPER WHERE CCODPER = '000014'"));
        $same('L|A', $value("SELECT CONCAT(CESTADOOP, '|', CESTADO) FROM E01MAQ WHERE CCODPED = '000001' AND CCODPROD = '000001'"));
        $same('C', $value("SELECT CESTADOPL FROM E01DPED WHERE CCODPED = '000001' AND CCODPROD = '000001'"));
        $same('I', $value("SELECT CESTADOPL FROM E01CPED WHERE CCODPED = '000001'"));
        $throws(DomainException::class, static fn () => $planning->start('0001', $board, '000001', '000001'), 'ya fue planificado');
        $throws(DomainException::class, static fn () => $planning->plan('0001', $board, '000001', '000001', ['000002'], ['000015']), 'ya fue planificado');

        // A machine taken in the meantime rolls back the whole line.
        $throws(DomainException::class, static fn () => $planning->plan('0001', $board, '000001', '000002', ['000001'], ['000015']), 'máquinas');
        $same('L', $value("SELECT CESTADOOP FROM E01MPER WHERE CCODPER = '000015'"));
        $same('0', $value("SELECT COUNT(*) FROM E01PER WHERE CCODPROD = '000002'"));

        $planning->plan('0001', $board, '000001', '000002', ['000002'], ['000015']);
        $same('C', $value("SELECT CESTADOPL FROM E01CPED WHERE CCODPED = '000001'"));
        $throws(DomainException::class, static fn () => $planning->lines('0001', $boards->planning('ERP3010'), '000001'), 'consulta');
        $same(1, \count($planning->orders('0001', $boards->planning('ERP3010'))));
    };

    $cases['production assigns each process and the worker finishes it until the order is complete'] = static function () use ($same, $throws, $value, $production, $work, $boards, $permissions): void {
        $permissions->granted = ['ERP4000', 'ERP4050', 'ERP8000'];
        $board = $boards->production('ERP4000');
        $same([['000001', '000001', 'CLIENTE UNO', 'PENDIENTE', 'ALTA', '2026-12-01']], $production->orders('0014', $board));
        $throws(DomainException::class, static fn () => $work->job('0014'), 'no tiene trabajo');
        $throws(DomainException::class, static fn () => $production->assign('0014', $board, '000001', '000001', '000002', '000014'), 'máquina reservada');
        $names = ['A' => 'TEÑIDO', 'B' => 'PREPARACION', 'C' => 'TEJIDO', 'D' => 'ENSAMBLAJE', 'E' => 'ACABADO'];

        foreach ([ProductionStage::Dyeing, ProductionStage::Preparation, ProductionStage::Weaving, ProductionStage::Assembly, ProductionStage::Finishing] as $stage) {
            foreach (['000001' => ['000001', '000014', '0014'], '000002' => ['000002', '000015', '0015']] as $product => [$machine, $person, $worker]) {
                $production->assign('0014', $board, '000001', $product, $machine, $person);
                $same($stage->value.'|P', $value("SELECT CONCAT(CPROCESO, '|', CESTADOPR) FROM E01DPED WHERE CCODPED = '000001' AND CCODPROD = :product", ['product' => $product]));
                $throws(DomainException::class, static fn () => $production->assignment('0014', $board, '000001', $product), 'en proceso');
                $job = $work->job($worker);
                $same([$product, $machine, $names[$stage->value]], [$job['product'], $job['machine'], $job['process']]);
                $same(['TELAR UNO', 'TELAR DOS'][(int) $product - 1], $job['machineDescription']);
                $work->finish($worker);
                $same('E', $value("SELECT CESTADOPR FROM E01DPED WHERE CCODPED = '000001' AND CCODPROD = :product", ['product' => $product]));
            }
        }
        $same('E', $value("SELECT CESTADOPR FROM E01CPED WHERE CCODPED = '000001'"));
        $same('L|L', $value("SELECT CONCAT((SELECT CESTADOOP FROM E01MMAQ WHERE CCODMAQ = '000001'), '|', (SELECT CESTADOOP FROM E01MPER WHERE CCODPER = '000015'))"));
        $same('0', $value("SELECT COUNT(*) FROM E01MAQ WHERE CESTADO = 'A'"));
        $same('10', $value("SELECT COUNT(*) FROM E01TRABAJO WHERE CESTADO = 'I'"));
        $throws(DomainException::class, static fn () => $production->assignment('0014', $board, '000001', '000001'), 'terminó');
        $same([], $production->orders('0014', $board));

        $permissions->checked = [];
        $same(2, \count($production->stageLines('0014', $boards->stage('ERP4050'))));
        $same(['ERP4050'], $permissions->checked);
    };

    $cases['the planning screen walks from the order list to the saved plan'] = static function () use ($same, $connection, $session, $post, $views, $boards, $planning): void {
        $connection->insert('E01CPED', ['CCODPED' => '000002', 'CCODCLI' => '000001', 'CPRIORI' => 'A', 'CESTADOPL' => 'I', 'CESTADOPR' => 'P']);
        $connection->insert('E01DPED', ['CCODPED' => '000002', 'CCODPROD' => '000001', 'NCANT' => 1, 'CESTADOPL' => 'I', 'CESTADOPR' => 'P', 'CPROCESO' => 'I']);
        $controller = new PlanningController($planning, $boards, $session, $views);
        $call = static fn (array $fields) => $controller(Request::create('/ERP3000.php', 'POST'), 'ERP3000', $post(['_token' => $session->token('planning_ERP3000')] + $fields));
        $session->set('gcCodUsu', '0001');
        $page = $controller(Request::create('/ERP3000.php'), 'ERP3000', new MaintenanceInput('', [], []));
        $same(true, str_contains((string) $page->getContent(), 'value="000002"'));
        $same(200, $call(['Boton0' => 'Detalle', 'pcCodPed' => '000002'])->getStatusCode());
        $same(true, str_contains((string) $call(['Boton1' => 'Asignar', 'pcCodProd' => '000001'])->getContent(), 'TELAR UNO'));
        $call(['BotonM' => 'Agregar', 'pcCodMaq' => '000001']);
        $same(true, str_contains((string) $call(['BotonP' => 'Agregar', 'pcCodPer' => '000014'])->getContent(), 'ANA'));
        $saved = $call(['Boton2' => 'Grabar']);
        $same([303, '/ERP3000.php'], [$saved->getStatusCode(), $saved->headers->get('Location')]);
        $same('C', (string) $connection->fetchOne("SELECT CESTADOPL FROM E01CPED WHERE CCODPED = '000002'"));
        $same(true, str_contains((string) $controller(Request::create('/ERP3000.php'), 'ERP3000', new MaintenanceInput('', [], []))->getContent(), 'Grabación conforme.'));
        $same(422, $call(['Boton0' => 'Detalle', 'pcCodPed' => ''])->getStatusCode());
    };

    $cases['the production and work screens render every step'] = static function () use ($same, $session, $post, $views, $boards, $production, $work): void {
        $controller = new ProductionController($production, $boards, $session, $views);
        $call = static fn (array $fields) => $controller->orders(Request::create('/ERP4070.php', 'POST'), 'ERP4070', $post(['_token' => $session->token('production_ERP4070')] + $fields));
        $session->set('gcCodUsu', '0001');
        $same(200, $controller->orders(Request::create('/ERP4070.php'), 'ERP4070', new MaintenanceInput('', [], []))->getStatusCode());
        $same(true, str_contains((string) $call(['Boton0' => 'Detalle', 'pcCodPed' => '000002'])->getContent(), 'pcCodProd'));
        $same(true, str_contains((string) $call(['Boton1' => 'Asignar', 'pcCodProd' => '000001'])->getContent(), 'TELAR UNO'));
        $same(303, $call(['Boton2' => 'Grabar', 'pcCodMaq' => '000001', 'pcCodPer' => '000014'])->getStatusCode());
        $same(true, str_contains((string) $controller->stage(Request::create('/ERP4010.php'), 'ERP4010', new MaintenanceInput('', [], []))->getContent(), '000002'));

        $session->set('gcCodUsu', '0014');
        $screen = new WorkController($work, $session, $views);
        $same(true, str_contains((string) $screen(Request::create('/ERP8000.php'), new MaintenanceInput('', [], []))->getContent(), 'TELAR UNO'));
        $same(303, $screen(Request::create('/ERP8000.php', 'POST'), $post(['_token' => $session->token('work'), 'Boton0' => 'Hecho']))->getStatusCode());
        $same(true, str_contains((string) $screen(Request::create('/ERP8000.php'), new MaintenanceInput('', [], []))->getContent(), 'no tiene trabajo'));
    };

    $supplier = ['cNroDni' => '99999999', 'cTipPer' => 'C', 'cNroRuc' => '20999999991', 'cDirecc' => 'CALLE 1', 'dFecNac' => '2000-01-01',
        'cEstado' => 'A', 'mDatos' => '', 'cCodUsu' => '9999'];
    $connection->insert('E01MPRV', ['cCodPrv' => '000001', 'cNombre' => 'PROVEEDOR UNO'] + $supplier);
    $connection->insert('E01MINS', ['cCodIns' => '010001', 'cDescri' => 'HILO', 'cUniMed' => 'U', 'nPrecio' => '2.50']);
    $connection->insert('E01MINS', ['cCodIns' => '010002', 'cDescri' => 'TINTE', 'cUniMed' => 'U', 'nPrecio' => '4.00']);
    $connection->insert('E01CCOM', ['cCodCom' => '000001', 'cCodPrv' => '000001', 'cEstado' => 'A', 'CCODUSU' => '9999', 'dFecLis' => '2026-11-01', 'cCodBan' => '']);
    $connection->insert('E01CCOM', ['cCodCom' => '000002', 'cCodPrv' => '000001', 'cEstado' => 'I', 'CCODUSU' => '9999', 'dFecLis' => '2026-10-01', 'cCodBan' => '000001']);
    $connection->insert('E02DCOM', ['CCODCOM' => '000001', 'CCODINS' => '010001', 'NCANT' => 10]);
    $connection->insert('E02DCOM', ['CCODCOM' => '000002', 'CCODINS' => '010002', 'NCANT' => 100]);
    $connection->insert('E01MBAN', ['CCODBAN' => '000001', 'CNOMBAN' => 'BANCO UNO']);
    $connection->insert('E01PCTA', ['CCODBAN' => '000001', 'CCODPRV' => '999999', 'NCUENTA' => '111']);
    $connection->insert('E01PCTA', ['CCODBAN' => '999999', 'CCODPRV' => '000001', 'NCUENTA' => '222']);
    $connection->executeStatement("UPDATE E01MCLI SET CTIPO = 'C', CNRODNI = '99999999', CNRORUC = '20123456789', CDIREC = 'AV. SOL 1', CTELEF = '054111'");
    $connection->executeStatement("UPDATE E01MPROD SET CTIPO = 'A'");
    $connection->insert('E01PPROD', ['CCODPROD' => '000001', 'CCODINS' => '010001']);
    $connection->insert('E01PPROD', ['CCODPROD' => '000001', 'CCODINS' => '010002']);
    $receivables = new ReceivableService(new DbalReceivableRepository($connection, $innoDb), $permissions);
    $payables = new PayableService(new DbalPayableRepository($connection, $innoDb), $permissions);
    $purchases = new PurchaseService(new DbalPurchaseRepository($connection, $innoDb), $permissions);
    $dispatch = new DispatchService(new DbalDispatchRepository($connection, $innoDb), new OrderReportPdf(), $permissions);
    $salesRepository = new DbalSalesOrderRepository($connection, $innoDb);
    $sales = new SalesOrderService($salesRepository, new CatalogRepository($connection), $permissions);
    $documents = new OrderDocumentService($salesRepository, new OrderDocumentRtf(dirname(__DIR__, 2).'/templates/documents/order.rtf'), $permissions);

    $cases['the IGV split keeps the legacy rounding'] = static function () use ($same): void {
        $totals = TaxBreakdown::fromTotal(118.5);
        $same([100.42, 18.08, 118.5], [$totals->saleValue, $totals->tax, $totals->total]);
    };

    $cases['receivables split the stored order total into installments once'] = static function () use ($same, $throws, $value, $receivables, $permissions): void {
        $same(['000001', '000002'], array_column($receivables->orders('0001', ReceivableBoard::AllOrders), 0));
        $same(100, $receivables->total('0001', ReceivableBoard::AllOrders, '000001'));
        $throws(DomainException::class, static fn () => $receivables->createPlan('0001', ReceivableBoard::AllOrders, '000001', '0', ''), 'número de pagos');
        $throws(DomainException::class, static fn () => $receivables->createPlan('0001', ReceivableBoard::AllOrders, '000001', '4', '2026-02-30'), 'fecha');
        $receivables->createPlan('0001', ReceivableBoard::AllOrders, '000001', '4', '2026-12-31');
        $same('000001|100|25|2026-12-31', $value("SELECT CONCAT(CCODCRE, '|', NRESTAN, '|', NLETRA, '|', DFECLIM) FROM E01DCRE"));
        $throws(DomainException::class, static fn () => $receivables->createPlan('0001', ReceivableBoard::AllOrders, '000001', '2', ''), 'ya tiene letras');
        $same([['000001', '000001', 'CLIENTE UNO', 'EJECUTADO', 'ALTA', '100', '0', '0.0000']], $receivables->orders('0001', ReceivableBoard::WithInstallments));
        $permissions->checked = [];
        $same([], $receivables->orders('0001', ReceivableBoard::Delivered));
        $same(['ERP6030'], $permissions->checked);
    };

    $cases['payables add up the lines of each invoice and pay from a bank account'] = static function () use ($same, $throws, $value, $payables): void {
        $same([['000001', '000001', 'PROVEEDOR UNO', '2026-11-01', '25.00']], $payables->invoices('0001', PayableBoard::Pending));
        $detail = $payables->detail('0001', PayableBoard::Pending, '000001');
        $same([['010001', 'HILO', 'UNIDAD', '2.50', '10', '25.00']], $detail['lines']);
        $same(25.0, $detail['totals']->total);
        $throws(DomainException::class, static fn () => $payables->detail('0001', PayableBoard::Pending, '000002'), 'no está en esta lista');
        $same([['222', '222']], $payables->paymentOptions('0001', PayableBoard::Pending, '000001')['supplierAccounts']);
        $throws(DomainException::class, static fn () => $payables->pay('0001', PayableBoard::Pending, '000001', '000001', '222'), 'cuenta del banco');
        $throws(DomainException::class, static fn () => $payables->paymentOptions('0001', PayableBoard::Paid, '000002'), 'consulta');
        $payables->pay('0001', PayableBoard::Pending, '000001', '000001', '111');
        $same('I|111|000001|'.date('Y-m-d'), $value("SELECT CONCAT(cEstado, '|', cCtaPag, '|', cCodBan, '|', dFecLis) FROM E01CCOM WHERE cCodCom = '000001'"));
        $throws(DomainException::class, static fn () => $payables->pay('0001', PayableBoard::Pending, '000001', '000001', '111'), 'no está en esta lista');
    };

    $cases['purchases create unpaid invoices for the payable screen'] = static function () use ($same, $throws, $value, $purchases, $payables): void {
        $throws(DomainException::class, static fn () => $purchases->create('0001', '000009', ['010001' => '5']), 'proveedor');
        $throws(DomainException::class, static fn () => $purchases->create('0001', '000001', ['010001' => '0']), 'entero positivo');
        $throws(DomainException::class, static fn () => $purchases->create('0001', '000001', []), 'al menos un insumo');
        $same(['010001', 'HILO', 'UNIDAD', '2.50'], $purchases->supply('0001', '010001'));
        $same('000003', $purchases->create('0001', '000001', ['010001' => '5', '010002' => '1']));
        $same('2', $value("SELECT COUNT(*) FROM E02DCOM WHERE CCODCOM = '000003'"));
        $same([['000003', '000001', 'PROVEEDOR UNO', date('Y-m-d'), '16.50']], $payables->invoices('0001', PayableBoard::Pending));
    };

    $cases['dispatch delivers finished orders once and prints their report'] = static function () use ($same, $throws, $value, $dispatch): void {
        $same([['000001', '000001', 'CLIENTE UNO', '2026-12-01', 'PENDIENTE']], $dispatch->orders('0001'));
        $throws(DomainException::class, static fn () => $dispatch->deliver('0001', '000002'), 'no terminó');
        $same('Pedido entregado.', $dispatch->deliver('0001', '000001'));
        $same('I', $value("SELECT CESTADO FROM E01CPED WHERE CCODPED = '000001'"));
        $same('El pedido ya había sido entregado.', $dispatch->deliver('0001', '000001'));
        $same('ENTREGADO', $dispatch->orders('0001')[0][4]);
        $same('%PDF-', substr($dispatch->report('0001', '000001'), 0, 5));
        $throws(DomainException::class, static fn () => $dispatch->report('0001', '000999'), 'no tiene productos');
    };

    $cases['the order document matches the legacy RTF output'] = static function () use ($same): void {
        $document = new OrderDocumentRtf(dirname(__DIR__, 2).'/templates/documents/order.rtf');
        $totals = TaxBreakdown::fromTotal(118.5);
        // tests/php/fixtures/order-document.rtf was produced by the legacy RTFTable and CRtf code with the same data.
        $same(file_get_contents(__DIR__.'/fixtures/order-document.rtf'), $document->render(
            ['code' => '000123', 'name' => 'TEJIDOS PEÑA S.A.C.', 'address' => 'AV. LOS ANDES 123', 'number' => '20123456789', 'phone' => '054123456',
                'saleValue' => $totals->saleValue, 'tax' => $totals->tax, 'total' => $totals->total],
            [['CHOMPA DE ALPACA', '25.5', '3', '76.5'], ['CHALINA', '12', '3', '36']],
        ));
        $same(true, str_contains($document->render(['code' => '1', 'name' => 'A {B} \\C', 'address' => '', 'number' => '', 'phone' => '',
            'saleValue' => 0.0, 'tax' => 0.0, 'total' => 0.0], []), 'A \{B\} \\\\C'));
    };

    $cases['orders are priced from the recipe and saved with their document'] = static function () use ($same, $throws, $value, $sales, $documents): void {
        $same(['000001', 'CHOMPA', 6.5], $sales->product('0001', '000001'));
        $throws(DomainException::class, static fn () => $sales->product('0001', '000002'), 'receta');
        $same([['000001', '20123456789']], $sales->options('0001', false)['clients']);
        $same(['name' => 'CLIENTE UNO', 'address' => 'AV. SOL 1', 'phone' => '054111'], $sales->client('0001', false, '000001'));
        $throws(DomainException::class, static fn () => $sales->client('0001', true, '000001'), 'cliente');
        $quote = $sales->quote('0001', ['000001' => '3']);
        $same([['000001', 'CHOMPA', 6.5, 3, 19.5]], $quote['lines']);
        $throws(DomainException::class, static fn () => $sales->create('0001', false, '000001', 'A', '20-12-2026', ['000001' => '3']), 'fecha');
        $throws(DomainException::class, static fn () => $sales->create('0001', false, '000001', 'Z', '2026-12-20', ['000001' => '3']), 'prioridad');
        $code = $sales->create('0001', false, '000001', 'A', '2026-12-20', ['000001' => '3']);
        $same('000003', $code);
        $same('20|I|P', $value("SELECT CONCAT(NTOTAL, '|', CESTADOPL, '|', CESTADOPR) FROM E01CPED WHERE CCODPED = '000003'"));
        $same('3|I|P|I', $value("SELECT CONCAT(NCANT, '|', CESTADOPL, '|', CESTADOPR, '|', CPROCESO) FROM E01DPED WHERE CCODPED = '000003'"));
        $rtf = $documents->render('0001', 'ERP2010', $code);
        $same(true, str_contains($rtf, '20123456789') && str_contains($rtf, '\qc CHOMPA\cell') && str_contains($rtf, '\qc 19.5\cell'));
        $same(['000003'], array_column($documents->pendingOrders('0001'), 0));
        $throws(DomainException::class, static fn () => $documents->render('0001', 'ERP1040', $code));
    };

    $cases['the finance, purchase, dispatch and order screens render every step'] = static function () use ($same, $session, $post, $views, $receivables, $payables, $purchases, $dispatch, $sales, $documents): void {
        $session->set('gcCodUsu', '0001');
        $plain = new MaintenanceInput('', [], []);
        $receivable = new ReceivableController($receivables, $session, $views);
        $same(true, str_contains((string) $receivable(Request::create('/ERP6030.php'), 'ERP6030', $plain)->getContent(), 'CLIENTE UNO'));
        $same(true, str_contains((string) $receivable(Request::create('/ERP6020.php'), 'ERP6020', $plain)->getContent(), 'No hay facturas pendientes.'));
        $call = static fn (array $fields) => $receivable(Request::create('/ERP6010.php', 'POST'), 'ERP6010', $post(['_token' => $session->token('receivables_ERP6010')] + $fields));
        $same(true, str_contains((string) $call(['Boton0' => 'Aplicar', 'pcCodPed' => '000003'])->getContent(), 'jnPago'));
        $same(303, $call(['Boton1' => 'Grabar', 'pnPago' => '2', 'pcFecLim' => ''])->getStatusCode());

        $payable = new PayableController($payables, $session, $views);
        $same(true, str_contains((string) $payable(Request::create('/ERP7020.php'), 'ERP7020', $plain)->getContent(), '000001'));
        $call = static fn (array $fields) => $payable(Request::create('/ERP7010.php', 'POST'), 'ERP7010', $post(['_token' => $session->token('payables_ERP7010')] + $fields));
        $same(true, str_contains((string) $call(['Boton0' => 'Detalle', 'pcCodFac' => '000003'])->getContent(), 'TINTE'));
        $same(true, str_contains((string) $call(['Boton1' => 'Pagar'])->getContent(), 'BANCO UNO'));
        $same(true, str_contains((string) $call(['Boton2' => 'Cargar', 'pcCodBan' => '000001'])->getContent(), 'value="111"'));
        $same(303, $call(['Boton2' => 'Pagar', 'pcCodBan' => '000001', 'pcCtaBan' => '111'])->getStatusCode());

        $purchase = new PurchaseController($purchases, $session, $views);
        $call = static fn (array $fields) => $purchase(Request::create('/ERP5010.php', 'POST'), $post(['_token' => $session->token('purchases')] + $fields));
        $same(200, $purchase(Request::create('/ERP5010.php'), $plain)->getStatusCode());
        $same(true, str_contains((string) $call(['Boton1' => 'Agregar', 'pcCodPrv' => '000001', 'pcCodIns' => '010002'])->getContent(), 'TINTE'));
        $saved = $call(['Boton1' => 'Grabar', 'pcCodPrv' => '000001', 'paDataI' => ['010002' => ['010002', 'TINTE', 'UNIDAD', '4.00', '7']]]);
        $same(true, str_contains((string) $saved->getContent(), 'Factura 000004 registrada'));

        $screen = new DispatchController($dispatch, $session, $views);
        $applied = $screen(Request::create('/ERP9010.php', 'POST'), $post(['_token' => $session->token('dispatch'), 'Boton0' => 'Aplicar', 'pcCodPed' => '000001']));
        $same(303, $applied->getStatusCode());
        $same(true, str_contains((string) $screen(Request::create('/ERP9010.php'), $plain)->getContent(), '/ERP9010/000001.pdf'));
        $same('application/pdf', $screen->report('000001')->headers->get('Content-Type'));

        $order = new SalesOrderController($sales, $session, $views);
        $call = static fn (array $fields) => $order(Request::create('/ERP2010.php', 'POST'), $post(['_token' => $session->token('sales_order')] + $fields));
        $same(true, str_contains((string) $order(Request::create('/ERP2010.php'), $plain)->getContent(), 'EMPRESA'));
        $same(true, str_contains((string) $call(['Boton0' => 'Aceptar', 'pcTipo' => 'C'])->getContent(), '20123456789'));
        $header = ['pcNroRUC' => '000001', 'pcPriori' => 'A', 'pdFecLis' => '2026-12-24'];
        $same(true, str_contains((string) $call(['Boton' => 'Cargar Datos'] + $header)->getContent(), 'AV. SOL 1'));
        $same(true, str_contains((string) $call(['Boton2' => 'Agregar', 'pcCodProd' => '000001'] + $header)->getContent(), 'CHOMPA'));
        $calculated = (string) $call(['Boton3' => 'Calcular', 'paDataP' => ['000001' => ['000001', 'CHOMPA', '6.5', '2']]] + $header)->getContent();
        $same(true, str_contains($calculated, 'value="13"'));
        $same(true, str_contains((string) $call(['Boton4' => 'Grabar'] + $header)->getContent(), '/ERP2010/000004.rtf'));

        $list = new OrderDocumentController($documents, $session, $views);
        $detail = $list(Request::create('/ERP2020.php', 'POST'), $post(['_token' => $session->token('order_documents'), 'Boton0' => 'Detalle', 'pcCodPed' => '000004']));
        $same(true, str_contains((string) $detail->getContent(), '/ERP2020/000004.rtf'));
        $same('application/rtf', $list->document('ERP2020', '000004')->headers->get('Content-Type'));
    };

    foreach ($cases as $name => $case) {
        try {
            $permissions->granted = ['ERP2010', 'ERP2020', 'ERP3000', 'ERP3010', 'ERP4000', 'ERP4010', 'ERP4050', 'ERP4070', 'ERP5010',
                'ERP6010', 'ERP6020', 'ERP6030', 'ERP7010', 'ERP7020', 'ERP8000', 'ERP9010'];
            $case();
        } catch (Throwable $exception) {
            $failures[] = $name.': '.$exception->getMessage().' at line '.($exception->getTrace()[0]['line'] ?? $exception->getLine());
        }
    }
} finally {
    foreach (array_reverse($ownedViews) as $view) {
        $connection->executeStatement('DROP VIEW '.$view);
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
