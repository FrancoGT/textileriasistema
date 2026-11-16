<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * The legacy ERP9010 script had its permission check commented out, and the
 * option did not exist, so every user could mark orders as delivered. The
 * Symfony screen checks the option. Profile A has every other option, so it
 * receives this one. Grant other profiles from the profile screen (ERP1020).
 */
final class Version20261115000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create the ERP9010 dispatch option and grant it to profile A';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            "INSERT INTO E01TOPC (CCODOPC, CDESCRI, CESTADO, CCODUSU) SELECT 'ERP9010', 'ENTREGA EN DESPACHO', 'A', '9999' FROM DUAL
             WHERE NOT EXISTS (SELECT 1 FROM E01TOPC WHERE CCODOPC = 'ERP9010')",
        );
        $this->addSql(
            "INSERT INTO E01PPRF (NSERIAL, CCODOPC, CPERFIL, CESTADO, CCODUSU)
             SELECT COALESCE(MAX(NSERIAL), 0) + 1, 'ERP9010', 'A', 'A', '9999' FROM E01PPRF
             HAVING NOT EXISTS (SELECT 1 FROM E01PPRF WHERE CCODOPC = 'ERP9010' AND CPERFIL = 'A')",
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql("DELETE FROM E01PPRF WHERE CCODOPC = 'ERP9010'");
        $this->addSql("DELETE FROM E01TOPC WHERE CCODOPC = 'ERP9010'");
    }
}
