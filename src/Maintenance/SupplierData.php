<?php

declare(strict_types=1);

namespace App\Maintenance;

use Symfony\Component\DependencyInjection\Attribute\Exclude;

#[Exclude]
final readonly class SupplierData
{
    /** @param list<string> $productionLines */
    public function __construct(
        public string $name,
        public string $address,
        public string $dni,
        public string $ruc,
        public string $personType,
        public string $date,
        public string $notes,
        public array $productionLines,
    ) {
    }
}
