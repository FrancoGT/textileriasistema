<?php

declare(strict_types=1);

namespace App\Repository;

use App\Maintenance\PersonnelData;

interface PersonnelRepository
{
    public function choices(): array;

    public function find(string $code): ?array;

    public function save(?string $code, PersonnelData $data, string $user): string;
}
