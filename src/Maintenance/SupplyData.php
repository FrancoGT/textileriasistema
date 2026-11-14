<?php

declare(strict_types=1);

namespace App\Maintenance;

use Symfony\Component\DependencyInjection\Attribute\Exclude;

#[Exclude]
final readonly class SupplyData
{
    public function __construct(
        public string $subtype,
        public string $description,
        public string $unit,
        public string $price,
        public string $granularity,
    ) {
    }
}
