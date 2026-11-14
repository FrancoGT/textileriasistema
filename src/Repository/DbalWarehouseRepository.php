<?php

declare(strict_types=1);

namespace App\Repository;

use App\Maintenance\WarehouseData;
use Doctrine\DBAL\Connection;

final class DbalWarehouseRepository implements WarehouseRepository
{
    public function __construct(private readonly Connection $connection, private readonly TransactionalTables $tables)
    {
    }

    public function choices(): array
    {
        return array_map(
            static fn (array $row): array => [(string) $row[0], (string) $row[1]],
            $this->connection->fetchAllNumeric('SELECT cCodAlm, cNomAlm FROM E01MALM ORDER BY cCodAlm'),
        );
    }

    public function find(string $code): ?array
    {
        $row = $this->connection->fetchAssociative(
            'SELECT cCodAlm AS code, cNomAlm AS name, cDirec AS address, cEstado AS status, cTipo AS type FROM E01MALM WHERE cCodAlm = :code',
            ['code' => $code],
        );

        return false === $row ? null : array_map(static fn (mixed $value): string => (string) $value, $row);
    }

    public function save(?string $code, WarehouseData $data, string $user): string
    {
        $this->tables->requireInnoDb('E01MALM');

        return $this->connection->transactional(function () use ($code, $data, $user): string {
            $values = ['cNomAlm' => $data->name, 'cDirec' => $data->address, 'cEstado' => $data->status, 'cTipo' => $data->type, 'cCodUsu' => $user];
            if (null === $code) {
                $last = $this->connection->fetchOne('SELECT cCodAlm FROM E01MALM ORDER BY cCodAlm DESC LIMIT 1 FOR UPDATE');
                $next = (int) $last + 1;
                if ($next > 999999) {
                    throw new \DomainException('No hay códigos de almacén disponibles.');
                }
                $code = str_pad((string) $next, 6, '0', STR_PAD_LEFT);
                $this->connection->insert('E01MALM', ['cCodAlm' => $code] + $values);

                return $code;
            }
            if (false === $this->connection->fetchOne('SELECT cCodAlm FROM E01MALM WHERE cCodAlm = :code FOR UPDATE', ['code' => $code])) {
                throw new \DomainException('El almacén no existe.');
            }
            $this->connection->update('E01MALM', $values, ['cCodAlm' => $code]);

            return $code;
        });
    }
}
