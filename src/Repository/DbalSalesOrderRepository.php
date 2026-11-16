<?php

declare(strict_types=1);

namespace App\Repository;

use App\Maintenance\ClientType;
use Doctrine\DBAL\Connection;

final class DbalSalesOrderRepository implements SalesOrderRepository
{
    public function __construct(private readonly Connection $connection, private readonly TransactionalTables $tables)
    {
    }

    public function clients(bool $natural): array
    {
        // The legacy screen listed the DNI column for companies too, which holds a placeholder for them.
        $sql = $natural
            ? 'SELECT CCODCLI, CNRODNI FROM E01MCLI WHERE CTIPO = :natural ORDER BY CCODCLI'
            : 'SELECT CCODCLI, CNRORUC FROM E01MCLI WHERE CTIPO <> :natural ORDER BY CCODCLI';

        return array_map(static fn (array $row): array => [(string) $row[0], (string) $row[1]],
            $this->connection->fetchAllNumeric($sql, ['natural' => ClientType::Natural->value]));
    }

    public function client(string $code): ?array
    {
        $row = $this->connection->fetchAssociative('SELECT CNOMBRE, CDIREC, CTELEF, CTIPO, CNRODNI, CNRORUC FROM E01MCLI WHERE CCODCLI = :code', ['code' => $code]);
        if (false === $row) {
            return null;
        }

        return ['name' => (string) $row['CNOMBRE'], 'address' => (string) $row['CDIREC'], 'phone' => (string) $row['CTELEF'],
            'natural' => ClientType::Natural->value === $row['CTIPO'], 'dni' => (string) $row['CNRODNI'], 'ruc' => (string) $row['CNRORUC']];
    }

    public function products(): array
    {
        return array_map(static fn (array $row): array => [(string) $row[0], (string) $row[1]],
            $this->connection->fetchAllNumeric("SELECT CCODPROD, CDESCRI FROM E01MPROD WHERE CTIPO = 'A' ORDER BY CCODPROD"));
    }

    public function product(string $code): ?array
    {
        $row = $this->connection->fetchNumeric(
            "SELECT P.CCODPROD, P.CDESCRI, SUM(I.nPrecio) FROM E01MPROD P
             INNER JOIN E01PPROD R ON R.CCODPROD = P.CCODPROD INNER JOIN E01MINS I ON I.cCodIns = R.CCODINS
             WHERE P.CCODPROD = :code AND P.CTIPO = 'A' GROUP BY P.CCODPROD, P.CDESCRI",
            ['code' => $code],
        );

        return false === $row ? null : [(string) $row[0], (string) $row[1], (float) $row[2]];
    }

    public function create(string $client, string $priority, string $dueDate, array $quantities, float $total, string $user): string
    {
        $this->tables->requireInnoDb('E01CPED', 'E01DPED');

        return $this->connection->transactional(function () use ($client, $priority, $dueDate, $quantities, $total, $user): string {
            // Codes that start with 9 are reserved, as in the legacy screen.
            $last = $this->connection->fetchOne("SELECT MAX(CCODPED) FROM E01CPED WHERE SUBSTR(CCODPED, 1, 1) <> '9' FOR UPDATE");
            $next = (int) $last + 1;
            if ($next > 899999) {
                throw new \DomainException('No hay códigos de pedido disponibles.');
            }
            $code = str_pad((string) $next, 6, '0', STR_PAD_LEFT);
            $this->connection->insert('E01CPED', ['CCODPED' => $code, 'CCODCLI' => $client, 'NTOTAL' => (int) round($total),
                'DFECLIS' => $dueDate, 'CPRIORI' => $priority, 'CESTADOPL' => 'I', 'CESTADOPR' => 'P', 'CCODUSU' => $user]);
            foreach ($quantities as $product => $quantity) {
                $this->connection->insert('E01DPED', ['CCODPED' => $code, 'CCODPROD' => (string) $product, 'NCANT' => $quantity,
                    'CESTADOPL' => 'I', 'CESTADOPR' => 'P', 'CPROCESO' => 'I', 'CCODUSU' => $user]);
            }

            return $code;
        });
    }

    public function pendingOrders(): array
    {
        return array_map(
            static fn (array $row): array => array_map(static fn (mixed $value): string => (string) $value, $row),
            $this->connection->fetchAllNumeric("SELECT CCODPED, CCODCLI, CNOMBRE, CDESPL, CDESPRIO, DFECLIS FROM V_E01CPED WHERE CESTADOPL = 'I' ORDER BY CCODPED"),
        );
    }

    public function order(string $code): ?array
    {
        $client = $this->connection->fetchOne('SELECT CCODCLI FROM E01CPED WHERE CCODPED = :code', ['code' => $code]);
        if (false === $client) {
            return null;
        }
        $lines = array_map(
            static fn (array $row): array => [(string) $row[0], (string) $row[1], (int) $row[2]],
            $this->connection->fetchAllNumeric(
                'SELECT A.CCODPROD, B.CDESCRI, A.NCANT FROM E01DPED A INNER JOIN E01MPROD B ON A.CCODPROD = B.CCODPROD WHERE A.CCODPED = :code ORDER BY A.CCODPROD',
                ['code' => $code],
            ),
        );

        return ['code' => $code, 'client' => (string) $client, 'lines' => $lines];
    }
}
