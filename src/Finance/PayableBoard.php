<?php

declare(strict_types=1);

namespace App\Finance;

/** Supplier invoice lists. Each value is the screen code and its menu option. */
enum PayableBoard: string
{
    case Pending = 'ERP7010';
    case Paid = 'ERP7020';

    public function canPay(): bool
    {
        return self::Pending === $this;
    }
}
