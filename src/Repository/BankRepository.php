<?php

declare(strict_types=1);

namespace App\Repository;

use App\Maintenance\BankData;

interface BankRepository
{
    /** @return list<array{0: string, 1: string}> */
    public function choices(): array;

    /** @return array{code: string, name: string, status: string, city: string}|null */
    public function find(string $code): ?array;

    public function save(?string $code, BankData $data, string $user): string;
}
