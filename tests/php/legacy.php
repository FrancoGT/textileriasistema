<?php

declare(strict_types=1);

use App\Controller\LegacyController;
use App\Legacy\LegacyAssetResponseFactory;
use App\Legacy\LegacyPageExecutor;
use App\Legacy\LegacyPathResolver;
use App\Legacy\LegacyResponseFactory;
use App\Legacy\LegacyScriptRunner;
use App\Legacy\LegacySession;
use App\Legacy\LegacyVariableCollector;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

require dirname(__DIR__, 2).'/vendor/autoload.php';

$directory = sys_get_temp_dir().'/textileria-legacy-'.bin2hex(random_bytes(8));
mkdir($directory);
$files = [];
$fixture = static function (string $name, string $content) use ($directory, &$files): string {
    $file = $directory.'/'.$name;
    file_put_contents($file, $content);
    $files[] = $file;

    return $file;
};
$same = static function (mixed $expected, mixed $actual): void {
    if ($expected !== $actual) {
        throw new RuntimeException('Expected '.var_export($expected, true).', got '.var_export($actual, true));
    }
};
$cases = [];

$cases['logout removes legacy identity and preserves other session data'] = static function () use ($same): void {
    session_start();
    $_SESSION = ['gcNombre' => 'User', 'gcCodUsu' => '9999', 'gcTipo' => 'A', 'login_change_password' => true, 'workspace' => 'ERP1000'];
    (new LegacySession())->logout();
    $same(['workspace' => 'ERP1000'], $_SESSION);
    session_write_close();
};

$cases['variable collection excludes superglobals and execution variables'] = static function () use ($fixture, $same): void {
    $file = $fixture('variables.php', '<?php $loSmarty = null; $loSmarty = 1; $_SESSION = []; $GLOBALS; $__legacyScript; $argc; $argv; $this; $customer;');
    $collector = new LegacyVariableCollector();
    $same(['loSmarty', 'customer'], $collector->collect($file));
    $same(['loSmarty', 'customer'], $collector->collect($file));
};

$cases['response conversion preserves status, redirects, MIME and repeated cookies'] = static function () use ($same): void {
    $factory = new LegacyResponseFactory();
    $response = $factory->create('content', 302, [
        'Location: /Mnu0000.php', 'Content-Type: application/json', 'X-Powered-By: PHP',
        'Set-Cookie: first=one; Path=/', 'Set-Cookie: second=two; Path=/',
    ]);
    $same(302, $response->getStatusCode());
    $same('content', $response->getContent());
    $same('/Mnu0000.php', $response->headers->get('Location'));
    $same('application/json', $response->headers->get('Content-Type'));
    $same(false, $response->headers->has('X-Powered-By'));
    $same(2, \count($response->headers->getCookies()));
    $same('text/html; charset=UTF-8', $factory->create('', 200, [])->headers->get('Content-Type'));
};

$cases['asset responses require revalidation and support conditional requests'] = static function () use ($fixture, $same): void {
    $file = $fixture('menu.css', 'body { margin: 0; }');
    $factory = new LegacyAssetResponseFactory();
    $response = $factory->create(Request::create('/CSS/menu.css'), 'CSS/menu.css', $file);
    $same('text/css', $response->headers->get('Content-Type'));
    $same(true, $response->headers->hasCacheControlDirective('public'));
    $same(true, $response->headers->hasCacheControlDirective('no-cache'));
    $same(false, $response->headers->hasCacheControlDirective('max-age'));
    $request = Request::create('/CSS/menu.css');
    $request->headers->set('If-None-Match', (string) $response->getEtag());
    $same(304, $factory->create($request, 'CSS/menu.css', $file)->getStatusCode());
    foreach (['Ficheros/menu.css', 'FILES/menu.css'] as $path) {
        $private = $factory->create(Request::create('/'.$path), $path, $file);
        $same(true, $private->headers->hasCacheControlDirective('private'));
        $same(true, $private->headers->hasCacheControlDirective('no-store'));
    }
};

$cases['controller dispatches through the executor contract and rejects internal paths'] = static function () use ($directory, $fixture, $same): void {
    $file = $fixture('ERP1000.php', '<?php echo "Personal";');
    $executor = new class implements LegacyPageExecutor {
        public ?string $script = null;

        public function run(string $script): Response
        {
            $this->script = $script;

            return new Response('Personal');
        }
    };
    $resolver = new LegacyPathResolver($directory);
    $controller = new LegacyController($resolver, $executor, new LegacyAssetResponseFactory());
    $same('Personal', $controller(Request::create('/ERP1000.php'), 'ERP1000.php')->getContent());
    $same(realpath($file), $executor->script);
    foreach (['../ERP1000.php', 'Clases/ERP1000.php', '.env', 'data.sql'] as $path) {
        try {
            $controller(Request::create('/'), $path);
            throw new RuntimeException('Internal path was served: '.$path);
        } catch (NotFoundHttpException) {
        }
    }
};

$cases['execution restores process state after success and exceptions'] = static function () use ($directory, $fixture, $same): void {
    $runner = new LegacyScriptRunner($directory, new LegacyVariableCollector(), new LegacyResponseFactory());
    $cwd = getcwd();
    $includePath = get_include_path();
    $bufferLevel = ob_get_level();
    $success = $fixture('success.php', '<?php $legacyTestValue = "Personal"; function legacy_test_output(): void { global $legacyTestValue; echo $legacyTestValue; } legacy_test_output(); ob_start(); echo " module";');
    $same('Personal module', $runner->run($success)->getContent());
    $same($cwd, getcwd());
    $same($includePath, get_include_path());
    $same($bufferLevel, ob_get_level());
    $failure = $fixture('failure.php', '<?php ob_start(); echo "discard"; throw new RuntimeException("expected failure");');
    try {
        $runner->run($failure);
        throw new LogicException('Expected the script exception.');
    } catch (RuntimeException $exception) {
        $same('expected failure', $exception->getMessage());
    }
    $same($cwd, getcwd());
    $same($includePath, get_include_path());
    $same($bufferLevel, ob_get_level());
};

$failures = [];
try {
    foreach ($cases as $name => $case) {
        try {
            $case();
        } catch (Throwable $exception) {
            $failures[] = $name.': '.$exception->getMessage();
        }
    }
} finally {
    foreach ($files as $file) {
        unlink($file);
    }
    rmdir($directory);
}

foreach ($failures as $failure) {
    fwrite(STDERR, $failure."\n");
}
echo \count($cases).' checks, '.\count($failures)." failures.\n";
exit([] === $failures ? 0 : 1);
