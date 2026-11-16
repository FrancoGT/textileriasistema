<?php

declare(strict_types=1);

namespace App\Controller;

use App\Http\MaintenanceInput;
use App\Http\MaintenanceInputResolver;
use App\Session\ErpSession;
use App\Session\MaintenanceFormState;
use App\Sales\OrderDocumentService;
use App\View\ErpView;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

final class OrderDocumentController
{
    private const MENU = '/Mnu0000.php?Id=1000';

    public function __construct(private readonly OrderDocumentService $documents, private readonly ErpSession $session, private readonly ErpView $views)
    {
    }

    #[Route('/ERP2020.php', name: 'app_order_documents', methods: ['GET', 'POST'])]
    public function __invoke(Request $request, #[MapRequestPayload(resolver: MaintenanceInputResolver::class)] MaintenanceInput $input): Response
    {
        $user = $this->session->user();
        if ('' === $user) {
            return new RedirectResponse('/index.php');
        }
        $form = new MaintenanceFormState($this->session, 'order_documents');
        if ($request->isMethod('POST') && !$form->isValidToken($input->value('_token'))) {
            throw new AccessDeniedHttpException('El formulario ha vencido. Recargue la página.');
        }
        if ('Salir' === $input->action) {
            return new RedirectResponse(self::MENU);
        }
        $data = ['scBehavior' => '0', 'maintenanceToken' => $form->token(), 'maintenanceMessage' => ''];
        $status = 200;
        if ('Detalle' === $input->action) {
            $order = $input->value('pcCodPed');
            try {
                $this->documents->exists($user, 'ERP2020', $order);

                return $this->views->render('ERP2020', ['scBehavior' => '1', 'scCodPed' => $order, 'scFilRet' => '/ERP2020/'.rawurlencode($order).'.rtf'] + $data);
            } catch (\DomainException $exception) {
                $data['maintenanceMessage'] = $exception->getMessage();
                $status = 422;
            }
        }
        $data['saDataPed'] = $this->documents->pendingOrders($user);
        if ([] === $data['saDataPed'] && '' === $data['maintenanceMessage']) {
            $data['maintenanceMessage'] = 'No hay pedidos.';
        }

        return $this->views->render('ERP2020', $data, $status);
    }

    #[Route('/{screen}/{order}.rtf', name: 'app_order_document', requirements: ['screen' => 'ERP2010|ERP2020', 'order' => '\w{1,6}'], methods: ['GET'])]
    public function document(string $screen, string $order): Response
    {
        $user = $this->session->user();
        if ('' === $user) {
            return new RedirectResponse('/index.php');
        }
        try {
            $rtf = $this->documents->render($user, $screen, $order);
        } catch (\DomainException $exception) {
            throw new NotFoundHttpException($exception->getMessage(), $exception);
        }

        return new Response($rtf, 200, [
            'Content-Type' => 'application/rtf',
            'Content-Disposition' => HeaderUtils::makeDisposition(HeaderUtils::DISPOSITION_ATTACHMENT, 'PEDIDO_'.$order.'.rtf'),
            'Cache-Control' => 'private, no-store',
        ]);
    }
}
