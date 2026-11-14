<?php

declare(strict_types=1);

namespace App\Repository;

use App\Maintenance\SupplierData;
use Doctrine\DBAL\Connection;

final class DbalSupplierRepository implements SupplierRepository
{
    public function __construct(private readonly Connection $connection, private readonly TransactionalTables $tables)
    {
    }

    public function all(): array
    {
        return $this->connection->fetchAllNumeric("SELECT a.cCodPrv, a.cNroRuc, a.cNombre, b.cDescri FROM E01MPRV a INNER JOIN V_E01TTAB b ON a.cEstado = b.cCodigo WHERE b.cCodTab = '001' ORDER BY a.cCodPrv");
    }

    public function find(string $code): ?array
    {
        $row = $this->connection->fetchAssociative(
            'SELECT cCodPrv AS code, cNombre AS name, cDirecc AS address, cNroDni AS dni, cNroRuc AS ruc, cTipPer AS person_type, dFecNac AS date, mDatos AS notes FROM E01MPRV WHERE cCodPrv = :code',
            ['code' => $code],
        );
        if (false === $row) {
            return null;
        }
        $row['production_lines'] = $this->connection->fetchFirstColumn('SELECT cTipArs FROM E01DPRV WHERE cCodPrv = :code ORDER BY cTipArs', ['code' => $code]);

        return $row;
    }

    public function save(?string $code, SupplierData $data, string $user): string
    {
        $this->tables->requireInnoDb('E01MPRV', 'E01DPRV');

        return $this->connection->transactional(function () use ($code, $data, $user): string {
            if (null === $code) {
                $last = $this->connection->fetchOne('SELECT cCodPrv FROM E01MPRV ORDER BY cCodPrv DESC LIMIT 1 FOR UPDATE');
                $next = (int) $last + 1;
                if ($next > 999999) {
                    throw new \DomainException('No hay códigos de proveedor disponibles.');
                }
                $code = str_pad((string) $next, 6, '0', STR_PAD_LEFT);
                $natural = 'a' === strtolower($data->personType);
                $this->connection->insert('E01MPRV', ['cCodPrv' => $code, 'cNombre' => strtoupper($data->name),
                    'cDirecc' => strtoupper($data->address), 'cNroDni' => $natural ? $data->dni : '99999999',
                    'cNroRuc' => $natural ? '99999999999' : $data->ruc, 'cTipPer' => $data->personType,
                    'dFecNac' => $data->date, 'cEstado' => 'A', 'mDatos' => $data->notes, 'cCodUsu' => $user]);
            } else {
                if (false === $this->connection->fetchOne('SELECT cCodPrv FROM E01MPRV WHERE cCodPrv = :code FOR UPDATE', ['code' => $code])) {
                    throw new \DomainException('El proveedor no existe.');
                }
                $this->connection->update('E01MPRV', ['cDirecc' => strtoupper($data->address), 'mDatos' => $data->notes, 'cCodUsu' => $user], ['cCodPrv' => $code]);
            }
            $this->connection->delete('E01DPRV', ['cCodPrv' => $code]);
            foreach ($data->productionLines as $line) {
                $this->connection->insert('E01DPRV', ['cCodPrv' => $code, 'cTipArs' => $line, 'cCodUsu' => $user]);
            }

            return $code;
        });
    }
}
