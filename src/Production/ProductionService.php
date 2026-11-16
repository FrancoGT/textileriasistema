<?php

declare(strict_types=1);

namespace App\Production;

use App\Repository\OrderRepository;
use App\Repository\ProductionRepository;
use App\Security\ModulePermission;

/** Assigns the next process of planned order lines (ERP4000 to ERP4070). */
final class ProductionService
{
    public function __construct(
        private readonly OrderRepository $orders,
        private readonly ProductionRepository $production,
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
    public function stageLines(string $user, StageBoard $board): array
    {
        $this->permissions->assertGranted($user, $board->permission);

        return $this->orders->stageLines($board->stage);
    }

    /** @return list<list<string>> */
    public function lines(string $user, OrderBoard $board, string $order): array
    {
        $this->assertCanOpen($user, $board);
        if ('' === $order) {
            throw new \DomainException('Seleccione un pedido.');
        }
        $lines = $this->orders->productionLines($order);
        if ([] === $lines) {
            throw new \DomainException('El pedido no tiene productos para producción.');
        }

        return $lines;
    }

    /**
     * @return array{description: string, quantity: string, process: string, stage: ProductionStage,
     *     machines: list<array{0: string, 1: string}>, staff: list<array{0: string, 1: string}>}
     */
    public function assignment(string $user, OrderBoard $board, string $order, string $product): array
    {
        $this->assertCanOpen($user, $board);
        if ('' === $product) {
            throw new \DomainException('Seleccione un producto.');
        }
        $line = $this->orders->line($order, $product) ?? throw new \DomainException('El producto no pertenece al pedido.');
        if ('C' !== $line['planning']) {
            throw new \DomainException('El producto aún no fue planificado.');
        }
        if (ProductionStage::Finishing === $line['stage'] && 'E' === $line['production']) {
            throw new \DomainException('El producto ya terminó todos los procesos.');
        }
        // A line waits for its first process with status P, and for the next ones with status E.
        if ('P' === $line['production'] && ProductionStage::Pending !== $line['stage']) {
            throw new \DomainException('El producto tiene un trabajo en proceso.');
        }
        $machines = $this->production->plannedMachines($order, $product);
        if ([] === $machines) {
            throw new \DomainException('No hay máquinas libres.');
        }
        $staff = $this->production->plannedStaff($order, $product);
        if ([] === $staff) {
            throw new \DomainException('No hay personal libre.');
        }

        return ['description' => $line['description'], 'quantity' => $line['quantity'], 'process' => $line['process'],
            'stage' => $line['stage'], 'machines' => $machines, 'staff' => $staff];
    }

    public function assign(string $user, OrderBoard $board, string $order, string $product, string $machine, string $person): void
    {
        $assignment = $this->assignment($user, $board, $order, $product);
        if (!\in_array($machine, array_column($assignment['machines'], 0), true)) {
            throw new \DomainException('Seleccione una máquina reservada para el producto.');
        }
        if (!\in_array($person, array_column($assignment['staff'], 0), true)) {
            throw new \DomainException('Seleccione una persona reservada para el producto.');
        }
        $this->production->assign($order, $product, $assignment['stage'], $machine, $person, $user);
    }

    private function assertCanOpen(string $user, OrderBoard $board): void
    {
        $this->permissions->assertGranted($user, $board->permission);
        if (!$board->canOpen) {
            throw new \DomainException('Esta pantalla es solo de consulta.');
        }
    }
}
