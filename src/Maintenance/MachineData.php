<?php

declare(strict_types=1);

namespace App\Maintenance;

use Symfony\Component\DependencyInjection\Attribute\Exclude;

#[Exclude]
final readonly class MachineData
{
    public function __construct(public string $description, public string $status, public string $type)
    {
    }
}
