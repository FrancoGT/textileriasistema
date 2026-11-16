<?php

declare(strict_types=1);

namespace App\Controller;

use App\Finance\ReceivableBoard;
use App\Finance\ReceivableService;
use App\Http\MaintenanceInput;
use App\Http\MaintenanceInputResolver;
use App\Session\ErpSession;
use App\Session\MaintenanceFormState;
use App\View\ErpView;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Attribute\Route;

final class ReceivableController
{
    private const MENU = '/Mnu0000.php?Id=1000';

    public function __construct(private readonly ReceivableService $receivables, private readonly ErpSession $session, private readonly ErpView $views)
    {
    }

    #[Route('/{screen}.php', name: 'app_receivables', requirements: ['screen' => 'ERP6010|ERP6020|ERP6030'], methods: ['GET', 'POST'])]
    public function __invoke(Request $request, string $screen, #[MapRequestPayload(resolver: MaintenanceInputResolver::class)] MaintenanceInput $input): Response
    {
        $user = $this->session->user();
        if ('' === $user) {
            return new RedirectResponse('/index.php');
        }
        $board = ReceivableBoard::from($screen);
        $form = new MaintenanceFormState($this->session, 'receivables_'.$screen);
        if ($request->isMethod('POST') && !$form->isValidToken($input->value('_token'))) {
            throw new AccessDeniedHttpException('El formulario ha vencido. Recargue la página.');
        }
        if ('Salir' === $input->action) {
            return new RedirectResponse(self::MENU);
        }
        $data = ['screen' => $screen, 'scBehavior' => '0', 'maintenanceToken' => $form->token(), 'maintenanceMessage' => $form->takeFlash()];
        $status = 200;
        try {
            if ('Aplicar' === $input->action) {
                $order = $input->value('pcCodPed');
                $data = array_replace($data, ['scBehavior' => '1', 'scCodPed' => $order, 'snTotal' => $this->receivables->total($user, $board, $order)]);
                $form->remember('order', $order);
            } elseif ('Grabar' === $input->action) {
                $order = $form->recall('order') ?? '';
                $data = array_replace($data, ['scBehavior' => '1', 'scCodPed' => $order, 'snPago' => $input->value('pnPago'), 'sdFecLim' => $input->value('pcFecLim')]);
                $data['snTotal'] = $this->receivables->total($user, $board, $order);
                $this->receivables->createPlan($user, $board, $order, $input->value('pnPago'), $input->value('pcFecLim'));
                $form->flash('Grabación conforme.');

                return new RedirectResponse('/'.$screen.'.php', 303);
            }
        } catch (\DomainException $exception) {
            $data['maintenanceMessage'] = $exception->getMessage();
            $status = 422;
            if (!isset($data['snTotal'])) {
                $data['scBehavior'] = '0';
            }
        }
        if ('0' === $data['scBehavior']) {
            $data['saDataPed'] = $this->receivables->orders($user, $board);
            if ([] === $data['saDataPed'] && '' === $data['maintenanceMessage']) {
                $data['maintenanceMessage'] = 'No hay facturas pendientes.';
            }
        }

        return $this->views->render($board->template(), $data, $status);
    }
}
