<?php

declare(strict_types=1);

namespace App\Repository;

use Doctrine\DBAL\Connection;

final class DbalDispatchRepository implements DispatchRepository
{
    public function __construct(private readonly Connection $connection, private readonly TransactionalTables $tables)
    {
    }

    public function finishedOrders(): array
    {
        return array_map(
            static fn (array $row): array => [(string) $row[0], (string) $row[1], (string) $row[2], (string) $row[3], (string) $row[4]],
            $this->connection->fetchAllNumeric('SELECT A.CCODPED, A.CCODCLI, A.CNOMBRE, A.DFECLIS, B.CESTADO FROM V_E01CPED A
                INNER JOIN E01CPED B ON A.CCODPED = B.CCODPED WHERE A.CESTADOPL = \'C\' AND A.CESTADOPR = \'E\' ORDER BY A.CCODPED'),
        );
    }

    public function deliver(string $order): bool
    {
        $this->tables->requireInnoDb('E01CPED');

        return $this->connection->transactional(function () use ($order): bool {
            $status = $this->connection->fetchOne("SELECT CESTADO FROM E01CPED WHERE CCODPED = :order AND CESTADOPL = 'C' AND CESTADOPR = 'E' FOR UPDATE", ['order' => $order]);
            if (false === $status) {
                throw new \DomainException('El pedido no terminó su producción.');
            }
            if ('A' !== $status) {
                return false;
            }
            $this->connection->update('E01CPED', ['CESTADO' => 'I'], ['CCODPED' => $order]);

            return true;
        });
    }

    public function reportLines(string $order): array
    {
        return array_map(
            static fn (array $row): array => array_map(static fn (mixed $value): string => (string) $value, $row),
            $this->connection->fetchAllNumeric(
                'SELECT A.CCODPED, B.CCODPROD, C.CDESCRI, B.NCANT, A.DFECLIS FROM E01CPED A
                 INNER JOIN E01DPED B ON A.CCODPED = B.CCODPED INNER JOIN E01MPROD C ON B.CCODPROD = C.CCODPROD
                 WHERE A.CCODPED = :order ORDER BY B.CCODPROD',
                ['order' => $order],
            ),
        );
    }
}
