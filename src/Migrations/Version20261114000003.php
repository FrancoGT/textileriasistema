<?php

declare(strict_types=1);

namespace App\Migrations;

use App\Security\UserPasswordHasher;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * The login still accepts a plain-text value and replaces it after a successful
 * login, so a database restored from an old backup keeps working.
 */
final class Version20261114000003 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Replace the plain-text passwords in E01TUSU with hashes';
    }

    public function up(Schema $schema): void
    {
        $duplicates = $this->connection->fetchFirstColumn('SELECT CCODUSU FROM E01TUSU GROUP BY CCODUSU HAVING COUNT(*) > 1');
        $this->abortIf([] !== $duplicates, 'E01TUSU has more than one row for the user codes '.implode(', ', $duplicates).'. Remove the extra rows first.');
        $hasher = new UserPasswordHasher();
        $rows = $this->connection->fetchAllNumeric("SELECT CCODUSU, CCLAVE FROM E01TUSU WHERE CCLAVE IS NOT NULL AND CCLAVE <> ''");
        foreach ($rows as [$code, $password]) {
            if (UserPasswordHasher::isPlainText((string) $password)) {
                // The plain-text value is not a query parameter: DBAL writes parameters into error messages and logs.
                $this->addSql(
                    "UPDATE E01TUSU SET CCLAVE = :hash WHERE CCODUSU = :code AND CCLAVE NOT LIKE '$2y$%'",
                    ['hash' => $hasher->hash((string) $password), 'code' => $code],
                );
            }
        }
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException('A hash cannot be turned back into the original password.');
    }
}
