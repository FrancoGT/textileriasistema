<?php

declare(strict_types=1);

namespace App\Maintenance;

use Symfony\Component\DependencyInjection\Attribute\Exclude;

#[Exclude]
final readonly class WarehouseData
{
    public function __construct(public string $name, public string $address, public string $status, public string $type)
    {
    }
}
