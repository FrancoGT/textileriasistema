<?php

declare(strict_types=1);

namespace App\Repository;

use App\Finance\ReceivableBoard;

interface ReceivableRepository
{
    /** @return list<list<string>> */
    public function orders(ReceivableBoard $board): array;

    public function total(string $order): ?int;

    /**
     * Registers the installments of an order (E01DCRE). $installment is the
     * amount of each installment.
     */
    public function createPlan(string $order, int $total, int $installment, ?string $dueDate, string $user): void;
}
