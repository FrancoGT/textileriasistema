<?php

declare(strict_types=1);

namespace App\Repository;

use Doctrine\DBAL\Connection;

final class ModulePermissionRepository
{
    public function __construct(private readonly Connection $connection)
    {
    }

    public function allows(string $user, string $option): bool
    {
        return false !== $this->connection->fetchOne(
            "SELECT p.cCodOpc FROM E01PPRF p INNER JOIN E01TUSU u ON u.cPerfil = p.cPerfil WHERE u.cCodUsu = :user AND p.cCodOpc = :option AND p.cEstado = 'A'",
            ['user' => $user, 'option' => $option],
        );
    }
}
