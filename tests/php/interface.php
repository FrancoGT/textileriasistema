<?php

declare(strict_types=1);

use App\EventListener\InterfaceStylesListener;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

require dirname(__DIR__, 2).'/vendor/autoload.php';

$kernel = new App\Kernel('test', true);
$listener = new InterfaceStylesListener();
$checks = 0;
$check = static function (bool $condition) use (&$checks): void {
    ++$checks;
    if (!$condition) {
        throw new RuntimeException('Interface check '.$checks.' failed.');
    }
};
$render = static function (Response $response, int $type = HttpKernelInterface::MAIN_REQUEST) use ($kernel, $listener): string {
    $listener(new ResponseEvent($kernel, Request::create('/ERP1050.php'), $type, $response));

    return $response->getContent();
};
$html = '<html><head><title>ERP-X</title></head><body>Insumos</body></html>';
$headers = ['Content-Type' => 'text/html; charset=UTF-8'];
$styled = $render(new Response($html, 200, $headers));
$check(str_contains($styled, '/css/ui-feedback.css'));
$check(str_contains($styled, '/js/ui-feedback.js'));
$check(str_contains($styled, 'name="viewport"'));
$existing = str_replace('</head>', '<meta name="viewport" content="width=device-width"><script src="/js/ui-feedback.js" defer></script></head>', $html);
$styled = $render(new Response($existing, 200, $headers));
$check(1 === substr_count($styled, '/js/ui-feedback.js'));
$check(1 === substr_count($styled, 'name="viewport"'));
$check($html === $render(new Response($html, 200, ['Content-Type' => 'application/pdf'])));
$check($html === $render(new Response($html, 200, $headers + ['Content-Disposition' => 'attachment; filename=report.html'])));
$check($html === $render(new Response($html, 200, $headers), HttpKernelInterface::SUB_REQUEST));
$check('<div>Dialog</div>' === $render(new Response('<div>Dialog</div>', 200, $headers)));
echo $checks." interface checks passed.\n";
