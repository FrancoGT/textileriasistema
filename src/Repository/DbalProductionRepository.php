<?php

declare(strict_types=1);

namespace App\Repository;

use App\Production\ProductionStage;
use Doctrine\DBAL\Connection;

final class DbalProductionRepository implements ProductionRepository
{
    public function __construct(private readonly Connection $connection, private readonly TransactionalTables $tables)
    {
    }

    public function plannedMachines(string $order, string $product): array
    {
        return $this->pairs(
            "SELECT A.CCODMAQ, B.CDESCRI FROM E01MAQ A INNER JOIN E01MMAQ B ON A.CCODMAQ = B.CCODMAQ
             WHERE A.CESTADOOP = 'L' AND A.CESTADO = 'A' AND A.CCODPED = :order AND A.CCODPROD = :product ORDER BY A.CCODMAQ",
            ['order' => $order, 'product' => $product],
        );
    }

    public function plannedStaff(string $order, string $product): array
    {
        return $this->pairs(
            "SELECT A.CCODPER, B.CNOMBRE FROM E01PER A INNER JOIN E01MPER B ON A.CCODPER = B.CCODPER
             WHERE A.CESTADOOP = 'L' AND A.CESTADO = 'A' AND A.CCODPED = :order AND A.CCODPROD = :product ORDER BY A.CCODPER",
            ['order' => $order, 'product' => $product],
        );
    }

    public function assign(string $order, string $product, ProductionStage $from, string $machine, string $person, string $user): void
    {
        $this->tables->requireInnoDb('E01TRABAJO', 'E01MAQ', 'E01PER', 'E01DPED');
        $this->connection->transactional(function () use ($order, $product, $from, $machine, $person, $user): void {
            $line = ['order' => $order, 'product' => $product];
            $stage = $this->connection->fetchOne('SELECT CPROCESO FROM E01DPED WHERE CCODPED = :order AND CCODPROD = :product FOR UPDATE', $line);
            if ($from->value !== $stage) {
                throw new \DomainException('El producto cambió de proceso. Vuelva a abrir el pedido.');
            }
            $this->occupy('E01MAQ', 'CCODMAQ', $machine, $line, 'La máquina ya no está libre para este producto.');
            $this->occupy('E01PER', 'CCODPER', $person, $line, 'La persona ya no está libre para este producto.');
            $next = $from->next();
            $this->connection->insert('E01TRABAJO', ['CCODPED' => $order, 'CCODPROD' => $product, 'CCODMAQ' => $machine,
                'CCODPER' => $person, 'CESTADO' => 'A', 'CPROCESO' => $next->value, 'CCODUSU' => $user]);
            $this->connection->update('E01DPED', ['CPROCESO' => $next->value, 'CESTADOPR' => 'P'], ['CCODPED' => $order, 'CCODPROD' => $product]);
        });
    }

    public function activeJob(string $person): ?array
    {
        // The legacy screen showed the first machine of E01MMAQ instead of the machine of the job.
        $row = $this->connection->fetchAssociative(
            "SELECT T.CCODPED, T.CCODPROD, T.CCODMAQ, T.CCODPER, P.CNOMBRE, R.CDESCRI AS PRODUCTO, D.NCANT, C.cDescri AS PROCESO,
                    M.CDESCRI AS MAQUINA, Y.cDescri AS TIPO
             FROM E01TRABAJO T
             LEFT JOIN E01MPER P ON P.CCODPER = T.CCODPER
             LEFT JOIN E01MPROD R ON R.CCODPROD = T.CCODPROD
             LEFT JOIN E01DPED D ON D.CCODPED = T.CCODPED AND D.CCODPROD = T.CCODPROD
             LEFT JOIN V_E01TTAB C ON C.cCodigo = T.CPROCESO AND C.cCodTab = '012'
             LEFT JOIN E01MMAQ M ON M.CCODMAQ = T.CCODMAQ
             LEFT JOIN V_E01TTAB Y ON Y.cCodigo = M.CTIPO AND Y.cCodTab = '005'
             WHERE T.CCODPER = :person AND T.CESTADO = 'A'
             ORDER BY T.TMODIFI LIMIT 1",
            ['person' => $person],
        );
        if (false === $row) {
            return null;
        }

        return ['order' => (string) $row['CCODPED'], 'product' => (string) $row['CCODPROD'], 'machine' => (string) $row['CCODMAQ'],
            'person' => (string) $row['CCODPER'], 'name' => (string) $row['CNOMBRE'], 'description' => (string) $row['PRODUCTO'],
            'quantity' => (string) $row['NCANT'], 'process' => (string) $row['PROCESO'], 'machineDescription' => (string) $row['MAQUINA'],
            'machineType' => (string) $row['TIPO']];
    }

    public function finish(string $order, string $product, string $machine, string $person): void
    {
        $this->tables->requireInnoDb('E01TRABAJO', 'E01MAQ', 'E01PER', 'E01DPED', 'E01CPED', 'E01MMAQ', 'E01MPER');
        $this->connection->transactional(function () use ($order, $product, $machine, $person): void {
            $job = ['CCODPED' => $order, 'CCODPROD' => $product, 'CCODMAQ' => $machine, 'CCODPER' => $person];
            $active = $this->connection->fetchOne(
                "SELECT COUNT(*) FROM E01TRABAJO WHERE CCODPED = :CCODPED AND CCODPROD = :CCODPROD AND CCODMAQ = :CCODMAQ AND CCODPER = :CCODPER AND CESTADO = 'A' FOR UPDATE",
                $job,
            );
            if (0 === (int) $active) {
                throw new \DomainException('El trabajo ya fue terminado.');
            }
            $line = ['CCODPED' => $order, 'CCODPROD' => $product, 'CESTADO' => 'A'];
            $this->connection->update('E01MAQ', ['CESTADOOP' => 'L'], $line + ['CCODMAQ' => $machine]);
            $this->connection->update('E01PER', ['CESTADOOP' => 'L'], $line + ['CCODPER' => $person]);
            $this->connection->update('E01TRABAJO', ['CESTADO' => 'I'], $job);
            $this->connection->update('E01DPED', ['CESTADOPR' => 'E'], ['CCODPED' => $order, 'CCODPROD' => $product]);
            $unfinished = $this->connection->fetchOne(
                "SELECT COUNT(*) FROM E01DPED WHERE CCODPED = :order AND (CESTADOPR <> 'E' OR CPROCESO <> 'E')",
                ['order' => $order],
            );
            if (0 !== (int) $unfinished) {
                return;
            }
            $this->connection->update('E01CPED', ['CESTADOPR' => 'E'], ['CCODPED' => $order]);
            $this->connection->executeStatement(
                "UPDATE E01MPER SET CESTADOOP = 'L' WHERE CCODPER IN (SELECT CCODPER FROM E01PER WHERE CCODPED = :order AND CESTADO = 'A')",
                ['order' => $order],
            );
            $this->connection->executeStatement(
                "UPDATE E01MMAQ SET CESTADOOP = 'L' WHERE CCODMAQ IN (SELECT CCODMAQ FROM E01MAQ WHERE CCODPED = :order AND CESTADO = 'A')",
                ['order' => $order],
            );
            $this->connection->update('E01PER', ['CESTADO' => 'I'], ['CCODPED' => $order]);
            $this->connection->update('E01MAQ', ['CESTADO' => 'I'], ['CCODPED' => $order]);
        });
    }

    /** @param array{order: string, product: string} $line */
    private function occupy(string $table, string $column, string $code, array $line, string $message): void
    {
        $free = $this->connection->fetchOne(
            "SELECT $column FROM $table WHERE $column = :code AND CCODPED = :order AND CCODPROD = :product AND CESTADO = 'A' AND CESTADOOP = 'L' LIMIT 1 FOR UPDATE",
            ['code' => $code] + $line,
        );
        if (false === $free) {
            throw new \DomainException($message);
        }
        $this->connection->executeStatement(
            "UPDATE $table SET CESTADOOP = 'O' WHERE $column = :code AND CCODPED = :order AND CCODPROD = :product AND CESTADO = 'A'",
            ['code' => $code] + $line,
        );
    }

    /**
     * @param array<string, string> $parameters
     *
     * @return list<array{0: string, 1: string}>
     */
    private function pairs(string $sql, array $parameters): array
    {
        return array_map(static fn (array $row): array => [(string) $row[0], (string) $row[1]], $this->connection->fetchAllNumeric($sql, $parameters));
    }
}
