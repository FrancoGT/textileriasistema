<?php

declare(strict_types=1);

namespace App\Repository;

use Doctrine\DBAL\Connection;

final class CatalogRepository
{
    public function __construct(private readonly Connection $connection)
    {
    }

    /** @return list<array{0: string, 1: string}> */
    public function choices(string $table, bool $singleCharacter = false): array
    {
        $rows = $this->connection->fetchAllNumeric(
            'SELECT cCodigo, cDescri FROM V_E01TTAB WHERE cCodTab = :table ORDER BY cDescri',
            ['table' => $table],
        );

        return array_map(static fn (array $row): array => [
            $singleCharacter ? substr((string) $row[0], 0, 1) : trim((string) $row[0]),
            (string) $row[1],
        ], $rows);
    }
}
