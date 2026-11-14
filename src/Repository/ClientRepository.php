<?php

declare(strict_types=1);

namespace App\Repository;

use App\Maintenance\ClientData;
use App\Maintenance\ClientType;

interface ClientRepository
{
    /** @return list<array{0: string, 1: string}> */
    public function choices(ClientType $type): array;

    /** @return array<string, string>|null */
    public function find(string $code): ?array;

    public function save(?string $code, ClientData $data, string $user): string;
}
