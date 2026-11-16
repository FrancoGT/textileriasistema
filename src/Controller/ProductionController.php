<?php

declare(strict_types=1);

namespace App\Controller;

use App\Http\MaintenanceInput;
use App\Http\MaintenanceInputResolver;
use App\Session\ErpSession;
use App\Session\MaintenanceFormState;
use App\Production\Boards;
use App\Production\OrderBoard;
use App\Production\ProductionService;
use App\View\ErpView;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Attribute\Route;

final class ProductionController
{
    private const MENU = '/Mnu0000.php?Id=1000';

    public function __construct(
        private readonly ProductionService $production,
        private readonly Boards $boards,
        private readonly ErpSession $session,
        private readonly ErpView $views,
    ) {
    }

    #[Route('/{screen}.php', name: 'app_production', requirements: ['screen' => 'ERP4000|ERP4060|ERP4070'], methods: ['GET', 'POST'])]
    public function orders(Request $request, string $screen, #[MapRequestPayload(resolver: MaintenanceInputResolver::class)] MaintenanceInput $input): Response
    {
        $user = $this->session->user();
        if ('' === $user) {
            return new RedirectResponse('/index.php');
        }
        $board = $this->boards->production($screen);
        $form = new MaintenanceFormState($this->session, 'production_'.$screen);
        if ($request->isMethod('POST') && !$form->isValidToken($input->value('_token'))) {
            throw new AccessDeniedHttpException('El formulario ha vencido. Recargue la página.');
        }
        if ('Salir' === $input->action) {
            return new RedirectResponse(self::MENU);
        }
        $data = ['screen' => $screen, 'title' => $board->title, 'canOpen' => $board->canOpen,
            'maintenanceToken' => $form->token(), 'maintenanceMessage' => $form->takeFlash()];
        $order = $form->recall('order') ?? '';
        $product = $form->recall('product') ?? '';
        $status = 200;
        try {
            if ('Detalle' === $input->action) {
                $order = $input->value('pcCodPed');
                $data += $this->detail($user, $board, $order);
                $form->remember('order', $order);
            } elseif ('Boton1' === $input->button && 'Asignar' === $input->action) {
                $product = $input->value('pcCodProd');
                $data += $this->assignment($user, $board, $order, $product);
                $form->remember('product', $product);
            } elseif ('Boton2' === $input->button && 'Grabar' === $input->action) {
                $this->production->assign($user, $board, $order, $product, $input->value('pcCodMaq'), $input->value('pcCodPer'));
                $form->flash('Grabación conforme.');

                return new RedirectResponse('/'.$screen.'.php', 303);
            } elseif ('Boton2' === $input->button) {
                $data += $this->detail($user, $board, $order);
            } else {
                $data += $this->list($user, $board);
            }
        } catch (\DomainException $exception) {
            $data['maintenanceMessage'] = $exception->getMessage();
            $status = 422;
            $data += $this->fallback($user, $board, $input, $order, $product);
        }
        if ('0' === $data['scBehavior'] && [] === $data['saDataPed'] && '' === $data['maintenanceMessage']) {
            $data['maintenanceMessage'] = 'No hay pedidos para producción.';
        }

        return $this->views->render('ERP4000', $data, $status);
    }

    #[Route('/{screen}.php', name: 'app_production_stage', requirements: ['screen' => 'ERP4010|ERP4020|ERP4030|ERP4040|ERP4050'], methods: ['GET', 'POST'])]
    public function stage(Request $request, string $screen, #[MapRequestPayload(resolver: MaintenanceInputResolver::class)] MaintenanceInput $input): Response
    {
        $user = $this->session->user();
        if ('' === $user) {
            return new RedirectResponse('/index.php');
        }
        $board = $this->boards->stage($screen);
        $form = new MaintenanceFormState($this->session, 'production_'.$screen);
        if ($request->isMethod('POST') && !$form->isValidToken($input->value('_token'))) {
            throw new AccessDeniedHttpException('El formulario ha vencido. Recargue la página.');
        }
        // The only button of the report is Cancelar, which goes back to the menu.
        if ('' !== $input->action) {
            return new RedirectResponse(self::MENU);
        }
        $lines = $this->production->stageLines($user, $board);

        return $this->views->render('ERP4010', ['screen' => $screen, 'title' => $board->title, 'scBehavior' => '0',
            'saDataProd' => $lines, 'maintenanceToken' => $form->token(), 'maintenanceMessage' => [] === $lines ? $board->emptyMessage : '']);
    }

    /** @return array<string, mixed> */
    private function list(string $user, OrderBoard $board): array
    {
        return ['scBehavior' => '0', 'saDataPed' => $this->production->orders($user, $board)];
    }

    /** @return array<string, mixed> */
    private function detail(string $user, OrderBoard $board, string $order): array
    {
        return ['scBehavior' => '1', 'saDataProd' => $this->production->lines($user, $board, $order)];
    }

    /** @return array<string, mixed> */
    private function assignment(string $user, OrderBoard $board, string $order, string $product): array
    {
        $line = $this->production->assignment($user, $board, $order, $product);

        return ['scBehavior' => '2', 'scCodPed' => $order, 'scCodProd' => $product, 'scDescri' => $line['description'],
            'snCant' => $line['quantity'], 'scProc' => $line['process'], 'saCodMaq' => $line['machines'], 'saCodPer' => $line['staff']];
    }

    /** @return array<string, mixed> the screen before the step that failed */
    private function fallback(string $user, OrderBoard $board, MaintenanceInput $input, string $order, string $product): array
    {
        try {
            if ('Boton2' === $input->button) {
                return $this->assignment($user, $board, $order, $product);
            }
            if ('Boton1' === $input->button) {
                return $this->detail($user, $board, $order);
            }
        } catch (\DomainException) {
        }

        return $this->list($user, $board);
    }
}
