<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dispatch\DispatchService;
use App\Http\MaintenanceInput;
use App\Http\MaintenanceInputResolver;
use App\Session\ErpSession;
use App\Session\MaintenanceFormState;
use App\View\ErpView;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

final class DispatchController
{
    private const MENU = '/Mnu0000.php?Id=1000';

    public function __construct(private readonly DispatchService $dispatch, private readonly ErpSession $session, private readonly ErpView $views)
    {
    }

    #[Route('/ERP9010.php', name: 'app_dispatch', methods: ['GET', 'POST'])]
    public function __invoke(Request $request, #[MapRequestPayload(resolver: MaintenanceInputResolver::class)] MaintenanceInput $input): Response
    {
        $user = $this->session->user();
        if ('' === $user) {
            return new RedirectResponse('/index.php');
        }
        $form = new MaintenanceFormState($this->session, 'dispatch');
        if ($request->isMethod('POST') && !$form->isValidToken($input->value('_token'))) {
            throw new AccessDeniedHttpException('El formulario ha vencido. Recargue la página.');
        }
        if ('Salir' === $input->action) {
            return new RedirectResponse(self::MENU);
        }
        $status = 200;
        $message = $form->takeFlash();
        try {
            if ('Aplicar' === $input->action) {
                $order = $input->value('pcCodPed');
                $form->flash($this->dispatch->deliver($user, $order).' Abra el archivo de salida para ver el reporte.');
                $form->remember('report', $order);

                return new RedirectResponse('/ERP9010.php', 303);
            }
            if ('Cancelar' === $input->action) {
                $form->remember('report', null);
            }
        } catch (\DomainException $exception) {
            $message = $exception->getMessage();
            $status = 422;
        }
        $orders = $this->dispatch->orders($user);
        $report = $form->recall('report');

        return $this->views->render('ERP9010', ['scBehavior' => '0', 'saDataPed' => $orders,
            'scFilRet' => null === $report ? '' : '/ERP9010/'.rawurlencode($report).'.pdf',
            'maintenanceToken' => $form->token(),
            'maintenanceMessage' => '' === $message && [] === $orders ? 'No hay pedidos terminados.' : $message], $status);
    }

    #[Route('/ERP9010/{order}.pdf', name: 'app_dispatch_report', requirements: ['order' => '\w{1,6}'], methods: ['GET'])]
    public function report(string $order): Response
    {
        $user = $this->session->user();
        if ('' === $user) {
            return new RedirectResponse('/index.php');
        }
        try {
            $pdf = $this->dispatch->report($user, $order);
        } catch (\DomainException $exception) {
            throw new NotFoundHttpException($exception->getMessage(), $exception);
        }

        return new Response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => HeaderUtils::makeDisposition(HeaderUtils::DISPOSITION_INLINE, 'PEDIDO_'.$order.'.pdf'),
            'Cache-Control' => 'private, no-store',
        ]);
    }
}
