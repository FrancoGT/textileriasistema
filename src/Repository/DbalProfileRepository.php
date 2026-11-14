<?php

declare(strict_types=1);

namespace App\Repository;

use Doctrine\DBAL\Connection;

final class DbalProfileRepository implements ProfileRepository
{
    public function __construct(private readonly Connection $connection, private readonly TransactionalTables $tables)
    {
    }

    public function granted(string $profile): array
    {
        $rows = $this->connection->fetchAllNumeric(
            "SELECT o.cCodOpc, o.cDescri, COALESCE(s.cDescri, '') FROM E01TOPC o
                INNER JOIN E01PPRF p ON p.cCodOpc = o.cCodOpc
                LEFT JOIN V_E01TTAB s ON s.cCodTab = '001' AND s.cCodigo = o.cEstado
                WHERE p.cPerfil = :profile ORDER BY o.cCodOpc",
            ['profile' => $profile],
        );

        return array_map(static fn (array $row): array => array_map(static fn (mixed $value): string => trim((string) $value), $row), $rows);
    }

    public function available(string $profile): array
    {
        $rows = $this->connection->fetchAllNumeric(
            "SELECT o.cCodOpc, o.cDescri FROM E01TOPC o WHERE o.cEstado = 'A'
                AND NOT EXISTS (SELECT 1 FROM E01PPRF p WHERE p.cCodOpc = o.cCodOpc AND p.cPerfil = :profile) ORDER BY o.cCodOpc",
            ['profile' => $profile],
        );

        return array_map(static fn (array $row): array => array_map(static fn (mixed $value): string => trim((string) $value), $row), $rows);
    }

    public function profileOf(string $user): ?string
    {
        $profile = $this->connection->fetchOne('SELECT cPerfil FROM E01TUSU WHERE cCodUsu = :user', ['user' => $user]);

        return false === $profile ? null : (string) $profile;
    }

    public function grant(string $profile, string $option, string $status, string $user): void
    {
        $this->tables->requireInnoDb('E01PPRF');
        $this->connection->transactional(function () use ($profile, $option, $status, $user): void {
            $serial = (int) $this->connection->fetchOne('SELECT nSerial FROM E01PPRF ORDER BY nSerial DESC LIMIT 1 FOR UPDATE') + 1;
            $exists = $this->connection->fetchOne(
                'SELECT 1 FROM E01PPRF WHERE cPerfil = :profile AND cCodOpc = :option LIMIT 1 FOR UPDATE',
                ['profile' => $profile, 'option' => $option],
            );
            if (false !== $exists) {
                throw new \DomainException('La opción ya está asignada al perfil.');
            }
            $this->connection->insert('E01PPRF', ['nSerial' => $serial, 'cCodOpc' => $option, 'cPerfil' => $profile, 'cEstado' => $status, 'cCodUsu' => $user]);
        });
    }

    public function revoke(string $profile, string $option): void
    {
        $this->tables->requireInnoDb('E01PPRF');
        if (0 === $this->connection->delete('E01PPRF', ['cPerfil' => $profile, 'cCodOpc' => $option])) {
            throw new \DomainException('La opción no está asignada al perfil.');
        }
    }
}
