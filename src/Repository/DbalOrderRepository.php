<?php

declare(strict_types=1);

namespace App\Repository;

use App\Production\OrderFilter;
use App\Production\ProductionStage;
use Doctrine\DBAL\Connection;

final class DbalOrderRepository implements OrderRepository
{
    public function __construct(private readonly Connection $connection)
    {
    }

    public function orders(OrderFilter $filter): array
    {
        $status = $filter->showsProductionStatus() ? 'CDESPR' : 'CDESPL';
        $where = match ($filter) {
            OrderFilter::PlanningPending => "CESTADOPL = 'I'",
            OrderFilter::PlanningDone, OrderFilter::ProductionAll => "CESTADOPL = 'C'",
            OrderFilter::PlanningAll => '1 = 1',
            OrderFilter::ProductionPending => "CESTADOPL = 'C' AND CESTADOPR = 'P'",
            OrderFilter::ProductionDone => "CESTADOPL = 'C' AND CESTADOPR = 'E'",
        };

        return $this->rows("SELECT CCODPED, CCODCLI, CNOMBRE, $status, CDESPRIO, DFECLIS FROM V_E01CPED WHERE $where ORDER BY CCODPED");
    }

    public function planningLines(string $order): array
    {
        return $this->rows('SELECT CCODPED, CCODPROD, CDESCRI, NCANT, CDESPL FROM V_E01DPED WHERE CCODPED = :order ORDER BY CCODPROD', ['order' => $order]);
    }

    public function productionLines(string $order): array
    {
        return $this->rows('SELECT CCODPED, CCODPROD, CDESCRI, NCANT, CDESPR, CDESPROC FROM V_E01DPED WHERE CCODPED = :order ORDER BY CCODPROD', ['order' => $order]);
    }

    public function stageLines(ProductionStage $stage): array
    {
        return $this->rows('SELECT CCODPED, CCODPROD, CDESCRI, NCANT, CDESPR, CDESPROC FROM V_E01DPED WHERE CPROCESO = :stage ORDER BY CCODPED, CCODPROD', ['stage' => $stage->value]);
    }

    public function line(string $order, string $product): ?array
    {
        $row = $this->connection->fetchAssociative(
            'SELECT CDESCRI, NCANT, CESTADOPL, CESTADOPR, CPROCESO, CDESPROC FROM V_E01DPED WHERE CCODPED = :order AND CCODPROD = :product',
            ['order' => $order, 'product' => $product],
        );
        if (false === $row) {
            return null;
        }

        return ['description' => (string) $row['CDESCRI'], 'quantity' => (string) $row['NCANT'], 'planning' => (string) $row['CESTADOPL'],
            'production' => (string) $row['CESTADOPR'], 'stage' => ProductionStage::from((string) $row['CPROCESO']), 'process' => (string) $row['CDESPROC']];
    }

    /**
     * @param array<string, string> $parameters
     *
     * @return list<list<string>>
     */
    private function rows(string $sql, array $parameters = []): array
    {
        return array_map(
            static fn (array $row): array => array_map(static fn (mixed $value): string => (string) $value, $row),
            $this->connection->fetchAllNumeric($sql, $parameters),
        );
    }
}
