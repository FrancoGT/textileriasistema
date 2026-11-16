<?php

declare(strict_types=1);

namespace App\Repository;

use App\Production\OrderFilter;
use App\Production\ProductionStage;

interface OrderRepository
{
    /** @return list<list<string>> code, client code, client name, status, priority, delivery date */
    public function orders(OrderFilter $filter): array;

    /** @return list<list<string>> order, product, description, quantity, planning status */
    public function planningLines(string $order): array;

    /** @return list<list<string>> order, product, description, quantity, production status, process */
    public function productionLines(string $order): array;

    /** @return list<list<string>> order, product, description, quantity, production status, process */
    public function stageLines(ProductionStage $stage): array;

    /**
     * @return array{description: string, quantity: string, planning: string, production: string, stage: ProductionStage, process: string}|null
     */
    public function line(string $order, string $product): ?array;
}
