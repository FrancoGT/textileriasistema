<?php

declare(strict_types=1);

namespace App\Repository;

interface StockRepository
{
    /** @return list<array{0: string, 1: string, 2: string}> active supply warehouses: code, name, address */
    public function warehouses(): array;

    /** @return list<array{0: string, 1: string, 2: string, 3: string, 4: string}> code, description, unit, price, quantity */
    public function stock(string $warehouse): array;

    /** @return array<string, array{0: string, 1: string, 2: string, 3: string}> code => code, description, unit, price */
    public function supplies(): array;

    /**
     * Moves quantities in one transaction. A null origin adds stock received
     * from a supplier without taking it from a warehouse.
     *
     * @param array<string, int> $quantities supply code => units
     */
    public function transfer(?string $origin, string $destination, array $quantities, string $user): void;
}
