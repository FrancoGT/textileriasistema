<?php

declare(strict_types=1);

namespace App\Repository;

use Doctrine\DBAL\Connection;

final class TransactionalTables
{
    public function __construct(private readonly Connection $connection)
    {
    }

    public function requireInnoDb(string ...$tables): void
    {
        foreach ($tables as $table) {
            $engine = $this->connection->fetchOne(
                'SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table',
                ['table' => $table],
            );
            if ('INNODB' !== strtoupper((string) $engine)) {
                throw new \RuntimeException('La tabla '.$table.' requiere la migración a InnoDB antes de grabar.');
            }
        }
    }
}
