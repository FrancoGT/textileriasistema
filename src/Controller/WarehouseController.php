<?php

declare(strict_types=1);

namespace App\Controller;

use App\Http\MaintenanceInput;
use App\Http\MaintenanceInputResolver;
use App\Legacy\LegacySession;
use App\Legacy\LegacyTemplateRenderer;
use App\Legacy\MaintenanceFormState;
use App\Maintenance\WarehouseData;
use App\Maintenance\WarehouseService;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Attribute\Route;

final class WarehouseController
{
    private const MENU = '/Mnu0000.php?Id=1000';

    public function __construct(
        private readonly WarehouseService $warehouses,
        private readonly LegacySession $session,
        private readonly LegacyTemplateRenderer $views,
    ) {
    }

    #[Route('/ERP1090.php', name: 'app_warehouses', methods: ['GET', 'POST'])]
    public function __invoke(Request $request, #[MapRequestPayload(resolver: MaintenanceInputResolver::class)] MaintenanceInput $input): Response
    {
        $user = $this->session->user();
        if ('' === $user) {
            return new RedirectResponse('/index.php');
        }
        $form = new MaintenanceFormState($this->session, 'warehouses');
        $options = $this->warehouses->options($user);
        if ($request->isMethod('POST') && !$form->isValidToken($input->value('_token'))) {
            throw new AccessDeniedHttpException('El formulario ha vencido. Recargue la página.');
        }
        if ('Salir' === $input->action) {
            return new RedirectResponse(self::MENU);
        }
        $data = ['scBehavior' => '0', 'saCodAlm' => $this->warehouses->choices($user),
            'saEstado' => $options['statuses'], 'saTipo' => $options['types'],
            'scCodAlm' => '', 'scNomAlm' => '', 'scDirec' => '', 'scEstado' => '', 'scTipo' => '',
            'maintenanceToken' => $form->token(), 'maintenanceMessage' => $form->takeFlash()];
        $status = 200;
        try {
            if ('Nuevo' === $input->action) {
                $form->open(null);
                $data['scBehavior'] = '2';
            } elseif ('Aplicar' === $input->action) {
                $form->close();
                $row = $this->warehouses->find($user, $input->value('pcCodAlm'));
                $form->open($row['code']);
                $data = array_replace($data, ['scBehavior' => '1', 'scCodAlm' => $row['code'], 'scNomAlm' => $row['name'],
                    'scDirec' => $row['address'], 'scEstado' => $row['status'], 'scTipo' => $row['type']]);
            } elseif ('Grabar' === $input->action) {
                $code = $form->openedCode($input->value('pcCodAlm'));
                $data = array_replace($data, ['scBehavior' => null === $code ? '2' : '1', 'scCodAlm' => $code ?? '',
                    'scNomAlm' => $input->value('pcNomAlm'), 'scDirec' => $input->value('pcDirec'),
                    'scEstado' => $input->value('pcEstado'), 'scTipo' => $input->value('pcTipo')]);
                $this->warehouses->save($user, $code, new WarehouseData($data['scNomAlm'], $data['scDirec'], $data['scEstado'], $data['scTipo']));
                $form->close();
                $form->flash('Grabación conforme.');

                return new RedirectResponse('/ERP1090.php', 303);
            } else {
                $form->close();
            }
        } catch (\DomainException $exception) {
            $data['maintenanceMessage'] = $exception->getMessage();
            $status = 422;
        }

        return $this->views->render('Plantillas/ERP1090.tpl', $data, $status);
    }
}
