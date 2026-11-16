<?php

declare(strict_types=1);

namespace App\Repository;

interface PurchaseRepository
{
    /** @return list<array{0: string, 1: string}> */
    public function suppliers(): array;

    /** @return list<array{0: string, 1: string}> */
    public function supplies(): array;

    /** @return array{0: string, 1: string, 2: string, 3: string}|null code, description, unit, price */
    public function supply(string $code): ?array;

    /**
     * Creates an unpaid supplier invoice (E01CCOM) with its lines (E02DCOM).
     *
     * @param array<string, int> $quantities quantity by supply code
     */
    public function create(string $supplier, array $quantities, string $user): string;
}
