<?php

declare(strict_types=1);

namespace App\Http;

use Symfony\Component\DependencyInjection\Attribute\Exclude;

#[Exclude]
final readonly class MaintenanceInput
{
    /**
     * @param array<string, string>                                            $fields
     * @param list<string>                                                     $productionLines
     * @param array<string, array<array-key, string|array<array-key, string>>> $grids
     */
    public function __construct(public string $action, public array $fields, public array $productionLines, public array $grids = [], public string $button = '')
    {
    }

    public function value(string $name): string
    {
        return $this->fields[$name] ?? '';
    }

    /** @return list<string> */
    public function list(string $name): array
    {
        return array_values(array_filter($this->grids[$name] ?? [], 'is_string'));
    }

    /**
     * Rows ordered by their numeric key, each with its cells indexed from 0.
     *
     * @return list<array<int, string>>
     */
    public function rows(string $name): array
    {
        $rows = array_filter($this->grids[$name] ?? [], 'is_array');
        ksort($rows, SORT_NUMERIC);

        return array_values($rows);
    }
}
