<?php

declare(strict_types=1);

namespace App\Repository;

use App\Maintenance\ClientData;
use App\Maintenance\ClientType;
use Doctrine\DBAL\Connection;

final class DbalClientRepository implements ClientRepository
{
    public function __construct(private readonly Connection $connection, private readonly TransactionalTables $tables)
    {
    }

    public function choices(ClientType $type): array
    {
        return array_map(
            static fn (array $row): array => [(string) $row[0], (string) $row[1]],
            $this->connection->fetchAllNumeric('SELECT cCodCli, cNombre FROM E01MCLI WHERE cTipo = :type ORDER BY cCodCli', ['type' => $type->value]),
        );
    }

    public function find(string $code): ?array
    {
        $row = $this->connection->fetchAssociative(
            'SELECT cCodCli AS code, cTipo AS type, cNombre AS name, cNroDni AS dni, cNroRuc AS ruc, cDirec AS address, cTelef AS phone,
                cEstado AS status, cCodCiiu AS ciiu, cSiglas AS acronym, cPriori AS priority FROM E01MCLI WHERE cCodCli = :code',
            ['code' => $code],
        );

        return false === $row ? null : array_map(static fn (mixed $value): string => trim((string) $value), $row);
    }

    public function save(?string $code, ClientData $data, string $user): string
    {
        $this->tables->requireInnoDb('E01MCLI');

        return $this->connection->transactional(function () use ($code, $data, $user): string {
            $values = ['cDirec' => $data->address, 'cTelef' => $data->phone, 'cEstado' => $data->status,
                'cCodCiiu' => $data->ciiu, 'cSiglas' => $data->acronym, 'cPriori' => $data->priority, 'cCodUsu' => $user];
            if (null === $code) {
                // The legacy screen used COUNT(*) + 1, which reuses codes after a deletion.
                $last = $this->connection->fetchOne('SELECT cCodCli FROM E01MCLI ORDER BY cCodCli DESC LIMIT 1 FOR UPDATE');
                $next = (int) $last + 1;
                if ($next > 999999) {
                    throw new \DomainException('No hay códigos de cliente disponibles.');
                }
                $code = str_pad((string) $next, 6, '0', STR_PAD_LEFT);
                $this->connection->insert('E01MCLI', ['cCodCli' => $code, 'cTipo' => $data->type->value, 'cNombre' => $data->name,
                    'cNroDni' => $data->dni, 'cNroRuc' => $data->ruc] + $values);

                return $code;
            }
            if (false === $this->connection->fetchOne('SELECT cCodCli FROM E01MCLI WHERE cCodCli = :code FOR UPDATE', ['code' => $code])) {
                throw new \DomainException('El cliente no existe.');
            }
            $this->connection->update('E01MCLI', $values, ['cCodCli' => $code]);

            return $code;
        });
    }
}
