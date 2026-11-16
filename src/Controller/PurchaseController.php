<?php

declare(strict_types=1);

namespace App\Controller;

use App\Finance\PurchaseService;
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

final class PurchaseController
{
    private const MENU = '/Mnu0000.php?Id=1000';

    public function __construct(private readonly PurchaseService $purchases, private readonly ErpSession $session, private readonly ErpView $views)
    {
    }

    #[Route('/ERP5010.php', name: 'app_purchases', methods: ['GET', 'POST'])]
    public function __invoke(Request $request, #[MapRequestPayload(resolver: MaintenanceInputResolver::class)] MaintenanceInput $input): Response
    {
        $user = $this->session->user();
        if ('' === $user) {
            return new RedirectResponse('/index.php');
        }
        $form = new MaintenanceFormState($this->session, 'purchases');
        if ($request->isMethod('POST') && !$form->isValidToken($input->value('_token'))) {
            throw new AccessDeniedHttpException('El formulario ha vencido. Recargue la página.');
        }
        if ('Salir' === $input->action || 'Cancelar' === $input->action) {
            $form->keep('lines', []);

            return new RedirectResponse(self::MENU);
        }
        $options = $this->purchases->options($user);
        $lines = $this->withPostedQuantities($form->kept('lines'), $input);
        $data = ['scBehavior' => '0', 'saCodPrv' => $options['suppliers'], 'saCodIns' => $options['supplies'],
            'scCodPrv' => $input->value('pcCodPrv'), 'scCodIns' => $input->value('pcCodIns'),
            'maintenanceToken' => $form->token(), 'maintenanceMessage' => $form->takeFlash()];
        $status = 200;
        try {
            if ('Agregar' === $input->action) {
                $supply = $this->purchases->supply($user, $input->value('pcCodIns'));
                $lines[$supply[0]] ??= [...$supply, '1'];
            } elseif ('Grabar' === $input->action) {
                $quantities = array_map(static fn (array $line): string => trim((string) $line[4]), $lines);
                $code = $this->purchases->create($user, $input->value('pcCodPrv'), $quantities);
                $form->keep('lines', []);
                $supplier = array_column($options['suppliers'], 1, 0)[$input->value('pcCodPrv')] ?? '';

                return $this->views->render('ERP5010', ['scBehavior' => '2', 'scCodPrv' => $input->value('pcCodPrv'), 'scNomPrv' => $supplier,
                    'saDataID' => array_values($lines), 'maintenanceToken' => $form->token(),
                    'maintenanceMessage' => 'Factura '.$code.' registrada. Aparece en Cuentas por Pagar.']);
            } else {
                $lines = [];
            }
        } catch (\DomainException $exception) {
            $data['maintenanceMessage'] = $exception->getMessage();
            $status = 422;
        }
        $form->keep('lines', $lines);
        $data['saDataI'] = array_values($lines);

        return $this->views->render('ERP5010', $data, $status);
    }

    /**
     * Copies the quantities typed in the table into the lines kept in the session.
     *
     * @param array<array-key, mixed> $lines
     *
     * @return array<string, list<string>>
     */
    private function withPostedQuantities(array $lines, MaintenanceInput $input): array
    {
        foreach ($input->rows('paDataI') as $row) {
            $code = $row[0] ?? '';
            if (isset($lines[$code], $row[4])) {
                $lines[$code][4] = $row[4];
            }
        }

        return $lines;
    }
}
