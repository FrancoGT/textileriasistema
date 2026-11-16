<?php

declare(strict_types=1);

namespace App\Finance;

/** Order lists of the receivable screens. Each value is the screen code and its menu option. */
enum ReceivableBoard: string
{
    case AllOrders = 'ERP6010';
    case WithInstallments = 'ERP6020';
    case Delivered = 'ERP6030';

    public function template(): string
    {
        return self::WithInstallments === $this ? 'ERP6020' : 'ERP6010';
    }
}
