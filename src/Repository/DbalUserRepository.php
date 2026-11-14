<?php

declare(strict_types=1);

namespace App\Repository;

use App\Security\UserPasswordHasher;
use Doctrine\DBAL\Connection;

final class DbalUserRepository implements UserRepository
{
    public const ADMINISTRATOR = '9999';

    public function __construct(
        private readonly Connection $connection,
        private readonly TransactionalTables $tables,
        private readonly CredentialRepository $credentials,
        private readonly UserPasswordHasher $hasher,
    )
    {
    }

    public function all(): array
    {
        $rows = $this->connection->fetchAllNumeric(
            "SELECT u.cCodUsu, p.cNombre, COALESCE(t.cDescri, '') FROM E01TUSU u
                INNER JOIN E01MPER p ON p.cCodPer = u.cCodPer
                LEFT JOIN V_E01TTAB t ON t.cCodTab = '049' AND t.cCodigo = u.cPerfil
                WHERE u.cCodUsu <> :administrator ORDER BY p.cNombre",
            ['administrator' => self::ADMINISTRATOR],
        );

        return array_map(static fn (array $row): array => array_map('strval', $row), $rows);
    }

    public function find(string $code): ?array
    {
        $row = $this->connection->fetchAssociative(
            'SELECT u.cCodUsu AS code, p.cNombre AS name, u.cEstado AS status, u.dFecFin AS end_date, u.cPerfil AS profile
                FROM E01TUSU u INNER JOIN E01MPER p ON p.cCodPer = u.cCodPer WHERE u.cCodUsu = :code',
            ['code' => $code],
        );

        return false === $row ? null : array_map(static fn (mixed $value): string => (string) $value, $row);
    }

    public function candidates(): array
    {
        $rows = $this->connection->fetchAllNumeric(
            "SELECT p.cCodPer, p.cNombre FROM E01MPER p WHERE p.cEstado = 'A'
                AND NOT EXISTS (SELECT 1 FROM E01TUSU u WHERE u.cCodPer = p.cCodPer) ORDER BY p.cNombre",
        );

        return array_map(static fn (array $row): array => array_map('strval', $row), $rows);
    }

    public function create(string $personnel, string $profile, string $user): string
    {
        $this->tables->requireInnoDb('E01TUSU', 'E01MPER');
        if (!$this->credentials->canStoreHashes()) {
            throw new \DomainException('No se pueden crear usuarios hasta aplicar las migraciones de base de datos.');
        }

        return $this->connection->transactional(function () use ($personnel, $profile, $user): string {
            $dni = $this->connection->fetchOne(
                "SELECT cNroDni FROM E01MPER WHERE cCodPer = :personnel AND cEstado = 'A' FOR UPDATE",
                ['personnel' => $personnel],
            );
            if (false === $dni) {
                throw new \DomainException('Seleccione un trabajador activo.');
            }
            // The user code is the last four digits of the personnel code, as in the legacy screen.
            $code = substr($personnel, 2, 4);
            $taken = $this->connection->fetchOne(
                'SELECT cCodUsu FROM E01TUSU WHERE cCodUsu = :code OR cCodPer = :personnel LIMIT 1 FOR UPDATE',
                ['code' => $code, 'personnel' => $personnel],
            );
            if (false !== $taken) {
                throw new \DomainException('El trabajador ya tiene usuario o el código '.$code.' está en uso.');
            }
            // The initial password is the DNI. The login asks for a new password while dFecFin is NULL.
            $this->connection->insert('E01TUSU', ['cCodUsu' => $code, 'cCodPer' => $personnel, 'cEstado' => 'A',
                'cClave' => $this->hasher->hash(trim((string) $dni)), 'cPerfil' => $profile, 'cUsuCod' => $user]);

            return $code;
        });
    }

    public function update(string $code, string $status, string $profile, string $endDate, string $user): void
    {
        $this->tables->requireInnoDb('E01TUSU');
        $this->connection->transactional(function () use ($code, $status, $profile, $endDate, $user): void {
            if (false === $this->connection->fetchOne('SELECT cCodUsu FROM E01TUSU WHERE cCodUsu = :code FOR UPDATE', ['code' => $code])) {
                throw new \DomainException('El usuario no existe.');
            }
            $this->connection->update('E01TUSU', ['cEstado' => $status, 'cPerfil' => $profile, 'dFecFin' => $endDate, 'cUsuCod' => $user], ['cCodUsu' => $code]);
        });
    }
}
