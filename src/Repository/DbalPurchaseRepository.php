<?php

declare(strict_types=1);

namespace App\Repository;

use Doctrine\DBAL\Connection;

final class DbalPurchaseRepository implements PurchaseRepository
{
    public function __construct(private readonly Connection $connection, private readonly TransactionalTables $tables)
    {
    }

    public function suppliers(): array
    {
        return $this->pairs('SELECT cCodPrv, cNombre FROM E01MPRV ORDER BY cCodPrv');
    }

    public function supplies(): array
    {
        return $this->pairs('SELECT cCodIns, cDescri FROM E01MINS ORDER BY cCodIns');
    }

    public function supply(string $code): ?array
    {
        $row = $this->connection->fetchNumeric(
            "SELECT A.cCodIns, A.cDescri, B.cDescri, A.nPrecio FROM E01MINS A
             LEFT JOIN V_E01TTAB B ON B.cCodigo = A.cUniMed AND B.cCodTab = '007' WHERE A.cCodIns = :code",
            ['code' => $code],
        );

        return false === $row ? null : [(string) $row[0], (string) $row[1], (string) $row[2], (string) $row[3]];
    }

    public function create(string $supplier, array $quantities, string $user): string
    {
        $this->tables->requireInnoDb('E01CCOM', 'E02DCOM');

        return $this->connection->transactional(function () use ($supplier, $quantities, $user): string {
            $last = $this->connection->fetchOne('SELECT cCodCom FROM E01CCOM ORDER BY cCodCom DESC LIMIT 1 FOR UPDATE');
            $next = (int) $last + 1;
            if ($next > 999999) {
                throw new \DomainException('No hay códigos de factura disponibles.');
            }
            $code = str_pad((string) $next, 6, '0', STR_PAD_LEFT);
            // cCodBan has no default value. The payment screen fills it in.
            $this->connection->insert('E01CCOM', ['cCodCom' => $code, 'cCodPrv' => $supplier, 'cEstado' => 'A', 'CCODUSU' => $user,
                'dFecLis' => date('Y-m-d'), 'cCodBan' => '']);
            foreach ($quantities as $supply => $quantity) {
                $this->connection->insert('E02DCOM', ['CCODCOM' => $code, 'CCODINS' => (string) $supply, 'NCANT' => $quantity, 'CCODUSU' => $user]);
            }

            return $code;
        });
    }

    /** @return list<array{0: string, 1: string}> */
    private function pairs(string $sql): array
    {
        return array_map(static fn (array $row): array => [(string) $row[0], (string) $row[1]], $this->connection->fetchAllNumeric($sql));
    }
}
