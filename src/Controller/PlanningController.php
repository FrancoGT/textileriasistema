<?php

declare(strict_types=1);

namespace App\Controller;

use App\Http\MaintenanceInput;
use App\Http\MaintenanceInputResolver;
use App\Session\ErpSession;
use App\Session\MaintenanceFormState;
use App\Production\Boards;
use App\Production\OrderBoard;
use App\Production\PlanningService;
use App\View\ErpView;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Attribute\Route;

final class PlanningController
{
    private const MENU = '/Mnu0000.php?Id=1000';

    public function __construct(
        private readonly PlanningService $planning,
        private readonly Boards $boards,
        private readonly ErpSession $session,
        private readonly ErpView $views,
    ) {
    }

    #[Route('/{screen}.php', name: 'app_planning', requirements: ['screen' => 'ERP3000|ERP3010|ERP3020'], methods: ['GET', 'POST'])]
    public function __invoke(Request $request, string $screen, #[MapRequestPayload(resolver: MaintenanceInputResolver::class)] MaintenanceInput $input): Response
    {
        $user = $this->session->user();
        if ('' === $user) {
            return new RedirectResponse('/index.php');
        }
        $board = $this->boards->planning($screen);
        $form = new MaintenanceFormState($this->session, 'planning_'.$screen);
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
                $data += $this->assignment($user, $board, $order, $product, [], []);
                $form->remember('product', $product);
                $form->keep('machines', []);
                $form->keep('staff', []);
            } elseif ('BotonM' === $input->button) {
                $machines = $form->kept('machines');
                $machine = $this->planning->machine($user, $board, $input->value('pcCodMaq'));
                $machines[$machine[0]] = $machine;
                $form->keep('machines', $machines);
                $data += $this->assignment($user, $board, $order, $product, $machines, $form->kept('staff'));
            } elseif ('BotonP' === $input->button) {
                $staff = $form->kept('staff');
                $person = $this->planning->person($user, $board, $input->value('pcCodPer'));
                $staff[$person[0]] = $person;
                $form->keep('staff', $staff);
                $data += $this->assignment($user, $board, $order, $product, $form->kept('machines'), $staff);
            } elseif ('Boton2' === $input->button && 'Grabar' === $input->action) {
                $this->planning->plan($user, $board, $order, $product, array_map('strval', array_keys($form->kept('machines'))),
                    array_map('strval', array_keys($form->kept('staff'))));
                $form->keep('machines', []);
                $form->keep('staff', []);
                $form->flash('Grabación conforme.');

                return new RedirectResponse('/'.$screen.'.php', 303);
            } else {
                $data += $this->list($user, $board);
            }
        } catch (\DomainException $exception) {
            $data['maintenanceMessage'] = $exception->getMessage();
            $status = 422;
            $data += $this->fallback($user, $board, $input, $order, $product, $form);
        }
        if ('0' === $data['scBehavior'] && [] === $data['saDataPed'] && '' === $data['maintenanceMessage']) {
            $data['maintenanceMessage'] = 'No hay pedidos.';
        }

        return $this->views->render('ERP3000', $data, $status);
    }

    /** @return array<string, mixed> */
    private function list(string $user, OrderBoard $board): array
    {
        return ['scBehavior' => '0', 'saDataPed' => $this->planning->orders($user, $board)];
    }

    /** @return array<string, mixed> */
    private function detail(string $user, OrderBoard $board, string $order): array
    {
        return ['scBehavior' => '1', 'saDataProd' => $this->planning->lines($user, $board, $order)];
    }

    /**
     * @param array<array-key, mixed> $machines
     * @param array<array-key, mixed> $staff
     *
     * @return array<string, mixed>
     */
    private function assignment(string $user, OrderBoard $board, string $order, string $product, array $machines, array $staff): array
    {
        $line = $this->planning->start($user, $board, $order, $product);

        return ['scBehavior' => '2', 'scCodPed' => $order, 'scCodProd' => $product, 'scDescri' => $line['description'],
            'snCant' => $line['quantity'], 'saCodMaq' => $line['machines'], 'saCodPer' => $line['staff'],
            'saDataM' => array_values($machines), 'saDataP' => array_values($staff)];
    }

    /** @return array<string, mixed> the screen before the step that failed */
    private function fallback(string $user, OrderBoard $board, MaintenanceInput $input, string $order, string $product, MaintenanceFormState $form): array
    {
        try {
            if (\in_array($input->button, ['BotonM', 'BotonP', 'Boton2'], true)) {
                return $this->assignment($user, $board, $order, $product, $form->kept('machines'), $form->kept('staff'));
            }
            if ('Boton1' === $input->button) {
                return $this->detail($user, $board, $order);
            }
        } catch (\DomainException) {
        }

        return $this->list($user, $board);
    }
}
