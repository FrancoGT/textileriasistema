<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Replaces the SQL scripts 001 to 003 that were in docs/migrations. The Symfony repositories refuse to write
 * to these tables while they use MyISAM, because MyISAM cannot roll back.
 */
final class Version20261114000001 extends AbstractMigration
{
    public const TABLES = ['E01MPER', 'E01MPRV', 'E01DPRV', 'E01MCLI', 'E01TTAB', 'E01TUSU', 'E01PPRF'];

    public function getDescription(): string
    {
        return 'Convert the tables written by the migrated screens to InnoDB';
    }

    public function isTransactional(): bool
    {
        return false;
    }

    public function up(Schema $schema): void
    {
        $engines = array_change_key_case($this->connection->fetchAllKeyValue(
            'SELECT UPPER(TABLE_NAME), ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE()',
        ), \CASE_UPPER);
        foreach (self::TABLES as $table) {
            $this->abortIf(!isset($engines[$table]), $table.' does not exist. Import the initial database first.');
            // Converting an InnoDB table again would rebuild it for nothing.
            if ('InnoDB' !== $engines[$table]) {
                $this->addSql('ALTER TABLE '.$table.' ENGINE = InnoDB');
            }
        }
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException('Going back to MyISAM removes the transactions that the application needs.');
    }
}
