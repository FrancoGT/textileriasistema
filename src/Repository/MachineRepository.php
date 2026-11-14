<?php

declare(strict_types=1);

namespace App\Repository;

use App\Maintenance\MachineData;

interface MachineRepository
{
    /** @return list<array{0: string, 1: string}> */
    public function choices(): array;

    /** @return array{code: string, description: string, status: string, type: string}|null */
    public function find(string $code): ?array;

    public function save(?string $code, MachineData $data, string $user): string;
}
