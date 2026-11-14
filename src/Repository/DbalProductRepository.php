<?php

declare(strict_types=1);

namespace App\Repository;

use Doctrine\DBAL\Connection;

final class DbalProductRepository implements ProductRepository
{
    // cTipo values of catalog table 004: "N" has no recipe, "A" has a recipe.
    private const WITHOUT_RECIPE = 'N';
    private const WITH_RECIPE = 'A';

    public function __construct(private readonly Connection $connection, private readonly TransactionalTables $tables)
    {
    }

    public function all(): array
    {
        $rows = $this->connection->fetchAllNumeric(
            "SELECT p.cCodProd, p.cDescri, COALESCE(s.cDescri, ''), COALESCE(t.cDescri, '') FROM E01MPROD p
                LEFT JOIN V_E01TTAB s ON s.cCodTab = '001' AND s.cCodigo = p.cEstado
                LEFT JOIN V_E01TTAB t ON t.cCodTab = '004' AND t.cCodigo = p.cTipo
                ORDER BY p.cCodProd",
        );

        return array_map(static fn (array $row): array => array_map('strval', $row), $rows);
    }

    public function find(string $code): ?array
    {
        $row = $this->connection->fetchAssociative(
            'SELECT cCodProd AS code, cDescri AS description, cEstado AS status FROM E01MPROD WHERE cCodProd = :code',
            ['code' => $code],
        );

        return false === $row ? null : array_map(static fn (mixed $value): string => (string) $value, $row);
    }

    public function recipe(string $code): array
    {
        return array_map('strval', $this->connection->fetchFirstColumn(
            'SELECT cCodIns FROM E01PPROD WHERE cCodProd = :code ORDER BY cCodIns',
            ['code' => $code],
        ));
    }

    public function create(string $description, string $status, string $user): string
    {
        $this->tables->requireInnoDb('E01MPROD');

        return $this->connection->transactional(function () use ($description, $status, $user): string {
            // Codes that start with 9 are reserved, as in the legacy screen.
            $last = $this->connection->fetchOne("SELECT cCodProd FROM E01MPROD WHERE cCodProd NOT LIKE '9%' ORDER BY cCodProd DESC LIMIT 1 FOR UPDATE");
            $next = (int) $last + 1;
            if ($next > 899999) {
                throw new \DomainException('No hay códigos de producto disponibles.');
            }
            $code = str_pad((string) $next, 6, '0', STR_PAD_LEFT);
            $this->connection->insert('E01MPROD', ['cCodProd' => $code, 'cDescri' => $description, 'cEstado' => $status,
                'cTipo' => self::WITHOUT_RECIPE, 'cCodUsu' => $user]);

            return $code;
        });
    }

    public function saveRecipe(string $code, string $description, array $supplies, string $user): void
    {
        $this->tables->requireInnoDb('E01MPROD', 'E01PPROD');
        $this->connection->transactional(function () use ($code, $description, $supplies, $user): void {
            if (false === $this->connection->fetchOne('SELECT cCodProd FROM E01MPROD WHERE cCodProd = :code FOR UPDATE', ['code' => $code])) {
                throw new \DomainException('El producto no existe.');
            }
            $this->connection->delete('E01PPROD', ['cCodProd' => $code]);
            foreach ($supplies as $supply) {
                $this->connection->insert('E01PPROD', ['cCodProd' => $code, 'cCodIns' => $supply, 'cEstado' => 'A', 'cCodUsu' => $user]);
            }
            $this->connection->update('E01MPROD', ['cDescri' => $description, 'cTipo' => self::WITH_RECIPE, 'cCodUsu' => $user], ['cCodProd' => $code]);
        });
    }
}
