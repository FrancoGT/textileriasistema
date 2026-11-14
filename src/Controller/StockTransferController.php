<?php

declare(strict_types=1);

namespace App\Controller;

use App\Http\MaintenanceInput;
use App\Http\MaintenanceInputResolver;
use App\Inventory\StockTransferService;
use App\Legacy\LegacySession;
use App\Legacy\LegacyTemplateRenderer;
use App\Legacy\MaintenanceFormState;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Attribute\Route;

final class StockTransferController
{
    private const MENU = '/Mnu0000.php?Id=1000';

    public function __construct(
        private readonly StockTransferService $transfers,
        private readonly LegacySession $session,
        private readonly LegacyTemplateRenderer $views,
    ) {
    }

    #[Route('/ERP1091.php', name: 'app_stock_transfer', methods: ['GET', 'POST'])]
    public function __invoke(Request $request, #[MapRequestPayload(resolver: MaintenanceInputResolver::class)] MaintenanceInput $input): Response
    {
        $user = $this->session->user();
        if ('' === $user) {
            return new RedirectResponse('/index.php');
        }
        $form = new MaintenanceFormState($this->session, 'stock_transfer');
        $warehouses = $this->transfers->warehouses($user);
        if ($request->isMethod('POST') && !$form->isValidToken($input->value('_token'))) {
            throw new AccessDeniedHttpException('El formulario ha vencido. Recargue la página.');
        }
        if ('Salir' === $input->action) {
            return new RedirectResponse(self::MENU);
        }
        $data = ['scBehavior' => '0', 'saDataA' => $warehouses, 'saDataID' => [], 'scCodAlm' => '', 'scNomAlm' => '',
            'saCodAlm' => [], 'saCodIns' => [], 'saDataI' => [], 'scOrigen' => '', 'scDestino' => '', 'scCodIns' => '', 'flagn' => '',
            'maintenanceToken' => $form->token(), 'maintenanceMessage' => $form->takeFlash()];
        $status = 200;
        try {
            switch ($input->action) {
                case 'Detalle':
                    $warehouse = $input->value('pcCodAlm');
                    $stock = $this->transfers->stock($user, $warehouse);
                    $data = array_replace($data, ['scBehavior' => '2', 'scCodAlm' => $warehouse, 'scNomAlm' => $stock['name'], 'saDataID' => $stock['stock']]);
                    if ([] === $stock['stock']) {
                        $data['maintenanceMessage'] = 'El almacén no tiene insumos.';
                    }
                    break;
                case 'Asignar Insumos':
                case 'Agregar':
                case 'Eliminar':
                case 'Grabar':
                    $supplies = $this->transfers->supplies($user);
                    $quantities = $this->quantities($input);
                    if ('Agregar' === $input->action && isset($supplies[$input->value('pcCodIns')])) {
                        $quantities += [$input->value('pcCodIns') => ''];
                        $data['flagn'] = '1';
                    }
                    if ('Eliminar' === $input->action) {
                        $quantities = array_diff_key($quantities, array_flip($input->list('paCheck')));
                    }
                    $data = array_replace($data, ['scBehavior' => '1', 'scOrigen' => $input->value('pcOrigen'), 'scDestino' => $input->value('pcDestino'),
                        'scCodIns' => $input->value('pcCodIns'), 'saCodIns' => array_map(static fn (array $row): array => [$row[0], $row[1]], array_values($supplies)),
                        'saCodAlm' => array_merge([[StockTransferService::SUPPLIER, 'PROVEEDOR']], array_map(static fn (array $row): array => [$row[0], $row[1]], $warehouses)),
                        'saDataI' => array_values(array_map(
                            static fn (string $code, string $quantity): array => [...$supplies[$code], $quantity],
                            array_keys(array_intersect_key($quantities, $supplies)),
                            array_intersect_key($quantities, $supplies),
                        ))]);
                    if ('Grabar' === $input->action) {
                        $this->transfers->transfer($user, $input->value('pcOrigen'), $input->value('pcDestino'), $quantities);
                        $form->flash('Grabación conforme.');

                        return new RedirectResponse('/ERP1091.php', 303);
                    }
                    break;
            }
        } catch (\DomainException $exception) {
            $data['maintenanceMessage'] = $exception->getMessage();
            $status = 422;
        }

        return $this->views->render('Plantillas/ERP1091.tpl', $data, $status);
    }

    /** @return array<string, string> supply code => typed quantity */
    private function quantities(MaintenanceInput $input): array
    {
        $quantities = [];
        foreach ($input->rows('paDataI') as $row) {
            $code = $row[0] ?? '';
            if ('' !== $code) {
                $quantities[$code] = $row[4] ?? '';
            }
        }

        return $quantities;
    }
}
