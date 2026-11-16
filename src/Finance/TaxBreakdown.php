<?php

declare(strict_types=1);

namespace App\Finance;

use Symfony\Component\DependencyInjection\Attribute\Exclude;

/** Splits a total that includes IGV (18 %) into the sale value and the tax. */
#[Exclude]
final readonly class TaxBreakdown
{
    private const IGV = 1.18;

    private function __construct(public float $saleValue, public float $tax, public float $total)
    {
    }

    public static function fromTotal(float $total): self
    {
        $saleValue = round($total / self::IGV, 2);

        return new self($saleValue, round($total - $saleValue, 2), $total);
    }
}
