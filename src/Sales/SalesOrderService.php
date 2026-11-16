<?php

declare(strict_types=1);

namespace App\Sales;

use App\Finance\TaxBreakdown;
use App\Repository\CatalogRepository;
use App\Repository\SalesOrderRepository;
use App\Security\ModulePermission;

/** Registers client orders (ERP2010). */
final class SalesOrderService
{
    private const PERMISSION = 'ERP2010';
    private const PERSON_TYPES = '098';
    private const PRIORITIES = '002';

    public function __construct(
        private readonly SalesOrderRepository $orders,
        private readonly CatalogRepository $catalogs,
        private readonly ModulePermission $permissions,
    ) {
    }

    /** @return list<array{0: string, 1: string}> */
    public function personTypes(string $user): array
    {
        $this->permissions->assertGranted($user, self::PERMISSION);

        return $this->catalogs->choices(self::PERSON_TYPES, true);
    }

    /**
     * @return array{clients: list<array{0: string, 1: string}>, priorities: list<array{0: string, 1: string}>, products: list<array{0: string, 1: string}>}
     */
    public function options(string $user, bool $natural): array
    {
        $this->permissions->assertGranted($user, self::PERMISSION);

        return ['clients' => $this->orders->clients($natural), 'priorities' => $this->catalogs->choices(self::PRIORITIES, true),
            'products' => $this->orders->products()];
    }

    /** @return array{name: string, address: string, phone: string} */
    public function client(string $user, bool $natural, string $code): array
    {
        $this->permissions->assertGranted($user, self::PERMISSION);
        $client = $this->orders->client($code);
        if (null === $client || $client['natural'] !== $natural) {
            throw new \DomainException('Seleccione un cliente.');
        }

        return ['name' => $client['name'], 'address' => $client['address'], 'phone' => $client['phone']];
    }

    /** @return array{0: string, 1: string, 2: float} code, description, unit price */
    public function product(string $user, string $code): array
    {
        $this->permissions->assertGranted($user, self::PERMISSION);
        if (!\in_array($code, array_column($this->orders->products(), 0), true)) {
            throw new \DomainException('Seleccione un producto.');
        }

        return $this->orders->product($code) ?? throw new \DomainException('El producto no tiene insumos en su receta.');
    }

    /**
     * Prices the lines with the current recipes.
     *
     * @param array<string, string> $quantities quantity by product code, as typed
     *
     * @return array{lines: list<array{0: string, 1: string, 2: float, 3: int, 4: float}>, totals: TaxBreakdown}
     */
    public function quote(string $user, array $quantities): array
    {
        if ([] === $quantities) {
            throw new \DomainException('Agregue al menos un producto.');
        }
        $lines = [];
        foreach ($quantities as $code => $quantity) {
            [$product, $description, $price] = $this->product($user, (string) $code);
            if (!ctype_digit($quantity) || (int) $quantity < 1 || (int) $quantity > 100000) {
                throw new \DomainException('La cantidad del producto '.$product.' debe ser un entero positivo.');
            }
            $lines[] = [$product, $description, $price, (int) $quantity, round($price * (int) $quantity, 2)];
        }

        return ['lines' => $lines, 'totals' => TaxBreakdown::fromTotal(round(array_sum(array_column($lines, 4)), 2))];
    }

    /** @param array<string, string> $quantities */
    public function create(string $user, bool $natural, string $client, string $priority, string $dueDate, array $quantities): string
    {
        $options = $this->options($user, $natural);
        if (!\in_array($client, array_column($options['clients'], 0), true)) {
            throw new \DomainException('Seleccione un cliente.');
        }
        if (!\in_array($priority, array_column($options['priorities'], 0), true)) {
            throw new \DomainException('Seleccione una prioridad.');
        }
        $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $dueDate);
        if (false === $date || $date->format('Y-m-d') !== $dueDate) {
            throw new \DomainException('La fecha listo debe tener el formato aaaa-mm-dd.');
        }
        $quote = $this->quote($user, $quantities);

        return $this->orders->create($client, $priority, $dueDate, array_column($quote['lines'], 3, 0), $quote['totals']->total, $user);
    }
}
