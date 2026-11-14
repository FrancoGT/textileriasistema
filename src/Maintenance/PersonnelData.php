<?php

declare(strict_types=1);

namespace App\Maintenance;

use Symfony\Component\DependencyInjection\Attribute\Exclude;

#[Exclude]
final readonly class PersonnelData
{
    public function __construct(
        public string $name,
        public string $address,
        public string $email,
        public string $dni,
        public string $sex,
        public string $civilStatus,
        public string $status,
    ) {
    }
}
