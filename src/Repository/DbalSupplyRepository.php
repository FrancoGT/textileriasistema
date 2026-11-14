<?php

declare(strict_types=1);

namespace App\Repository;

use App\Maintenance\SupplyData;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;

final class DbalSupplyRepository implements SupplyRepository
{
    // MyISAM tables that store supply codes without a foreign key.
    private const UNCHECKED_REFERENCES = ['E01DCOM', 'E01DPOG'];

    public function __construct(private readonly Connection $connection, private readonly TransactionalTables $tables)
    {
    }

    public function all(): array
    {
        $rows = $this->connection->fetchAllNumeric(
            "SELECT a.cCodIns, a.cDescri, COALESCE(s.cDescri, ''), COALESCE(u.cDescri, ''), a.nCanGra, a.nPrecio FROM E01MINS a
                LEFT JOIN V_E01TTAB s ON s.cCodTab = '011' AND s.cCodigo = SUBSTR(a.cCodIns, 1, 2)
                LEFT JOIN V_E01TTAB u ON u.cCodTab = '007' AND u.cCodigo = a.cUniMed
                ORDER BY a.cDescri",
        );

        return array_map(static fn (array $row): array => array_map(static fn (mixed $value): string => (string) $value, $row), $rows);
    }

    public function find(string $code): ?array
    {
        $row = $this->connection->fetchAssociative(
            'SELECT cCodIns AS code, cDescri AS description, cUniMed AS unit, nPrecio AS price, nCanGra AS granularity FROM E01MINS WHERE cCodIns = :code',
            ['code' => $code],
        );

        return false === $row ? null : array_map(static fn (mixed $value): string => trim((string) $value), $row);
    }

    public function save(?string $code, SupplyData $data, string $user): string
    {
        $this->tables->requireInnoDb('E01MINS');

        return $this->connection->transactional(function () use ($code, $data, $user): string {
            $values = ['cUniMed' => $data->unit, 'nPrecio' => $data->price, 'nCanGra' => (int) $data->granularity, 'cCodUsu' => $user];
            if (null === $code) {
                // The code is the two-character subtype followed by a four-digit sequence.
                $last = $this->connection->fetchOne(
                    'SELECT cCodIns FROM E01MINS WHERE cCodIns LIKE :prefix ORDER BY cCodIns DESC LIMIT 1 FOR UPDATE',
                    ['prefix' => $data->subtype.'%'],
                );
                $next = (int) substr((string) $last, 2) + 1;
                if ($next > 9999) {
                    throw new \DomainException('No hay códigos de insumo disponibles para este subtipo.');
                }
                $code = $data->subtype.str_pad((string) $next, 4, '0', STR_PAD_LEFT);
                $this->connection->insert('E01MINS', ['cCodIns' => $code, 'cDescri' => $data->description] + $values);

                return $code;
            }
            if (false === $this->connection->fetchOne('SELECT cCodIns FROM E01MINS WHERE cCodIns = :code FOR UPDATE', ['code' => $code])) {
                throw new \DomainException('El insumo no existe.');
            }
            $this->connection->update('E01MINS', $values, ['cCodIns' => $code]);

            return $code;
        });
    }

    public function delete(string $code): void
    {
        $this->tables->requireInnoDb('E01MINS');
        $this->connection->transactional(function () use ($code): void {
            if (false === $this->connection->fetchOne('SELECT cCodIns FROM E01MINS WHERE cCodIns = :code FOR UPDATE', ['code' => $code])) {
                throw new \DomainException('El insumo no existe.');
            }
            foreach (self::UNCHECKED_REFERENCES as $table) {
                if (false !== $this->connection->fetchOne('SELECT 1 FROM '.$table.' WHERE cCodIns = :code LIMIT 1', ['code' => $code])) {
                    throw new \DomainException('El insumo está en uso y no se puede eliminar.');
                }
            }
            try {
                $this->connection->delete('E01MINS', ['cCodIns' => $code]);
            } catch (ForeignKeyConstraintViolationException) {
                throw new \DomainException('El insumo está en uso y no se puede eliminar.');
            }
        });
    }
}
