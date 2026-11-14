<?php

declare(strict_types=1);

namespace App\Repository;

use App\Maintenance\BankData;
use Doctrine\DBAL\Connection;

final class DbalBankRepository implements BankRepository
{
    public function __construct(private readonly Connection $connection, private readonly TransactionalTables $tables)
    {
    }

    public function choices(): array
    {
        return array_map(
            static fn (array $row): array => [(string) $row[0], (string) $row[1]],
            $this->connection->fetchAllNumeric('SELECT cCodBan, cNomBan FROM E01MBAN ORDER BY cCodBan'),
        );
    }

    public function find(string $code): ?array
    {
        $row = $this->connection->fetchAssociative(
            'SELECT cCodBan AS code, cNomBan AS name, cEstado AS status, cCiudad AS city FROM E01MBAN WHERE cCodBan = :code',
            ['code' => $code],
        );

        return false === $row ? null : array_map(static fn (mixed $value): string => trim((string) $value), $row);
    }

    public function save(?string $code, BankData $data, string $user): string
    {
        $this->tables->requireInnoDb('E01MBAN');

        return $this->connection->transactional(function () use ($code, $data, $user): string {
            $values = ['cNomBan' => $data->name, 'cEstado' => $data->status, 'cCiudad' => $data->city, 'cCodUsu' => $user];
            if (null === $code) {
                // The legacy screen padded an undefined variable and produced the same code for every new bank.
                $last = $this->connection->fetchOne('SELECT cCodBan FROM E01MBAN ORDER BY cCodBan DESC LIMIT 1 FOR UPDATE');
                $next = (int) $last + 1;
                if ($next > 999999) {
                    throw new \DomainException('No hay códigos de banco disponibles.');
                }
                $code = str_pad((string) $next, 6, '0', STR_PAD_LEFT);
                $this->connection->insert('E01MBAN', ['cCodBan' => $code] + $values);

                return $code;
            }
            if (false === $this->connection->fetchOne('SELECT cCodBan FROM E01MBAN WHERE cCodBan = :code FOR UPDATE', ['code' => $code])) {
                throw new \DomainException('El banco no existe.');
            }
            $this->connection->update('E01MBAN', $values, ['cCodBan' => $code]);

            return $code;
        });
    }
}
