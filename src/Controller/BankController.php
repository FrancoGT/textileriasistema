<?php

declare(strict_types=1);

namespace App\Controller;

use App\Http\MaintenanceInput;
use App\Http\MaintenanceInputResolver;
use App\Session\ErpSession;
use App\View\ErpView;
use App\Session\MaintenanceFormState;
use App\Maintenance\BankData;
use App\Maintenance\BankService;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Attribute\Route;

final class BankController
{
    private const MENU = '/Mnu0000.php?Id=1000';

    public function __construct(
        private readonly BankService $banks,
        private readonly ErpSession $session,
        private readonly ErpView $views,
    ) {
    }

    #[Route('/ERP1093.php', name: 'app_banks', methods: ['GET', 'POST'])]
    public function __invoke(Request $request, #[MapRequestPayload(resolver: MaintenanceInputResolver::class)] MaintenanceInput $input): Response
    {
        $user = $this->session->user();
        if ('' === $user) {
            return new RedirectResponse('/index.php');
        }
        $form = new MaintenanceFormState($this->session, 'banks');
        $options = $this->banks->options($user);
        if ($request->isMethod('POST') && !$form->isValidToken($input->value('_token'))) {
            throw new AccessDeniedHttpException('El formulario ha vencido. Recargue la página.');
        }
        if ('Salir' === $input->action) {
            return new RedirectResponse(self::MENU);
        }
        $data = ['scBehavior' => '0', 'saCodBan' => $this->banks->choices($user),
            'saEstado' => $options['statuses'], 'saCiudad' => $options['cities'],
            'scCodBan' => '', 'scNomBan' => '', 'scEstado' => '', 'scCiudad' => '',
            'maintenanceToken' => $form->token(), 'maintenanceMessage' => $form->takeFlash()];
        $status = 200;
        try {
            if ('Nuevo' === $input->action) {
                $form->open(null);
                $data['scBehavior'] = '2';
            } elseif ('Aplicar' === $input->action) {
                $form->close();
                $row = $this->banks->find($user, $input->value('pcCodBan'));
                $form->open($row['code']);
                $data = array_replace($data, ['scBehavior' => '1', 'scCodBan' => $row['code'], 'scNomBan' => $row['name'],
                    'scEstado' => $row['status'], 'scCiudad' => $row['city']]);
            } elseif ('Grabar' === $input->action) {
                $code = $form->openedCode($input->value('pcCodBan'));
                $data = array_replace($data, ['scBehavior' => null === $code ? '2' : '1', 'scCodBan' => $code ?? '',
                    'scNomBan' => $input->value('pcNomBan'), 'scEstado' => $input->value('pcEstado'), 'scCiudad' => $input->value('pcCiudad')]);
                $this->banks->save($user, $code, new BankData($data['scNomBan'], $data['scEstado'], $data['scCiudad']));
                $form->close();
                $form->flash('Grabación conforme.');

                return new RedirectResponse('/ERP1093.php', 303);
            } else {
                $form->close();
            }
        } catch (\DomainException $exception) {
            $data['maintenanceMessage'] = $exception->getMessage();
            $status = 422;
        }

        return $this->views->render('ERP1093', $data, $status);
    }
}
