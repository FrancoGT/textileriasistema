<?php

declare(strict_types=1);

namespace App\Controller;

use App\Http\MaintenanceInput;
use App\Http\MaintenanceInputResolver;
use App\Legacy\LegacySession;
use App\Legacy\LegacyTemplateRenderer;
use App\Maintenance\PersonnelData;
use App\Maintenance\PersonnelService;
use App\Repository\CatalogRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Attribute\Route;

final class PersonnelController
{
    public function __construct(
        private readonly PersonnelService $people,
        private readonly CatalogRepository $catalogs,
        private readonly LegacySession $session,
        private readonly LegacyTemplateRenderer $views,
    ) {
    }

    #[Route('/ERP1000.php', name: 'app_personnel', methods: ['GET', 'POST'])]
    public function __invoke(Request $request, #[MapRequestPayload(resolver: MaintenanceInputResolver::class)] MaintenanceInput $input): Response
    {
        $user = $this->session->user();
        if ('' === $user) {
            return new RedirectResponse('/index.php');
        }
        $choices = $this->people->choices($user);
        if ($request->isMethod('POST') && !$this->session->verifyToken('personnel', $input->value('_token'))) {
            throw new AccessDeniedHttpException('El formulario ha vencido. Recargue la página.');
        }
        $action = $input->action;
        if ('Salir' === $action) {
            return new RedirectResponse('/Mnu0000.php?Id=1000');
        }
        $data = ['scBehavior' => '0', 'saCodPer' => $choices,
            'saSexo' => $this->catalogs->choices('017', true), 'saEstCiv' => $this->catalogs->choices('018', true),
            'saEstado' => $this->catalogs->choices('001', true), 'maintenanceToken' => $this->session->token('personnel'),
            'scCodPer' => '', 'scNombre' => '', 'scDirecc' => '', 'scEmail' => '', 'scNroDni' => '',
            'scSexo' => '', 'scEstCiv' => '', 'scEstado' => '',
            'maintenanceMessage' => $this->session->get('_maintenance_personnel_message') ?? ''];
        $this->session->set('_maintenance_personnel_message', null);
        $status = 200;
        try {
            if ('Nuevo' === $action) {
                $this->session->set('_maintenance_personnel_edit', ['code' => null]);
                $data['scBehavior'] = '2';
            } elseif ('Aplicar' === $action || 'Aceptar' === $request->query->get('Id')) {
                $this->session->set('_maintenance_personnel_edit', null);
                $code = 'Aceptar' === $request->query->get('Id') ? (string) $this->session->get('pcCodPer') : $input->value('pcCodPer');
                $row = $this->people->find($user, $code);
                $this->session->set('_maintenance_personnel_edit', ['code' => $code]);
                $this->session->set('pcCodPer', $code);
                $data = array_replace($data, ['scBehavior' => '1', 'scCodPer' => $code,
                    'scNombre' => $row['name'], 'scDirecc' => $row['address'], 'scEmail' => $row['email'],
                    'scNroDni' => $row['dni'], 'scSexo' => $row['sex'], 'scEstCiv' => $row['civil_status'], 'scEstado' => $row['status']]);
            } elseif ('Grabar' === $action) {
                $edit = $this->session->get('_maintenance_personnel_edit');
                if (!\is_array($edit) || !array_key_exists('code', $edit)) {
                    throw new \DomainException('Seleccione un empleado o pulse Nuevo antes de grabar.');
                }
                if (($edit['code'] ?? '') !== $input->value('pcCodPer')) {
                    throw new \DomainException('El registro seleccionado cambió. Vuelva a abrir el formulario.');
                }
                $data = array_replace($data, ['scBehavior' => null === $edit['code'] ? '2' : '1',
                    'scCodPer' => $edit['code'] ?? '', 'scNombre' => $input->value('pcNombre'), 'scDirecc' => $input->value('pcDirecc'),
                    'scEmail' => $input->value('pcEmail'), 'scNroDni' => $input->value('pcNroDni'), 'scSexo' => $input->value('pcSexo'),
                    'scEstCiv' => $input->value('pcEstCiv'), 'scEstado' => $input->value('pcEstado')]);
                $this->people->save($user, $edit['code'], new PersonnelData(
                    $input->value('pcNombre'), $input->value('pcDirecc'), $input->value('pcEmail'), $input->value('pcNroDni'),
                    $input->value('pcSexo'), $input->value('pcEstCiv'), $input->value('pcEstado'),
                ));
                $this->session->set('_maintenance_personnel_edit', null);
                $this->session->set('_maintenance_personnel_message', 'Grabación conforme.');

                return new RedirectResponse('/ERP1000.php', 303);
            } else {
                $this->session->set('_maintenance_personnel_edit', null);
            }
        } catch (\DomainException $exception) {
            $data['maintenanceMessage'] = $exception->getMessage();
            $status = 422;
        }

        return $this->views->render('Plantillas/ERP1000.tpl', $data, $status);
    }
}
