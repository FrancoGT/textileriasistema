<?php

declare(strict_types=1);

namespace App\Repository;

use App\Maintenance\AccountOwner;

interface BankAccountRepository
{
    /** @return list<array{0: string, 1: string}> */
    public function owners(AccountOwner $owner): array;

    public function add(AccountOwner $owner, string $code, string $account, string $user): void;
}
