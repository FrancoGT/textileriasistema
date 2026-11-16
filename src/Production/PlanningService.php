<?php

declare(strict_types=1);

namespace App\Production;

use App\Repository\OrderRepository;
use App\Repository\PlanningRepository;
use App\Security\ModulePermission;

/** Reserves machines and staff for the lines of new orders (ERP3000 to ERP3020). */
final class PlanningService
{
    public function __construct(
        private readonly OrderRepository $orders,
        private readonly PlanningRepository $planning,
        private readonly ModulePermission $permissions,
    ) {
    }

    /** @return list<list<string>> */
    public function orders(string $user, OrderBoard $board): array
    {
        $this->permissions->assertGranted($user, $board->permission);

        return $this->orders->orders($board->filter);
    }

    /** @return list<list<string>> */
    public function lines(string $user, OrderBoard $board, string $order): array
    {
        $this->assertCanOpen($user, $board);
        if ('' === $order) {
            throw new \DomainException('Seleccione un pedido.');
        }
        $lines = $this->orders->planningLines($order);
        if ([] === $lines) {
            throw new \DomainException('El pedido no tiene productos.');
        }

        return $lines;
    }

    /**
     * @return array{description: string, quantity: string, machines: list<array{0: string, 1: string}>, staff: list<array{0: string, 1: string}>}
     */
    public function start(string $user, OrderBoard $board, string $order, string $product): array
    {
        $this->assertCanOpen($user, $board);
        if ('' === $product) {
            throw new \DomainException('Seleccione un producto.');
        }
        $line = $this->orders->line($order, $product) ?? throw new \DomainException('El producto no pertenece al pedido.');
        if ('I' !== $line['planning']) {
            throw new \DomainException('El producto ya fue planificado.');
        }
        $machines = $this->planning->freeMachines();
        if ([] === $machines) {
            throw new \DomainException('No hay máquinas libres.');
        }
        $staff = $this->planning->freeStaff();
        if ([] === $staff) {
            throw new \DomainException('No hay personal libre.');
        }

        return ['description' => $line['description'], 'quantity' => $line['quantity'], 'machines' => $machines, 'staff' => $staff];
    }

    /** @return array{0: string, 1: string, 2: string} */
    public function machine(string $user, OrderBoard $board, string $code): array
    {
        $this->assertCanOpen($user, $board);
        if (!\in_array($code, array_column($this->planning->freeMachines(), 0), true)) {
            throw new \DomainException('Seleccione una máquina libre.');
        }

        return $this->planning->machine($code) ?? throw new \DomainException('La máquina no existe.');
    }

    /** @return array{0: string, 1: string} */
    public function person(string $user, OrderBoard $board, string $code): array
    {
        $this->assertCanOpen($user, $board);
        if (!\in_array($code, array_column($this->planning->freeStaff(), 0), true)) {
            throw new \DomainException('Seleccione una persona libre.');
        }

        return $this->planning->person($code) ?? throw new \DomainException('La persona no existe.');
    }

    /**
     * @param list<string> $machines
     * @param list<string> $staff
     */
    public function plan(string $user, OrderBoard $board, string $order, string $product, array $machines, array $staff): void
    {
        $this->assertCanOpen($user, $board);
        if ([] === $machines) {
            throw new \DomainException('Agregue al menos una máquina.');
        }
        if ([] === $staff) {
            throw new \DomainException('Agregue al menos una persona.');
        }
        $this->planning->plan($order, $product, array_values(array_unique($machines)), array_values(array_unique($staff)), $user);
    }

    private function assertCanOpen(string $user, OrderBoard $board): void
    {
        $this->permissions->assertGranted($user, $board->permission);
        if (!$board->canOpen) {
            throw new \DomainException('Esta pantalla es solo de consulta.');
        }
    }
}
