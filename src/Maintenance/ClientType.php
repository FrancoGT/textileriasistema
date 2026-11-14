<?php

declare(strict_types=1);

namespace App\Maintenance;

/**
 * Person types of catalog table 098. A natural person is identified by DNI.
 * A legal entity is identified by RUC, CIIU code and acronym. The legacy
 * screen stores placeholder values in the columns that do not apply.
 */
enum ClientType: string
{
    case Natural = 'A';
    case NonProfit = 'B';
    case Company = 'C';

    public const DNI_PLACEHOLDER = '99999999';
    public const RUC_PLACEHOLDER = '99999999999';
    public const CIIU_PLACEHOLDER = '9999';
    public const ACRONYM_PLACEHOLDER = 'S/S';

    public function isNatural(): bool
    {
        return self::Natural === $this;
    }

    /** Template state that lists the clients of this type. */
    public function listBehavior(): string
    {
        return match ($this) {
            self::Natural => '3',
            self::NonProfit => '4',
            self::Company => '5',
        };
    }
}
