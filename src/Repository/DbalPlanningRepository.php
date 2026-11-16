<?php

declare(strict_types=1);

namespace App\Repository;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;

final class DbalPlanningRepository implements PlanningRepository
{
    // Staff member 999999 is the administrator record. The legacy planning screen never offered it.
    private const ADMINISTRATOR = '999999';

    public function __construct(private readonly Connection $connection, private readonly TransactionalTables $tables)
    {
    }

    public function freeMachines(): array
    {
        return $this->pairs("SELECT CCODMAQ, CDESCRI FROM E01MMAQ WHERE CESTADOOP = 'L' ORDER BY CCODMAQ");
    }

    public function freeStaff(): array
    {
        return $this->pairs("SELECT CCODPER, CNOMBRE FROM E01MPER WHERE CESTADOOP = 'L' AND CCODPER <> :administrator ORDER BY CCODPER", ['administrator' => self::ADMINISTRATOR]);
    }

    public function machine(string $code): ?array
    {
        $row = $this->connection->fetchNumeric(
            "SELECT A.CCODMAQ, A.CDESCRI, B.CDESCRI FROM E01MMAQ A LEFT JOIN V_E01TTAB B ON B.CCODIGO = A.CTIPO AND B.CCODTAB = '005' WHERE A.CCODMAQ = :code",
            ['code' => $code],
        );

        return false === $row ? null : [(string) $row[0], (string) $row[1], (string) $row[2]];
    }

    public function person(string $code): ?array
    {
        $row = $this->connection->fetchNumeric('SELECT CCODPER, CNOMBRE FROM E01MPER WHERE CCODPER = :code', ['code' => $code]);

        return false === $row ? null : [(string) $row[0], (string) $row[1]];
    }

    public function plan(string $order, string $product, array $machines, array $staff, string $user): void
    {
        $this->tables->requireInnoDb('E01MAQ', 'E01PER', 'E01MMAQ', 'E01MPER', 'E01DPED', 'E01CPED');
        $this->connection->transactional(function () use ($order, $product, $machines, $staff, $user): void {
            $status = $this->connection->fetchOne(
                'SELECT CESTADOPL FROM E01DPED WHERE CCODPED = :order AND CCODPROD = :product FOR UPDATE',
                ['order' => $order, 'product' => $product],
            );
            if (false === $status) {
                throw new \DomainException('El producto no pertenece al pedido.');
            }
            if ('I' !== $status) {
                throw new \DomainException('El producto ya fue planificado.');
            }
            $this->reserve('E01MMAQ', 'CCODMAQ', $machines, 'Una de las máquinas ya no está libre.');
            $this->reserve('E01MPER', 'CCODPER', $staff, 'Una de las personas ya no está libre.');
            $assignment = ['CCODPED' => $order, 'CCODPROD' => $product, 'CESTADOOP' => 'L', 'CESTADO' => 'A', 'CCODUSU' => $user];
            foreach ($machines as $machine) {
                $this->connection->insert('E01MAQ', ['CCODMAQ' => $machine] + $assignment);
            }
            foreach ($staff as $person) {
                $this->connection->insert('E01PER', ['CCODPER' => $person] + $assignment);
            }
            $this->connection->update('E01DPED', ['CESTADOPL' => 'C'], ['CCODPED' => $order, 'CCODPROD' => $product]);
            $pending = $this->connection->fetchOne("SELECT COUNT(*) FROM E01DPED WHERE CCODPED = :order AND CESTADOPL = 'I'", ['order' => $order]);
            if (0 === (int) $pending) {
                $this->connection->update('E01CPED', ['CESTADOPL' => 'C'], ['CCODPED' => $order]);
            }
        });
    }

    /** @param list<string> $codes */
    private function reserve(string $table, string $column, array $codes, string $message): void
    {
        $free = $this->connection->fetchFirstColumn(
            "SELECT $column FROM $table WHERE $column IN (:codes) AND CESTADOOP = 'L' FOR UPDATE",
            ['codes' => $codes],
            ['codes' => ArrayParameterType::STRING],
        );
        if (\count(array_unique($free)) !== \count($codes)) {
            throw new \DomainException($message);
        }
        $this->connection->executeStatement(
            "UPDATE $table SET CESTADOOP = 'O' WHERE $column IN (:codes)",
            ['codes' => $codes],
            ['codes' => ArrayParameterType::STRING],
        );
    }

    /**
     * @param array<string, string> $parameters
     *
     * @return list<array{0: string, 1: string}>
     */
    private function pairs(string $sql, array $parameters = []): array
    {
        return array_map(static fn (array $row): array => [(string) $row[0], (string) $row[1]], $this->connection->fetchAllNumeric($sql, $parameters));
    }
}
