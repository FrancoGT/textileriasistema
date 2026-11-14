<?php

declare(strict_types=1);

namespace App\Repository;

use App\Maintenance\PersonnelData;
use Doctrine\DBAL\Connection;

final class DbalPersonnelRepository implements PersonnelRepository
{
    public function __construct(private readonly Connection $connection, private readonly TransactionalTables $tables)
    {
    }

    public function choices(): array
    {
        return $this->connection->fetchAllNumeric("SELECT CCODPER, CNOMBRE FROM E01MPER WHERE SUBSTR(CCODPER, 1, 1) != '9' ORDER BY CNOMBRE");
    }

    public function find(string $code): ?array
    {
        $row = $this->connection->fetchAssociative(
            'SELECT CCODPER AS code, CNOMBRE AS name, CDIRECC AS address, CEMAIL AS email, CNRODNI AS dni, CSEXO AS sex, CESTCIV AS civil_status, CESTADO AS status FROM E01MPER WHERE CCODPER = :code',
            ['code' => $code],
        );

        return false === $row ? null : $row;
    }

    public function save(?string $code, PersonnelData $data, string $user): string
    {
        $this->tables->requireInnoDb('E01MPER');

        return $this->connection->transactional(function () use ($code, $data, $user): string {
            $values = ['CNOMBRE' => strtoupper($data->name), 'CDIRECC' => strtoupper($data->address),
                'CEMAIL' => $data->email, 'CNRODNI' => $data->dni, 'CSEXO' => $data->sex,
                'CESTCIV' => $data->civilStatus, 'CESTADO' => $data->status, 'CCODUSU' => $user];
            if (null === $code) {
                $last = $this->connection->fetchOne("SELECT CCODPER FROM E01MPER WHERE CCODPER < '900000' ORDER BY CCODPER DESC LIMIT 1 FOR UPDATE");
                $next = (int) $last + 1;
                if ($next >= 900000) {
                    throw new \DomainException('No hay códigos de personal disponibles.');
                }
                $code = str_pad((string) $next, 6, '0', STR_PAD_LEFT);
                $this->connection->insert('E01MPER', ['CCODPER' => $code] + $values);
            } else {
                $exists = $this->connection->fetchOne('SELECT CCODPER FROM E01MPER WHERE CCODPER = :code FOR UPDATE', ['code' => $code]);
                if (false === $exists) {
                    throw new \DomainException('El código de personal no existe.');
                }
                $this->connection->executeStatement(
                    'UPDATE E01MPER SET CNOMBRE = :CNOMBRE, CDIRECC = :CDIRECC, CEMAIL = :CEMAIL, CNRODNI = :CNRODNI, CSEXO = :CSEXO, CESTCIV = :CESTCIV, CESTADO = :CESTADO, CCODUSU = :CCODUSU, TMODIFI = CURRENT_TIMESTAMP WHERE CCODPER = :code',
                    $values + ['code' => $code],
                );
            }

            return $code;
        });
    }
}
