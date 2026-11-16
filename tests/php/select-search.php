<?php

declare(strict_types=1);

use App\Controller\SelectSearchController;
use App\EventListener\SelectSearchListener;
use App\Session\ErpSession;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

require dirname(__DIR__, 2).'/vendor/autoload.php';

$checks = 0;
$check = static function (bool $condition) use (&$checks): void {
    ++$checks;
    if (!$condition) {
        throw new RuntimeException('Select search check '.$checks.' failed.');
    }
};
$session = new ErpSession();
$session->login('search-test', 'Search test');
$listener = new SelectSearchListener($session);
$controller = new SelectSearchController();
$kernel = new App\Kernel('test', true);
$html = '<html><head></head><body><select name="product" onchange="changed()"><option value="01">Algodón &amp; hilo</option><option disabled value="02">Disabled</option><optgroup label="Blocked" disabled><option value="03">Blocked</option></optgroup><option value="04">Lana</option></select></body></html>';
$response = new Response($html, 200, ['Content-Type' => 'text/html']);
$listener(new ResponseEvent($kernel, Request::create('/ERP1070.php'), HttpKernelInterface::MAIN_REQUEST, $response));
$check(str_contains($response->getContent(), '/js/select-search.js'));
$check(str_contains($response->getContent(), 'onchange="changed()"'));
$check(str_contains($response->getContent(), '<option disabled value="02">'));
preg_match('/data-search-key="([a-f0-9]+)"/', $response->getContent(), $match);
$key = $match[1];
$search = static fn (string $key, string $q = ''): Response => $controller(Request::create('/api/select-search', 'GET', ['key' => $key, 'q' => $q]), $session);
$result = json_decode($search($key, 'algodon')->getContent(), true, flags: JSON_THROW_ON_ERROR);
$check($result['results'] === [['index' => 0, 'value' => '01', 'label' => 'Algodón & hilo']]);
$check(count(json_decode($search($key)->getContent(), true)['results']) === 2);
$check(json_decode($search($key, '04')->getContent(), true)['results'][0]['index'] === 3);
$check(json_decode($search($key, 'unknown')->getContent(), true)['results'] === []);
$check($search('unknown')->getStatusCode() === 410);
$lists = $session->get('_maintenance_select_lists');
$lists[$key]['options'] = array_fill(0, 60, ['index' => 0, 'value' => 'a', 'label' => 'a']);
$session->set('_maintenance_select_lists', $lists);
$result = json_decode($search($key)->getContent(), true);
$check(count($result['results']) === 50 && $result['more']);
$lists[$key]['created'] = time() - 7201;
$session->set('_maintenance_select_lists', $lists);
$check($search($key)->getStatusCode() === 410);
$session->logout();
$check($search($key)->getStatusCode() === 401);
$session->login('other-test', 'Other test');
$check($search($key)->getStatusCode() === 410);
$session->logout();
echo $checks." select search checks passed.\n";
