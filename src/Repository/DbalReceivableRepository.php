<?php

declare(strict_types=1);

namespace App\Repository;

use App\Finance\ReceivableBoard;
use Doctrine\DBAL\Connection;

final class DbalReceivableRepository implements ReceivableRepository
{
    public function __construct(private readonly Connection $connection, private readonly TransactionalTables $tables)
    {
    }

    public function orders(ReceivableBoard $board): array
    {
        $rows = $this->connection->fetchAllNumeric(match ($board) {
            ReceivableBoard::AllOrders => 'SELECT CCODPED, CCODCLI, CNOMBRE, CDESPR, CDESPRIO, DFECLIS FROM V_E01CPED ORDER BY CCODPED',
            ReceivableBoard::Delivered => "SELECT A.CCODPED, A.CCODCLI, A.CNOMBRE, A.CDESPR, A.CDESPRIO, A.DFECLIS FROM V_E01CPED A
                INNER JOIN E01CPED B ON A.CCODPED = B.CCODPED WHERE B.CESTADO = 'I' ORDER BY A.CCODPED",
            // The last column is the legacy "letras faltantes" value: paid amount / (total / installment).
            ReceivableBoard::WithInstallments => "SELECT A.CCODPED, A.CCODCLI, A.CNOMBRE, A.CDESPR, A.CDESPRIO, B.NTOTAL, C.NIMPORT,
                CASE WHEN C.NLETRA > 0 AND B.NTOTAL > 0 THEN C.NIMPORT / (B.NTOTAL / C.NLETRA) ELSE 0 END
                FROM V_E01CPED A INNER JOIN E01CPED B ON A.CCODPED = B.CCODPED INNER JOIN E01DCRE C ON A.CCODPED = C.CCODPED
                WHERE B.CESTADO = 'A' ORDER BY A.CCODPED",
        });

        return array_map(static fn (array $row): array => array_map(static fn (mixed $value): string => (string) $value, $row), $rows);
    }

    public function total(string $order): ?int
    {
        $total = $this->connection->fetchOne('SELECT NTOTAL FROM E01CPED WHERE CCODPED = :order', ['order' => $order]);

        return false === $total ? null : (int) $total;
    }

    public function createPlan(string $order, int $total, int $installment, ?string $dueDate, string $user): void
    {
        $this->tables->requireInnoDb('E01DCRE', 'E01CPED');
        $this->connection->transactional(function () use ($order, $total, $installment, $dueDate, $user): void {
            $this->connection->fetchOne('SELECT CCODPED FROM E01CPED WHERE CCODPED = :order FOR UPDATE', ['order' => $order]);
            if (false !== $this->connection->fetchOne('SELECT CCODPED FROM E01DCRE WHERE CCODPED = :order', ['order' => $order])) {
                throw new \DomainException('El pedido ya tiene letras registradas.');
            }
            $this->connection->insert('E01DCRE', ['CCODCRE' => $order, 'DFECHA' => date('Y-m-d'), 'NRESTAN' => $total,
                'NLETRA' => $installment, 'CCODPED' => $order, 'DFECLIM' => $dueDate, 'CCODUSU' => $user]);
        });
    }
}
