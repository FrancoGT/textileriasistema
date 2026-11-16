<?php

declare(strict_types=1);

namespace App\Repository;

interface DispatchRepository
{
    /** @return list<array{0: string, 1: string, 2: string, 3: string, 4: string}> code, client, name, date, delivery status (A or I) */
    public function finishedOrders(): array;

    /** Marks a finished order as delivered. Returns false when it was delivered before. */
    public function deliver(string $order): bool;

    /** @return list<list<string>> order, product, description, quantity, date */
    public function reportLines(string $order): array;
}
