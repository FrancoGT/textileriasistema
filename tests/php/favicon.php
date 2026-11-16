<?php

declare(strict_types=1);

require dirname(__DIR__, 2).'/vendor/autoload.php';

(new Symfony\Component\Dotenv\Dotenv())->bootEnv(dirname(__DIR__, 2).'/.env');
$kernel = new App\Kernel('test', true);
$kernel->boot();
$twig = $kernel->getContainer()->get('test.service_container')->get('twig');
$kernel->getContainer()->get('request_stack')->push(Symfony\Component\HttpFoundation\Request::create('/index.php'));
$templates = array_merge(['base.html.twig', 'security/login.html.twig'], array_map(
    static fn (string $file): string => 'erp/'.basename($file),
    glob(dirname(__DIR__, 2).'/templates/erp/*.html.twig'),
));
foreach ($templates as $template) {
    $html = $twig->render($template, ['screen' => 'ERP3000', 'title' => 'Planning', 'scCodigo' => 'test', 'scNombre' => 'Test user']);
    $document = Dom\HTMLDocument::createFromString($html, LIBXML_NOERROR);
    $icons = $document->querySelectorAll('head link[rel="icon"]');
    if (1 !== count($icons) || '/images/favicon.ico' !== $icons[0]->getAttribute('href')) {
        throw new RuntimeException('Missing or duplicated favicon in '.$template);
    }
}
if (!is_file(dirname(__DIR__, 2).'/public/images/favicon.ico')) {
    throw new RuntimeException('The favicon file is missing.');
}
$kernel->shutdown();
echo count($templates)." favicon template checks passed.\n";
