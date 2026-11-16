<?php

declare(strict_types=1);

namespace App\Controller;

use App\Http\MaintenanceInput;
use App\Http\MaintenanceInputResolver;
use App\Session\ErpSession;
use App\Session\MaintenanceFormState;
use App\Production\WorkService;
use App\View\ErpView;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Attribute\Route;

final class WorkController
{
    private const MENU = '/Mnu0000.php?Id=1000';

    public function __construct(private readonly WorkService $work, private readonly ErpSession $session, private readonly ErpView $views)
    {
    }

    #[Route('/ERP8000.php', name: 'app_work', methods: ['GET', 'POST'])]
    public function __invoke(Request $request, #[MapRequestPayload(resolver: MaintenanceInputResolver::class)] MaintenanceInput $input): Response
    {
        $user = $this->session->user();
        if ('' === $user) {
            return new RedirectResponse('/index.php');
        }
        $form = new MaintenanceFormState($this->session, 'work');
        if ($request->isMethod('POST') && !$form->isValidToken($input->value('_token'))) {
            throw new AccessDeniedHttpException('El formulario ha vencido. Recargue la página.');
        }
        if ('Salir' === $input->action) {
            return new RedirectResponse(self::MENU);
        }
        $data = ['scBehavior' => '0', 'maintenanceToken' => $form->token(), 'maintenanceMessage' => $form->takeFlash()];
        $status = 200;
        try {
            if ('Hecho' === $input->action) {
                $this->work->finish($user);
                $form->flash('Grabación conforme.');

                return new RedirectResponse('/ERP8000.php', 303);
            }
            $job = $this->work->job($user);
            $data += ['scNombre' => $job['name'], 'scCodPed' => $job['order'], 'scCodProd' => $job['product'],
                'scDescri' => $job['description'], 'snCant' => $job['quantity'], 'scProc' => $job['process'],
                'scDescriM' => $job['machineDescription'], 'scTipo' => $job['machineType']];
        } catch (\DomainException $exception) {
            $data['maintenanceMessage'] = trim($data['maintenanceMessage'].' '.$exception->getMessage());
            // Without a job the screen shows only the message and the Salir button.
            $data['scBehavior'] = 'none';
            $status = 'Hecho' === $input->action ? 422 : 200;
        }

        return $this->views->render('ERP8000', $data, $status);
    }
}
