<?php

declare(strict_types=1);

namespace App\Controller;

use App\Http\MaintenanceInput;
use App\Http\MaintenanceInputResolver;
use App\Session\ErpSession;
use App\View\ErpView;
use App\Session\MaintenanceFormState;
use App\Maintenance\CatalogEntry;
use App\Maintenance\CatalogTableService;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Attribute\Route;

final class CatalogTableController
{
    private const MENU = '/Mnu0000.php?Id=1000';

    public function __construct(
        private readonly CatalogTableService $tables,
        private readonly ErpSession $session,
        private readonly ErpView $views,
    ) {
    }

    #[Route('/ERP1080.php', name: 'app_catalog_tables', methods: ['GET', 'POST'])]
    public function __invoke(Request $request, #[MapRequestPayload(resolver: MaintenanceInputResolver::class)] MaintenanceInput $input): Response
    {
        $user = $this->session->user();
        if ('' === $user) {
            return new RedirectResponse('/index.php');
        }
        $form = new MaintenanceFormState($this->session, 'catalog_tables');
        $headers = $this->tables->headers($user);
        if ($request->isMethod('POST') && !$form->isValidToken($input->value('_token'))) {
            throw new AccessDeniedHttpException('El formulario ha vencido. Recargue la página.');
        }
        if ('Salir' === $input->action) {
            return new RedirectResponse(self::MENU);
        }
        $data = ['scBehavior' => '0', 'saCodTab' => $headers, 'scCodTab' => '', 'scDescri' => '', 'saData' => [], 'flagn' => '',
            'maintenanceToken' => $form->token(), 'maintenanceMessage' => $form->takeFlash()];
        $status = 200;
        try {
            switch ($input->action) {
                case 'Editar':
                    $form->close();
                    $table = $input->list('paCheck')[0] ?? throw new \DomainException('Seleccione una tabla.');
                    $opened = $this->tables->open($user, $table);
                    $original = array_map(static fn (CatalogEntry $entry): string => $entry->code, $opened['entries']);
                    $form->open($table);
                    $form->remember('codes', json_encode($original, JSON_THROW_ON_ERROR));
                    $form->remember('description', $opened['description']);
                    $data = $this->grid($data, $table, $opened['description'], $opened['entries'], \count($original));
                    break;
                case 'Nuevo':
                case 'Eliminar':
                case 'Grabar':
                    [$table, $original] = $this->opened($form, $input);
                    $entries = array_map(
                        static fn (array $row): CatalogEntry => new CatalogEntry($row[1] ?? '', $row[2] ?? '', $row[3] ?? ''),
                        $input->rows('paData'),
                    );
                    $data = $this->grid($data, $table, (string) $form->recall('description'), $entries, \count($original));
                    if ('Nuevo' === $input->action) {
                        $entries[] = new CatalogEntry('0', '', '');
                        $data = $this->grid($data, $table, (string) $form->recall('description'), $entries, \count($original));
                        $data['flagn'] = '1';
                        break;
                    }
                    if ('Eliminar' === $input->action) {
                        foreach ($input->list('paCheck') as $id) {
                            $index = (int) $id - 1;
                            if ($index < \count($original)) {
                                throw new \DomainException('No se puede eliminar una fila existente.');
                            }
                            unset($entries[$index]);
                        }
                        $data = $this->grid($data, $table, (string) $form->recall('description'), array_values($entries), \count($original));
                        break;
                    }
                    $this->tables->save($user, $table, $original, $entries);
                    $form->close();
                    $form->flash('Grabación conforme.');

                    return new RedirectResponse('/ERP1080.php', 303);
                default:
                    $form->close();
            }
        } catch (\DomainException $exception) {
            $data['maintenanceMessage'] = $exception->getMessage();
            $status = 422;
        }

        return $this->views->render('ERP1080', $data, $status);
    }

    /** @return array{0: string, 1: list<string>} */
    private function opened(MaintenanceFormState $form, MaintenanceInput $input): array
    {
        $table = $form->openedCode($input->value('pcCodTab'));
        $codes = json_decode((string) $form->recall('codes'), true);
        if (null === $table || !\is_array($codes)) {
            throw new \DomainException('Seleccione una tabla antes de continuar.');
        }

        return [$table, $codes];
    }

    /** @param list<CatalogEntry> $entries */
    private function grid(array $data, string $table, string $description, array $entries, int $existing): array
    {
        $rows = [];
        foreach ($entries as $index => $entry) {
            $rows[] = [$index + 1, $entry->order, $entry->code, $entry->description, $index < $existing ? 0 : 1];
        }

        return array_replace($data, ['scBehavior' => '1', 'scCodTab' => $table, 'scDescri' => $description, 'saData' => $rows]);
    }
}
