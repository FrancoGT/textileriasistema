<?php

declare(strict_types=1);

namespace App\Repository;

use App\Maintenance\WarehouseData;

interface WarehouseRepository
{
    /** @return list<array{0: string, 1: string}> */
    public function choices(): array;

    /** @return array{code: string, name: string, address: string, status: string, type: string}|null */
    public function find(string $code): ?array;

    public function save(?string $code, WarehouseData $data, string $user): string;
}
