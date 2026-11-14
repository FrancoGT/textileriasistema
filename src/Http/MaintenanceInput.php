<?php

declare(strict_types=1);

namespace App\Http;

use Symfony\Component\DependencyInjection\Attribute\Exclude;

#[Exclude]
final readonly class MaintenanceInput
{
    /** @param array<string, string> $fields @param list<string> $productionLines */
    public function __construct(public string $action, public array $fields, public array $productionLines)
    {
    }

    public function value(string $name): string
    {
        return $this->fields[$name] ?? '';
    }
}
