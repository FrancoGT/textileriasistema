<?php

declare(strict_types=1);

namespace App\Repository;

use App\Maintenance\CatalogEntry;

interface CatalogTableRepository
{
    /** @return list<array{0: string, 1: string}> */
    public function headers(): array;

    /** @return array{description: string, sample: string}|null */
    public function header(string $table): ?array;

    /** @return list<CatalogEntry> ordered by code */
    public function entries(string $table): array;

    /**
     * Updates the entries that had $originalCodes, in the same order, and
     * inserts the remaining entries. Other columns, such as mTexto, are kept.
     *
     * @param list<string>       $originalCodes
     * @param list<CatalogEntry> $entries
     */
    public function save(string $table, array $originalCodes, array $entries, string $user): void;
}
