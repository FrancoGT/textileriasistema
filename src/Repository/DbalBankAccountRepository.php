<?php

declare(strict_types=1);

namespace App\Repository;

use App\Maintenance\AccountOwner;
use Doctrine\DBAL\Connection;

final class DbalBankAccountRepository implements BankAccountRepository
{
    public function __construct(private readonly Connection $connection, private readonly TransactionalTables $tables)
    {
    }

    public function owners(AccountOwner $owner): array
    {
        $sql = match ($owner) {
            AccountOwner::Supplier => 'SELECT cCodPrv, cNombre FROM E01MPRV ORDER BY cNombre',
            AccountOwner::Bank => 'SELECT cCodBan, cNomBan FROM E01MBAN ORDER BY cNomBan',
        };

        return array_map(static fn (array $row): array => [(string) $row[0], (string) $row[1]], $this->connection->fetchAllNumeric($sql));
    }

    public function add(AccountOwner $owner, string $code, string $account, string $user): void
    {
        $this->tables->requireInnoDb('E01PCTA');
        $this->connection->transactional(function () use ($owner, $code, $account, $user): void {
            $columns = $owner->columns($code);
            $exists = $this->connection->fetchOne(
                'SELECT 1 FROM E01PCTA WHERE cCodPrv = :supplier AND cCodBan = :bank AND nCuenta = :account LIMIT 1 FOR UPDATE',
                ['supplier' => $columns['cCodPrv'], 'bank' => $columns['cCodBan'], 'account' => $account],
            );
            if (false !== $exists) {
                throw new \DomainException('La cuenta ya está registrada.');
            }
            $this->connection->insert('E01PCTA', $columns + ['nCuenta' => $account, 'cCodUsu' => $user]);
        });
    }
}
