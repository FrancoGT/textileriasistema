<?php

declare(strict_types=1);

namespace App\Repository;

interface PayableRepository
{
    /** @return list<list<string>> code, supplier code, supplier name, date, amount */
    public function invoices(bool $paid): array;

    /**
     * @return array{supplier: string, paid: bool, date: string, name: string, personType: string, personTypeName: string,
     *     dni: string, ruc: string, address: string}|null
     */
    public function invoice(string $code): ?array;

    /** @return list<list<string>> supply, description, unit, price, quantity, amount */
    public function lines(string $code): array;

    /** @return list<array{0: string, 1: string}> */
    public function supplierAccounts(string $supplier): array;

    /** @return list<array{0: string, 1: string}> */
    public function banks(): array;

    /** @return list<array{0: string, 1: string}> */
    public function bankAccounts(string $bank): array;

    public function pay(string $code, string $bank, string $account): void;
}
