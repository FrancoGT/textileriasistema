<?php

declare(strict_types=1);

namespace App\Repository;

use Doctrine\DBAL\Connection;

final class DbalStockRepository implements StockRepository
{
    public function __construct(private readonly Connection $connection, private readonly TransactionalTables $tables)
    {
    }

    public function warehouses(): array
    {
        return $this->strings($this->connection->fetchAllNumeric(
            "SELECT cCodAlm, cNomAlm, cDirec FROM E01MALM WHERE cEstado = 'A' AND cTipo = 'I' ORDER BY cCodAlm",
        ));
    }

    public function stock(string $warehouse): array
    {
        return $this->strings($this->connection->fetchAllNumeric(
            "SELECT s.cCodIns, i.cDescri, COALESCE(u.cDescri, ''), i.nPrecio, s.nCant FROM E01PALM s
                INNER JOIN E01MINS i ON i.cCodIns = s.cCodIns
                LEFT JOIN V_E01TTAB u ON u.cCodTab = '007' AND u.cCodigo = i.cUniMed
                WHERE s.cCodAlm = :warehouse ORDER BY s.cCodIns",
            ['warehouse' => $warehouse],
        ));
    }

    public function supplies(): array
    {
        $rows = $this->strings($this->connection->fetchAllNumeric(
            "SELECT i.cCodIns, i.cDescri, COALESCE(u.cDescri, ''), i.nPrecio FROM E01MINS i
                LEFT JOIN V_E01TTAB u ON u.cCodTab = '007' AND u.cCodigo = i.cUniMed ORDER BY i.cDescri",
        ));

        return array_combine(array_column($rows, 0), $rows);
    }

    public function transfer(?string $origin, string $destination, array $quantities, string $user): void
    {
        $this->tables->requireInnoDb('E01PALM', 'E01MALM');
        $this->connection->transactional(function () use ($origin, $destination, $quantities, $user): void {
            // E01PALM has no unique key. Locking the warehouse rows serializes transfers that touch them.
            $warehouses = array_values(array_filter([$origin, $destination]));
            sort($warehouses);
            foreach ($warehouses as $warehouse) {
                $this->connection->fetchOne('SELECT cCodAlm FROM E01MALM WHERE cCodAlm = :code FOR UPDATE', ['code' => $warehouse]);
            }
            foreach ($quantities as $supply => $units) {
                if (null !== $origin) {
                    $available = $this->quantity($origin, $supply);
                    if (null === $available || $available < $units) {
                        throw new \DomainException('El almacén de origen no tiene unidades suficientes del insumo '.$supply.'.');
                    }
                    $this->connection->update('E01PALM', ['nCant' => $available - $units, 'cCodUsu' => $user], ['cCodAlm' => $origin, 'cCodIns' => $supply]);
                }
                $current = $this->quantity($destination, $supply);
                if (null === $current) {
                    $this->connection->insert('E01PALM', ['cCodAlm' => $destination, 'cCodIns' => $supply, 'nCant' => $units, 'cCodUsu' => $user]);
                } else {
                    $this->connection->update('E01PALM', ['nCant' => $current + $units, 'cCodUsu' => $user], ['cCodAlm' => $destination, 'cCodIns' => $supply]);
                }
            }
        });
    }

    private function quantity(string $warehouse, string $supply): ?int
    {
        $quantity = $this->connection->fetchOne(
            'SELECT nCant FROM E01PALM WHERE cCodAlm = :warehouse AND cCodIns = :supply LIMIT 1 FOR UPDATE',
            ['warehouse' => $warehouse, 'supply' => $supply],
        );

        return false === $quantity ? null : (int) $quantity;
    }

    /** @return list<list<string>> */
    private function strings(array $rows): array
    {
        return array_map(static fn (array $row): array => array_map(static fn (mixed $value): string => trim((string) $value), $row), $rows);
    }
}
