<?php

declare(strict_types=1);

namespace App\Controller;

use App\Http\MaintenanceInput;
use App\Http\MaintenanceInputResolver;
use App\Session\ErpSession;
use App\View\ErpView;
use App\Maintenance\SupplierData;
use App\Maintenance\SupplierService;
use App\Repository\CatalogRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Attribute\Route;

final class SupplierController
{
    public function __construct(
        private readonly SupplierService $suppliers,
        private readonly CatalogRepository $catalogs,
        private readonly ErpSession $session,
        private readonly ErpView $views,
    ) {
    }

    #[Route('/ERP1060.php', name: 'app_suppliers', methods: ['GET', 'POST'])]
    public function __invoke(Request $request, #[MapRequestPayload(resolver: MaintenanceInputResolver::class)] MaintenanceInput $input): Response
    {
        $user = $this->session->user();
        if ('' === $user) {
            return new RedirectResponse('/index.php');
        }
        $rows = $this->suppliers->all($user);
        if ($request->isMethod('POST') && !$this->session->verifyToken('suppliers', $input->value('_token'))) {
            throw new AccessDeniedHttpException('El formulario ha vencido. Recargue la página.');
        }
        if ('Salir' === $input->action) {
            return new RedirectResponse('/Mnu0000.php?Id=0000');
        }
        $data = ['scBehavior' => '0', 'saData' => $rows, 'saTipos' => $this->catalogs->choices('011'),
            'saTipPer' => $this->catalogs->choices('098'), 'saArsTip' => [], 'flag' => 'nuevo',
            'maintenanceToken' => $this->session->token('suppliers'),
            'scCodPrv' => '', 'scNombre' => '', 'scDirecc' => '', 'scNroDni' => '', 'scNroRuc' => '',
            'scTipPer' => '', 'sdFecNac' => '', 'scTexCon' => '',
            'maintenanceMessage' => $this->session->get('_maintenance_supplier_message') ?? ''];
        $this->session->set('_maintenance_supplier_message', null);
        $status = 200;
        try {
            if ('Nuevo' === $input->action) {
                $this->session->set('_maintenance_supplier_edit', ['code' => null]);
                $data['scBehavior'] = '1';
            } elseif ('Editar' === $input->action) {
                $this->session->set('_maintenance_supplier_edit', null);
                $code = $input->value('pcCodPrv');
                $row = $this->suppliers->find($user, $code);
                $this->session->set('_maintenance_supplier_edit', ['code' => $code]);
                $data = array_replace($data, ['scBehavior' => '1', 'flag' => 'editar', 'scCodPrv' => $code,
                    'scNombre' => $row['name'], 'scDirecc' => $row['address'], 'scNroDni' => $row['dni'],
                    'scNroRuc' => $row['ruc'], 'scTipPer' => $row['person_type'], 'sdFecNac' => $row['date'], 'scTexCon' => $row['notes'],
                    'saArsTip' => array_map(static fn (string $line): array => [$line], $row['production_lines'])]);
            } elseif ('Grabar' === $input->action) {
                $edit = $this->session->get('_maintenance_supplier_edit');
                if (!\is_array($edit) || !array_key_exists('code', $edit)) {
                    throw new \DomainException('Seleccione un proveedor o pulse Nuevo antes de grabar.');
                }
                if (($edit['code'] ?? '') !== $input->value('pcCodPrv')) {
                    throw new \DomainException('El registro seleccionado cambió. Vuelva a abrir el formulario.');
                }
                $data = array_replace($data, ['scBehavior' => '1', 'flag' => null === $edit['code'] ? 'nuevo' : 'editar',
                    'scCodPrv' => $edit['code'] ?? '', 'scNombre' => $input->value('pcNombre'), 'scDirecc' => $input->value('pcDirecc'),
                    'scNroDni' => $input->value('pcNroDni'), 'scNroRuc' => $input->value('pcNroRuc'), 'scTipPer' => $input->value('pcTipPer'),
                    'sdFecNac' => $input->value('pdFecNac'), 'scTexCon' => $input->value('pcTexCon'),
                    'saArsTip' => array_map(static fn (string $line): array => [$line], $input->productionLines)]);
                if (null !== $edit['code']) {
                    $row = $this->suppliers->find($user, $edit['code']);
                    $data = array_replace($data, ['scNombre' => $row['name'], 'scNroDni' => $row['dni'], 'scNroRuc' => $row['ruc'], 'scTipPer' => $row['person_type'], 'sdFecNac' => $row['date']]);
                }
                $this->suppliers->save($user, $edit['code'], new SupplierData(
                    $data['scNombre'], $data['scDirecc'], $data['scNroDni'], $data['scNroRuc'], $data['scTipPer'],
                    $data['sdFecNac'], $data['scTexCon'], $input->productionLines,
                ));
                $this->session->set('_maintenance_supplier_edit', null);
                $this->session->set('_maintenance_supplier_message', 'Proveedor grabado.');

                return new RedirectResponse('/ERP1060.php', 303);
            } else {
                $this->session->set('_maintenance_supplier_edit', null);
            }
        } catch (\DomainException $exception) {
            $data['maintenanceMessage'] = $exception->getMessage();
            $status = 422;
        }

        return $this->views->render('ERP1060', $data, $status);
    }
}
