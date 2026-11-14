<?php

declare(strict_types=1);

namespace App\Migrations;

use App\Repository\DbalCredentialRepository;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261114000002 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Widen E01TUSU.cClave to hold password hashes';
    }

    public function isTransactional(): bool
    {
        return false;
    }

    public function up(Schema $schema): void
    {
        $column = $this->connection->fetchAssociative(
            "SELECT CHARACTER_MAXIMUM_LENGTH AS length, CHARACTER_SET_NAME AS charset, COLLATION_NAME AS collation
                FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE() AND UPPER(TABLE_NAME) = 'E01TUSU' AND UPPER(COLUMN_NAME) = 'CCLAVE'",
        );
        $this->abortIf(false === $column, 'E01TUSU.cClave does not exist. Import the initial database first.');
        if ((int) $column['length'] >= DbalCredentialRepository::PASSWORD_LENGTH) {
            return;
        }
        // The character set and collation are kept, so the column only changes length.
        $this->addSql(\sprintf(
            'ALTER TABLE E01TUSU MODIFY CCLAVE VARCHAR(%d) CHARACTER SET %s COLLATE %s DEFAULT NULL',
            DbalCredentialRepository::PASSWORD_LENGTH,
            $column['charset'],
            $column['collation'],
        ));
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException('A shorter column would truncate the stored hashes.');
    }
}
