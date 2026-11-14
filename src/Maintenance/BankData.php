<?php

declare(strict_types=1);

namespace App\Maintenance;

use Symfony\Component\DependencyInjection\Attribute\Exclude;

#[Exclude]
final readonly class BankData
{
    public function __construct(public string $name, public string $status, public string $city)
    {
    }
}
