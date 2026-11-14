<?php

declare(strict_types=1);

namespace App\Inventory;

use App\Repository\StockRepository;
use App\Security\ModulePermission;

final class StockTransferService
{
    private const PERMISSION = 'ERP1091';
    // Origin code that the legacy screen uses for stock received from a supplier.
    public const SUPPLIER = '9999';

    public function __construct(private readonly StockRepository $stock, private readonly ModulePermission $permissions)
    {
    }

    /** @return list<array{0: string, 1: string, 2: string}> */
    public function warehouses(string $user): array
    {
        $this->permissions->assertGranted($user, self::PERMISSION);

        return $this->stock->warehouses();
    }

    /** @return array{name: string, stock: list<list<string>>} */
    public function stock(string $user, string $warehouse): array
    {
        $this->permissions->assertGranted($user, self::PERMISSION);
        $name = $this->warehouseName($warehouse) ?? throw new \DomainException('Seleccione un almacén.');

        return ['name' => $name, 'stock' => $this->stock->stock($warehouse)];
    }

    /** @return array<string, array{0: string, 1: string, 2: string, 3: string}> */
    public function supplies(string $user): array
    {
        $this->permissions->assertGranted($user, self::PERMISSION);

        return $this->stock->supplies();
    }

    /** @param array<string, string> $quantities supply code => units as typed */
    public function transfer(string $user, string $origin, string $destination, array $quantities): void
    {
        $this->permissions->assertGranted($user, self::PERMISSION);
        if (self::SUPPLIER === $destination) {
            throw new \DomainException('No se puede mover insumos al proveedor.');
        }
        if (null === $this->warehouseName($destination)) {
            throw new \DomainException('Seleccione un almacén de destino válido.');
        }
        if (self::SUPPLIER !== $origin && null === $this->warehouseName($origin)) {
            throw new \DomainException('Seleccione un origen válido.');
        }
        if ($origin === $destination) {
            throw new \DomainException('No se puede mover insumos al mismo almacén.');
        }
        if ([] === $quantities) {
            throw new \DomainException('Agregue al menos un insumo.');
        }
        $supplies = $this->stock->supplies();
        $units = [];
        foreach ($quantities as $supply => $quantity) {
            if (!isset($supplies[$supply])) {
                throw new \DomainException('El insumo '.$supply.' no existe.');
            }
            // E01PALM.nCant is DECIMAL(10,0).
            if (!preg_match('/^[1-9]\d{0,8}$/D', trim($quantity))) {
                throw new \DomainException('La cantidad del insumo '.$supply.' debe ser un número entero mayor que cero.');
            }
            $units[$supply] = (int) $quantity;
        }
        $this->stock->transfer(self::SUPPLIER === $origin ? null : $origin, $destination, $units, $user);
    }

    private function warehouseName(string $code): ?string
    {
        foreach ($this->stock->warehouses() as $warehouse) {
            if ($warehouse[0] === $code) {
                return $warehouse[1];
            }
        }

        return null;
    }
}
