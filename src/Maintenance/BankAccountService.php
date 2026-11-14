<?php

declare(strict_types=1);

namespace App\Maintenance;

use App\Repository\BankAccountRepository;
use App\Security\ModulePermission;

final class BankAccountService
{
    private const PERMISSION = 'ERP1094';

    public function __construct(private readonly BankAccountRepository $accounts, private readonly ModulePermission $permissions)
    {
    }

    public function assertGranted(string $user): void
    {
        $this->permissions->assertGranted($user, self::PERMISSION);
    }

    /** @return list<array{0: string, 1: string}> */
    public function owners(string $user, AccountOwner $owner): array
    {
        $this->assertGranted($user);

        return $this->accounts->owners($owner);
    }

    public function add(string $user, AccountOwner $owner, string $code, string $account): void
    {
        $this->assertGranted($user);
        if (!\in_array($code, array_column($this->accounts->owners($owner), 0), true)) {
            throw new \DomainException(AccountOwner::Supplier === $owner ? 'Seleccione un proveedor válido.' : 'Seleccione un banco válido.');
        }
        if (!preg_match('/^\d{14}$/D', $account)) {
            throw new \DomainException('El número de cuenta debe contener catorce dígitos.');
        }
        $this->accounts->add($owner, $code, $account, $user);
    }
}
