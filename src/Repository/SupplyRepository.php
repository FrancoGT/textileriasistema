<?php

declare(strict_types=1);

namespace App\Repository;

use App\Maintenance\SupplyData;

interface SupplyRepository
{
    /** @return list<list<string>> code, description, subtype, unit, granularity, price */
    public function all(): array;

    /** @return array{code: string, description: string, unit: string, price: string, granularity: string}|null */
    public function find(string $code): ?array;

    public function save(?string $code, SupplyData $data, string $user): string;

    public function delete(string $code): void;
}
