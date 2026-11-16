<?php

declare(strict_types=1);

namespace App\Finance;

use App\Repository\PurchaseRepository;
use App\Security\ModulePermission;

/**
 * Registers supplier invoices for supplies (ERP5010). The legacy screen showed
 * "GRABACION CONFORME" without saving. The invoices saved here appear in the
 * payable screen (ERP7010).
 */
final class PurchaseService
{
    private const PERMISSION = 'ERP5010';

    public function __construct(private readonly PurchaseRepository $purchases, private readonly ModulePermission $permissions)
    {
    }

    /** @return array{suppliers: list<array{0: string, 1: string}>, supplies: list<array{0: string, 1: string}>} */
    public function options(string $user): array
    {
        $this->permissions->assertGranted($user, self::PERMISSION);

        return ['suppliers' => $this->purchases->suppliers(), 'supplies' => $this->purchases->supplies()];
    }

    /** @return array{0: string, 1: string, 2: string, 3: string} */
    public function supply(string $user, string $code): array
    {
        $this->permissions->assertGranted($user, self::PERMISSION);
        if ('' === $code) {
            throw new \DomainException('Seleccione un insumo.');
        }

        return $this->purchases->supply($code) ?? throw new \DomainException('El insumo no existe.');
    }

    /** @param array<string, string> $quantities quantity by supply code, as typed */
    public function create(string $user, string $supplier, array $quantities): string
    {
        $options = $this->options($user);
        if (!\in_array($supplier, array_column($options['suppliers'], 0), true)) {
            throw new \DomainException('Seleccione un proveedor.');
        }
        if ([] === $quantities) {
            throw new \DomainException('Agregue al menos un insumo.');
        }
        $known = array_column($options['supplies'], 0);
        $parsed = [];
        foreach ($quantities as $supply => $quantity) {
            if (!\in_array((string) $supply, $known, true)) {
                throw new \DomainException('El insumo '.$supply.' no existe.');
            }
            if (!ctype_digit($quantity) || (int) $quantity < 1 || (int) $quantity > 1000000) {
                throw new \DomainException('La cantidad del insumo '.$supply.' debe ser un entero positivo.');
            }
            $parsed[(string) $supply] = (int) $quantity;
        }

        return $this->purchases->create($supplier, $parsed, $user);
    }
}
