<?php

declare(strict_types=1);

namespace App\Repository;

use App\Security\UserPasswordHasher;
use Doctrine\DBAL\Connection;

final class DbalCredentialRepository implements CredentialRepository
{
    // Set by the migration Version20261114000002. The server runs without strict
    // mode, so a shorter column would truncate a hash without an error.
    public const PASSWORD_LENGTH = 255;
    private const EXPIRATION_DAYS = 45;

    private ?bool $canStoreHashes = null;

    public function __construct(private readonly Connection $connection)
    {
    }

    public function find(string $code): ?array
    {
        $row = $this->connection->fetchAssociative(
            "SELECT u.cCodUsu AS code, COALESCE(u.cClave, '') AS password, u.dFecFin AS end_date,
                    COALESCE(u.cEstado, '') AS status, COALESCE(p.cNombre, '') AS name
                FROM E01TUSU u LEFT JOIN E01MPER p ON p.cCodPer = u.cCodPer
                WHERE u.cCodUsu = :code LIMIT 1",
            ['code' => $code],
        );
        if (false === $row) {
            return null;
        }

        return ['code' => (string) $row['code'], 'password' => (string) $row['password'],
            'end_date' => null === $row['end_date'] ? null : (string) $row['end_date'],
            'status' => (string) $row['status'], 'name' => (string) $row['name']];
    }

    public function rehash(string $code, string $current, string $hash): void
    {
        if (!$this->canStoreHashes()) {
            return;
        }
        [$guard, $parameters] = $this->unchanged($current);
        $this->connection->executeStatement(
            'UPDATE E01TUSU SET cClave = :hash WHERE cCodUsu = :code AND '.$guard,
            ['hash' => $hash, 'code' => $code] + $parameters,
        );
    }

    public function changePassword(string $code, string $current, string $hash): void
    {
        if (!$this->canStoreHashes()) {
            throw new \DomainException('No se puede cambiar la clave hasta aplicar las migraciones de base de datos.');
        }
        [$guard, $parameters] = $this->unchanged($current);
        $changed = $this->connection->executeStatement(
            'UPDATE E01TUSU SET cClave = :hash, dFecFin = CURRENT_DATE + INTERVAL '.self::EXPIRATION_DAYS.' DAY
                WHERE cCodUsu = :code AND '.$guard,
            ['hash' => $hash, 'code' => $code] + $parameters,
        );
        if (0 === $changed) {
            throw new \DomainException('La clave cambió mientras se editaba. Vuelva a intentarlo.');
        }
    }

    public function canStoreHashes(): bool
    {
        return $this->canStoreHashes ??= self::PASSWORD_LENGTH <= (int) $this->connection->fetchOne(
            "SELECT CHARACTER_MAXIMUM_LENGTH FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE() AND UPPER(TABLE_NAME) = 'E01TUSU' AND UPPER(COLUMN_NAME) = 'CCLAVE'",
        );
    }

    /**
     * Condition that the stored value is still $current. A plain-text value is
     * not sent as a parameter, because DBAL writes parameters into error messages
     * and logs. Any hash written in the meantime ends the plain-text match.
     *
     * @return array{0: string, 1: array<string, string>}
     */
    private function unchanged(string $current): array
    {
        if (UserPasswordHasher::isPlainText($current)) {
            return ["cClave NOT LIKE '$2y$%'", []];
        }

        return ['BINARY cClave = :current', ['current' => $current]];
    }
}
