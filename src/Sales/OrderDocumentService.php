<?php

declare(strict_types=1);

namespace App\Sales;

use App\Finance\TaxBreakdown;
use App\Report\OrderDocumentRtf;
use App\Repository\SalesOrderRepository;
use App\Security\ModulePermission;

/** Lists the orders waiting for planning and prints the order document (ERP2010, ERP2020). */
final class OrderDocumentService
{
    public const SCREENS = ['ERP2010', 'ERP2020'];

    public function __construct(
        private readonly SalesOrderRepository $orders,
        private readonly OrderDocumentRtf $document,
        private readonly ModulePermission $permissions,
    ) {
    }

    /** @return list<list<string>> */
    public function pendingOrders(string $user): array
    {
        $this->permissions->assertGranted($user, 'ERP2020');

        return $this->orders->pendingOrders();
    }

    public function exists(string $user, string $screen, string $code): void
    {
        $this->permissions->assertGranted($user, $screen);
        if ('' === $code) {
            throw new \DomainException('Seleccione un pedido.');
        }
        if (null === $this->orders->order($code)) {
            throw new \DomainException('El pedido no existe.');
        }
    }

    /** $screen is the menu option that opened the document. */
    public function render(string $user, string $screen, string $code): string
    {
        if (!\in_array($screen, self::SCREENS, true)) {
            throw new \DomainException('El pedido no existe.');
        }
        $this->permissions->assertGranted($user, $screen);
        $order = $this->orders->order($code) ?? throw new \DomainException('El pedido no existe.');
        $client = $this->orders->client($order['client']) ?? throw new \DomainException('El cliente del pedido no existe.');
        $lines = [];
        $total = 0.0;
        foreach ($order['lines'] as [$product, $description, $quantity]) {
            $price = $this->orders->product($product)[2] ?? 0.0;
            $total += $price * $quantity;
            $lines[] = [$description, (string) $price, (string) $quantity, (string) ($price * $quantity)];
        }
        $totals = TaxBreakdown::fromTotal($total);

        // The legacy document always printed the RUC, also for natural persons.
        return $this->document->render(['code' => $order['code'], 'name' => $client['name'], 'address' => $client['address'],
            'number' => $client['natural'] ? $client['dni'] : $client['ruc'], 'phone' => $client['phone'],
            'saleValue' => $totals->saleValue, 'tax' => $totals->tax, 'total' => $totals->total], $lines);
    }
}
