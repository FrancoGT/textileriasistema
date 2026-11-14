<?php

declare(strict_types=1);

namespace App\Repository;

use App\Maintenance\MachineData;
use Doctrine\DBAL\Connection;

final class DbalMachineRepository implements MachineRepository
{
    public function __construct(private readonly Connection $connection, private readonly TransactionalTables $tables)
    {
    }

    public function choices(): array
    {
        return array_map(
            static fn (array $row): array => [(string) $row[0], (string) $row[1]],
            $this->connection->fetchAllNumeric('SELECT cCodMaq, cDescri FROM E01MMAQ ORDER BY cCodMaq'),
        );
    }

    public function find(string $code): ?array
    {
        $row = $this->connection->fetchAssociative(
            'SELECT cCodMaq AS code, cDescri AS description, cEstado AS status, cTipo AS type FROM E01MMAQ WHERE cCodMaq = :code',
            ['code' => $code],
        );

        return false === $row ? null : array_map(static fn (mixed $value): string => (string) $value, $row);
    }

    public function save(?string $code, MachineData $data, string $user): string
    {
        $this->tables->requireInnoDb('E01MMAQ');

        return $this->connection->transactional(function () use ($code, $data, $user): string {
            $values = ['cDescri' => $data->description, 'cEstado' => $data->status, 'cTipo' => $data->type, 'cCodUsu' => $user];
            if (null === $code) {
                $last = $this->connection->fetchOne('SELECT cCodMaq FROM E01MMAQ ORDER BY cCodMaq DESC LIMIT 1 FOR UPDATE');
                $next = (int) $last + 1;
                if ($next > 999999) {
                    throw new \DomainException('No hay códigos de máquina disponibles.');
                }
                $code = str_pad((string) $next, 6, '0', STR_PAD_LEFT);
                // "L" (libre) is the initial flag written by the legacy screen.
                $this->connection->insert('E01MMAQ', ['cCodMaq' => $code, 'cFlag' => 'L'] + $values);

                return $code;
            }
            if (false === $this->connection->fetchOne('SELECT cCodMaq FROM E01MMAQ WHERE cCodMaq = :code FOR UPDATE', ['code' => $code])) {
                throw new \DomainException('La máquina no existe.');
            }
            $this->connection->update('E01MMAQ', $values, ['cCodMaq' => $code]);

            return $code;
        });
    }
}
