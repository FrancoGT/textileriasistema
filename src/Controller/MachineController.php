<?php

declare(strict_types=1);

namespace App\Controller;

use App\Http\MaintenanceInput;
use App\Http\MaintenanceInputResolver;
use App\Legacy\LegacySession;
use App\Legacy\LegacyTemplateRenderer;
use App\Legacy\MaintenanceFormState;
use App\Maintenance\MachineData;
use App\Maintenance\MachineService;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Attribute\Route;

final class MachineController
{
    private const MENU = '/Mnu0000.php?Id=1000';

    public function __construct(
        private readonly MachineService $machines,
        private readonly LegacySession $session,
        private readonly LegacyTemplateRenderer $views,
    ) {
    }

    #[Route('/ERP1040.php', name: 'app_machines', methods: ['GET', 'POST'])]
    public function __invoke(Request $request, #[MapRequestPayload(resolver: MaintenanceInputResolver::class)] MaintenanceInput $input): Response
    {
        $user = $this->session->user();
        if ('' === $user) {
            return new RedirectResponse('/index.php');
        }
        $form = new MaintenanceFormState($this->session, 'machines');
        $options = $this->machines->options($user);
        if ($request->isMethod('POST') && !$form->isValidToken($input->value('_token'))) {
            throw new AccessDeniedHttpException('El formulario ha vencido. Recargue la página.');
        }
        if ('Salir' === $input->action) {
            return new RedirectResponse(self::MENU);
        }
        $data = ['scBehavior' => '0', 'saCodMaq' => $this->machines->choices($user),
            'saEstado' => $options['statuses'], 'saTipo' => $options['types'],
            'scCodMaq' => '', 'scNomMaq' => '', 'scEstado' => '', 'scTipo' => '',
            'maintenanceToken' => $form->token(), 'maintenanceMessage' => $form->takeFlash()];
        $status = 200;
        try {
            if ('Nuevo' === $input->action) {
                $form->open(null);
                $data['scBehavior'] = '2';
            } elseif ('Aplicar' === $input->action) {
                $form->close();
                $row = $this->machines->find($user, $input->value('pcCodMaq'));
                $form->open($row['code']);
                $data = array_replace($data, ['scBehavior' => '1', 'scCodMaq' => $row['code'], 'scNomMaq' => $row['description'],
                    'scEstado' => $row['status'], 'scTipo' => $row['type']]);
            } elseif ('Grabar' === $input->action) {
                $code = $form->openedCode($input->value('pcCodMaq'));
                $data = array_replace($data, ['scBehavior' => null === $code ? '2' : '1', 'scCodMaq' => $code ?? '',
                    'scNomMaq' => $input->value('pcNomMaq'), 'scEstado' => $input->value('pcEstado'), 'scTipo' => $input->value('pcTipo')]);
                $this->machines->save($user, $code, new MachineData($data['scNomMaq'], $data['scEstado'], $data['scTipo']));
                $form->close();
                $form->flash('Grabación conforme.');

                return new RedirectResponse('/ERP1040.php', 303);
            } else {
                $form->close();
            }
        } catch (\DomainException $exception) {
            $data['maintenanceMessage'] = $exception->getMessage();
            $status = 422;
        }

        return $this->views->render('Plantillas/ERP1040.tpl', $data, $status);
    }
}
