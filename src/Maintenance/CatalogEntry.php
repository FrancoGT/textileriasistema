<?php

declare(strict_types=1);

namespace App\Maintenance;

use Symfony\Component\DependencyInjection\Attribute\Exclude;

#[Exclude]
final readonly class CatalogEntry
{
    public function __construct(public string $order, public string $code, public string $description)
    {
    }
}
