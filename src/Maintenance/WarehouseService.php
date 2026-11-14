<?php

declare(strict_types=1);

namespace App\Maintenance;

use App\Repository\CatalogRepository;
use App\Repository\WarehouseRepository;
use App\Security\ModulePermission;

final class WarehouseService
{
    private const PERMISSION = 'ERP1090';

    public function __construct(
        private readonly WarehouseRepository $warehouses,
        private readonly CatalogRepository $catalogs,
        private readonly ModulePermission $permissions,
    ) {
    }

    /** @return list<array{0: string, 1: string}> */
    public function choices(string $user): array
    {
        $this->permissions->assertGranted($user, self::PERMISSION);

        return $this->warehouses->choices();
    }

    /** @return array{statuses: list<array{0: string, 1: string}>, types: list<array{0: string, 1: string}>} */
    public function options(string $user): array
    {
        $this->permissions->assertGranted($user, self::PERMISSION);

        return ['statuses' => $this->catalogs->choices('001', true), 'types' => $this->catalogs->choices('003', true)];
    }

    public function find(string $user, string $code): array
    {
        $this->permissions->assertGranted($user, self::PERMISSION);

        return $this->warehouses->find($code) ?? throw new \DomainException('El almacén no existe.');
    }

    public function save(string $user, ?string $code, WarehouseData $data): string
    {
        $options = $this->options($user);
        $name = mb_strtoupper(trim($data->name));
        $address = mb_strtoupper(trim($data->address));
        if ('' === $name || mb_strlen($name) > 100) {
            throw new \DomainException('El nombre es obligatorio y admite hasta 100 caracteres.');
        }
        if ('' === $address || mb_strlen($address) > 100) {
            throw new \DomainException('La dirección es obligatoria y admite hasta 100 caracteres.');
        }
        if (!\in_array($data->status, array_column($options['statuses'], 0), true)) {
            throw new \DomainException('Seleccione un estado válido.');
        }
        if (!\in_array($data->type, array_column($options['types'], 0), true)) {
            throw new \DomainException('Seleccione un tipo de almacén válido.');
        }

        return $this->warehouses->save($code, new WarehouseData($name, $address, $data->status, $data->type), $user);
    }
}
