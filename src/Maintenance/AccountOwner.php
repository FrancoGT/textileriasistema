<?php

declare(strict_types=1);

namespace App\Maintenance;

/**
 * Owner of a bank account in E01PCTA. The row stores the owner code in its own
 * column and the placeholder in the other column, as the legacy screen did.
 */
enum AccountOwner: string
{
    case Supplier = '1';
    case Bank = '2';

    public const PLACEHOLDER = '999999';

    /** @return array{cCodPrv: string, cCodBan: string} */
    public function columns(string $code): array
    {
        return match ($this) {
            self::Supplier => ['cCodPrv' => $code, 'cCodBan' => self::PLACEHOLDER],
            self::Bank => ['cCodPrv' => self::PLACEHOLDER, 'cCodBan' => $code],
        };
    }

    /** Name of the form field that selects the owner. */
    public function field(): string
    {
        return self::Supplier === $this ? 'pcCodPrv' : 'pcCodBan';
    }
}
