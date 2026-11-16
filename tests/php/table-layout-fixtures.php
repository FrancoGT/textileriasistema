<?php

declare(strict_types=1);

require dirname(__DIR__, 2).'/vendor/autoload.php';

$twig = new Twig\Environment(new Twig\Loader\FilesystemLoader(dirname(__DIR__, 2).'/templates'));
$twig->addExtension(new Symfony\Bridge\Twig\Extension\AssetExtension(new Symfony\Component\Asset\Packages(
    new Symfony\Component\Asset\PathPackage('/', new Symfony\Component\Asset\VersionStrategy\EmptyVersionStrategy()),
)));
$fixtures = [];
foreach (glob(dirname(__DIR__, 2).'/templates/erp/ERP*.html.twig') as $file) {
    $source = file_get_contents($file);
    preg_match_all('/for i in (\w+)/', $source, $matches);
    foreach (['0', '1', '2'] as $state) {
        $data = ['scBehavior' => $state, 'scNombre' => 'Proveedor de prueba', 'scNomPrv' => 'Proveedor de prueba', 'scCodPrv' => '0001'];
        foreach (array_unique($matches[1]) as $list) {
            $data[$list] = array_map(static fn (int $index): array => [sprintf('%04d', $index), 'Algodón para tejido '.$index, 'Kilogramo', '12.50', '20', '250.00', 'Activo', '', '', ''], range(1, 25));
        }
        $name = basename($file, '.html.twig');
        $html = $twig->render('erp/'.$name.'.html.twig', $data);
        $html = str_replace('</head>', '<script src="/js/ui-feedback.js" defer></script></head>', $html);
        $html = str_replace('</body>', '<script>window.layoutFormNames = [...document.querySelectorAll("form [name]")].map(field => field.name).sort();</script></body>', $html);
        $fixtures[$name.'-'.$state] = $html;
    }
}
echo json_encode($fixtures, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
