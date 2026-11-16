<?php

declare(strict_types=1);

namespace App\Repository;

interface SalesOrderRepository
{
    /** @return list<array{0: string, 1: string}> client code and document number (DNI for natural persons, RUC for the others) */
    public function clients(bool $natural): array;

    /** @return array{name: string, address: string, phone: string, natural: bool, dni: string, ruc: string}|null */
    public function client(string $code): ?array;

    /** @return list<array{0: string, 1: string}> finished products that can be ordered */
    public function products(): array;

    /**
     * The unit price of a product is the sum of the prices of the supplies in its recipe.
     *
     * @return array{0: string, 1: string, 2: float}|null code, description, unit price. Null when the product has no recipe.
     */
    public function product(string $code): ?array;

    /** @param array<string, int> $quantities quantity by product code */
    public function create(string $client, string $priority, string $dueDate, array $quantities, float $total, string $user): string;

    /** @return list<list<string>> code, client code, client name, planning status, priority, delivery date */
    public function pendingOrders(): array;

    /**
     * @return array{code: string, client: string, lines: list<array{0: string, 1: string, 2: int}>}|null
     *     lines are product code, description and quantity
     */
    public function order(string $code): ?array;
}
