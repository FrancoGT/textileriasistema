<?php

declare(strict_types=1);

namespace App\Repository;

use App\Maintenance\CatalogEntry;
use Doctrine\DBAL\Connection;

final class DbalCatalogTableRepository implements CatalogTableRepository
{
    public function __construct(private readonly Connection $connection, private readonly TransactionalTables $tables)
    {
    }

    public function headers(): array
    {
        return array_map(
            static fn (array $row): array => [(string) $row[0], (string) $row[1]],
            $this->connection->fetchAllNumeric("SELECT cCodTab, cDescri FROM E01TTAB WHERE cTipReg = '0' ORDER BY cCodTab"),
        );
    }

    public function header(string $table): ?array
    {
        $row = $this->connection->fetchAssociative(
            "SELECT cDescri AS description, cCodigo AS sample FROM E01TTAB WHERE cCodTab = :table AND cTipReg = '0'",
            ['table' => $table],
        );

        return false === $row ? null : ['description' => (string) $row['description'], 'sample' => (string) $row['sample']];
    }

    public function entries(string $table): array
    {
        return array_map(
            static fn (array $row): CatalogEntry => new CatalogEntry((string) $row[0], trim((string) $row[1]), (string) $row[2]),
            $this->connection->fetchAllNumeric(
                "SELECT nOrden, cCodigo, cDescri FROM E01TTAB WHERE cCodTab = :table AND cTipReg = '1' ORDER BY cCodigo",
                ['table' => $table],
            ),
        );
    }

    public function save(string $table, array $originalCodes, array $entries, string $user): void
    {
        $this->tables->requireInnoDb('E01TTAB');
        $this->connection->transactional(function () use ($table, $originalCodes, $entries, $user): void {
            $current = array_map('trim', $this->connection->fetchFirstColumn(
                "SELECT cCodigo FROM E01TTAB WHERE cCodTab = :table AND cTipReg = '1' ORDER BY cCodigo FOR UPDATE",
                ['table' => $table],
            ));
            if ($current !== $originalCodes) {
                throw new \DomainException('Otro usuario modificó la tabla. Vuelva a abrirla.');
            }
            foreach ($entries as $index => $entry) {
                $values = ['nOrden' => (int) $entry->order, 'cCodigo' => $entry->code, 'cDescri' => $entry->description, 'cCodUsu' => $user];
                if (\array_key_exists($index, $originalCodes)) {
                    $this->connection->update('E01TTAB', $values, ['cCodTab' => $table, 'cTipReg' => '1', 'cCodigo' => $originalCodes[$index]]);
                } else {
                    $this->connection->insert('E01TTAB', ['cCodTab' => $table, 'cTipReg' => '1'] + $values);
                }
            }
        });
    }
}
