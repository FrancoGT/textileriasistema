<?php

declare(strict_types=1);

namespace App\Repository;

use App\Maintenance\SupplierData;

interface SupplierRepository
{
    public function all(): array;

    public function find(string $code): ?array;

    public function save(?string $code, SupplierData $data, string $user): string;
}
