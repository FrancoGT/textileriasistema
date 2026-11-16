<?php

declare(strict_types=1);

namespace App\Dispatch;

use App\Report\OrderReportPdf;
use App\Repository\DispatchRepository;
use App\Security\ModulePermission;

/** Delivers finished orders to the client and prints their report (ERP9010). */
final class DispatchService
{
    private const PERMISSION = 'ERP9010';

    public function __construct(
        private readonly DispatchRepository $dispatch,
        private readonly OrderReportPdf $report,
        private readonly ModulePermission $permissions,
    ) {
    }

    /** @return list<array{0: string, 1: string, 2: string, 3: string, 4: string}> the last column is PENDIENTE or ENTREGADO */
    public function orders(string $user): array
    {
        $this->permissions->assertGranted($user, self::PERMISSION);

        return array_map(
            static fn (array $order): array => [$order[0], $order[1], $order[2], $order[3], 'I' === $order[4] ? 'ENTREGADO' : 'PENDIENTE'],
            $this->dispatch->finishedOrders(),
        );
    }

    public function deliver(string $user, string $order): string
    {
        $this->permissions->assertGranted($user, self::PERMISSION);
        if ('' === $order) {
            throw new \DomainException('Seleccione un pedido.');
        }

        return $this->dispatch->deliver($order) ? 'Pedido entregado.' : 'El pedido ya había sido entregado.';
    }

    public function report(string $user, string $order): string
    {
        $this->permissions->assertGranted($user, self::PERMISSION);
        $lines = $this->dispatch->reportLines($order);
        if ([] === $lines) {
            throw new \DomainException('El pedido no tiene productos.');
        }

        return $this->report->render($lines);
    }
}
