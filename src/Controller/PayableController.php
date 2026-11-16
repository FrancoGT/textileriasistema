<?php

declare(strict_types=1);

namespace App\Controller;

use App\Finance\PayableBoard;
use App\Finance\PayableService;
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

final class PayableController
{
    private const MENU = '/Mnu0000.php?Id=1000';

    public function __construct(private readonly PayableService $payables, private readonly ErpSession $session, private readonly ErpView $views)
    {
    }

    #[Route('/{screen}.php', name: 'app_payables', requirements: ['screen' => 'ERP7010|ERP7020'], methods: ['GET', 'POST'])]
    public function __invoke(Request $request, string $screen, #[MapRequestPayload(resolver: MaintenanceInputResolver::class)] MaintenanceInput $input): Response
    {
        $user = $this->session->user();
        if ('' === $user) {
            return new RedirectResponse('/index.php');
        }
        $board = PayableBoard::from($screen);
        $form = new MaintenanceFormState($this->session, 'payables_'.$screen);
        if ($request->isMethod('POST') && !$form->isValidToken($input->value('_token'))) {
            throw new AccessDeniedHttpException('El formulario ha vencido. Recargue la página.');
        }
        if ('Salir' === $input->action) {
            return new RedirectResponse(self::MENU);
        }
        $data = ['screen' => $screen, 'canPay' => $board->canPay(), 'scBehavior' => '0',
            'maintenanceToken' => $form->token(), 'maintenanceMessage' => $form->takeFlash()];
        $invoice = $form->recall('invoice') ?? '';
        $status = 200;
        try {
            if ('Detalle' === $input->action) {
                $invoice = $input->value('pcCodFac');
                $data = array_replace($data, $this->detail($user, $board, $invoice));
                $form->remember('invoice', $invoice);
            } elseif ('Boton1' === $input->button && 'Pagar' === $input->action) {
                $data = array_replace($data, $this->payment($user, $board, $invoice));
            } elseif ('Cargar' === $input->action) {
                $data = array_replace($data, $this->payment($user, $board, $invoice), [
                    'scCtaPrv' => $input->value('pcCtaPrv'), 'scCodBan' => $input->value('pcCodBan'),
                    'saCtaBan' => $this->payables->bankAccounts($user, $board, $invoice, $input->value('pcCodBan')),
                ]);
            } elseif ('Boton2' === $input->button && 'Pagar' === $input->action) {
                $data = array_replace($data, $this->payment($user, $board, $invoice), ['scCodBan' => $input->value('pcCodBan')]);
                $this->payables->pay($user, $board, $invoice, $input->value('pcCodBan'), $input->value('pcCtaBan'));
                $form->flash('Grabación conforme.');

                return new RedirectResponse('/'.$screen.'.php', 303);
            }
        } catch (\DomainException $exception) {
            $data['maintenanceMessage'] = $exception->getMessage();
            $status = 422;
        }
        if ('0' === $data['scBehavior']) {
            $data['saDataFac'] = $this->payables->invoices($user, $board);
            if ([] === $data['saDataFac'] && '' === $data['maintenanceMessage']) {
                $data['maintenanceMessage'] = 'No hay facturas.';
            }
        }

        return $this->views->render('ERP7010', $data, $status);
    }

    /** @return array<string, mixed> */
    private function detail(string $user, PayableBoard $board, string $invoice): array
    {
        $detail = $this->payables->detail($user, $board, $invoice);

        return ['scBehavior' => '1', 'scNombre' => $detail['invoice']['name'], 'scTipo' => $detail['invoice']['personType'],
            'scTipPer' => $detail['invoice']['personTypeName'], 'scNroDNI' => $detail['invoice']['dni'], 'scNroRUC' => $detail['invoice']['ruc'],
            'scDirecc' => $detail['invoice']['address'], 'sdFecLis' => $detail['invoice']['date'], 'saData' => $detail['lines'],
            'VV' => $detail['totals']->saleValue, 'IGV' => $detail['totals']->tax, 'Total' => $detail['totals']->total];
    }

    /** @return array<string, mixed> */
    private function payment(string $user, PayableBoard $board, string $invoice): array
    {
        $options = $this->payables->paymentOptions($user, $board, $invoice);

        return ['scBehavior' => '2', 'scNombre' => $options['name'], 'saCtaPrv' => $options['supplierAccounts'], 'saCodBan' => $options['banks'], 'saCtaBan' => []];
    }
}
