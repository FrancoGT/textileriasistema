<?php

declare(strict_types=1);

namespace App\Controller;

use App\Finance\TaxBreakdown;
use App\Http\MaintenanceInput;
use App\Http\MaintenanceInputResolver;
use App\Session\ErpSession;
use App\Session\MaintenanceFormState;
use App\Sales\SalesOrderService;
use App\View\ErpView;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Attribute\Route;

final class SalesOrderController
{
    private const MENU = '/Mnu0000.php?Id=1000';
    private const EMPTY_DRAFT = ['natural' => true, 'client' => '', 'priority' => '', 'date' => '', 'name' => '', 'address' => '', 'phone' => '', 'lines' => []];

    public function __construct(private readonly SalesOrderService $orders, private readonly ErpSession $session, private readonly ErpView $views)
    {
    }

    #[Route('/ERP2010.php', name: 'app_sales_order', methods: ['GET', 'POST'])]
    public function __invoke(Request $request, #[MapRequestPayload(resolver: MaintenanceInputResolver::class)] MaintenanceInput $input): Response
    {
        $user = $this->session->user();
        if ('' === $user) {
            return new RedirectResponse('/index.php');
        }
        $form = new MaintenanceFormState($this->session, 'sales_order');
        if ($request->isMethod('POST') && !$form->isValidToken($input->value('_token'))) {
            throw new AccessDeniedHttpException('El formulario ha vencido. Recargue la página.');
        }
        $redirects = ['Salir' => self::MENU, 'Nuevo Cliente' => '/ERP1030.php', 'Nuevo' => '/ERP1070.php'];
        if (isset($redirects[$input->action])) {
            return new RedirectResponse($redirects[$input->action]);
        }
        $data = ['scBehavior' => '0', 'saTipo' => $this->orders->personTypes($user), 'maintenanceToken' => $form->token(), 'maintenanceMessage' => $form->takeFlash()];
        $draft = $form->kept('draft') + self::EMPTY_DRAFT;
        $status = 200;
        $totals = null;
        try {
            if ('Aceptar' === $input->action) {
                if (!\in_array($input->value('pcTipo'), array_column($data['saTipo'], 0), true)) {
                    throw new \DomainException('Seleccione un tipo de persona.');
                }
                $draft = ['natural' => 'A' === $input->value('pcTipo')] + self::EMPTY_DRAFT;
                $data['scBehavior'] = '1';
            } elseif (\in_array($input->action, ['Cargar Datos', 'Agregar', 'Calcular', 'Grabar'], true)) {
                $data['scBehavior'] = '1';
                $draft = $this->withPostedValues($draft, $input);
                if ('Cargar Datos' === $input->action) {
                    $client = $this->orders->client($user, $draft['natural'], $draft['client']);
                    $draft = array_replace($draft, $client);
                } elseif ('Agregar' === $input->action) {
                    $product = $this->orders->product($user, $input->value('pcCodProd'));
                    $draft['lines'][$product[0]] ??= '1';
                } elseif ('Calcular' === $input->action) {
                    $totals = $this->orders->quote($user, $draft['lines'])['totals'];
                } else {
                    $code = $this->orders->create($user, $draft['natural'], $draft['client'], $draft['priority'], $draft['date'], $draft['lines']);
                    $form->keep('draft', []);

                    return $this->views->render('ERP2010', ['scBehavior' => '2', 'scFilRet' => '/ERP2010/'.$code.'.rtf',
                        'maintenanceToken' => $form->token(), 'maintenanceMessage' => 'Pedido '.$code.' registrado.']);
                }
            } else {
                $draft = self::EMPTY_DRAFT;
            }
        } catch (\DomainException $exception) {
            $data['maintenanceMessage'] = $exception->getMessage();
            $status = 422;
        }
        $form->keep('draft', $draft);
        if ('1' === $data['scBehavior']) {
            $data += $this->entryView($user, $draft, $totals);
        }

        return $this->views->render('ERP2010', $data, $status);
    }

    /**
     * @param array<string, mixed> $draft
     *
     * @return array<string, mixed>
     */
    private function withPostedValues(array $draft, MaintenanceInput $input): array
    {
        $client = $input->value($draft['natural'] ? 'pcNroDNI' : 'pcNroRUC');
        if ($client !== $draft['client']) {
            $draft = array_replace($draft, ['client' => $client, 'name' => '', 'address' => '', 'phone' => '']);
        }
        $draft['priority'] = $input->value('pcPriori');
        $draft['date'] = trim($input->value('pdFecLis'));
        foreach ($input->rows('paDataP') as $row) {
            if (isset($row[0], $row[3], $draft['lines'][$row[0]])) {
                $draft['lines'][$row[0]] = trim($row[3]);
            }
        }

        return $draft;
    }

    /**
     * @param array<string, mixed> $draft
     *
     * @return array<string, mixed>
     */
    private function entryView(string $user, array $draft, ?TaxBreakdown $totals): array
    {
        $options = $this->orders->options($user, $draft['natural']);
        $lines = [];
        foreach ($draft['lines'] as $code => $quantity) {
            try {
                [, $description, $price] = $this->orders->product($user, (string) $code);
            } catch (\DomainException) {
                [$description, $price] = ['', 0.0];
            }
            $lines[] = [(string) $code, $description, $price, $quantity, ctype_digit($quantity) ? round($price * (int) $quantity, 2) : ''];
        }

        return ['scTipo' => $draft['natural'] ? 'A' : 'B', 'saNroDNI' => $options['clients'], 'saNroRUC' => $options['clients'],
            'scNroDNI' => $draft['client'], 'scNroRUC' => $draft['client'], 'scNombre' => $draft['name'], 'scDirec' => $draft['address'],
            'scTelef' => $draft['phone'], 'saPriori' => $options['priorities'], 'scPriori' => $draft['priority'], 'sdFecLis' => $draft['date'],
            'saCodProd' => $options['products'], 'saDataP' => $lines,
            'scVV' => $totals?->saleValue ?? '', 'scIGV' => $totals?->tax ?? '', 'scTotal' => $totals?->total ?? ''];
    }
}
