<?php

declare(strict_types=1);

namespace App\Maintenance;

use App\Repository\SupplierRepository;
use App\Security\ModulePermission;

final class SupplierService
{
    // ERP1060 currently shares the ERP1000 permission in the legacy application.
    private const PERMISSION = 'ERP1000';

    public function __construct(private readonly SupplierRepository $suppliers, private readonly ModulePermission $permissions)
    {
    }

    public function all(string $user): array
    {
        $this->permissions->assertGranted($user, self::PERMISSION);

        return $this->suppliers->all();
    }

    public function find(string $user, string $code): array
    {
        $this->permissions->assertGranted($user, self::PERMISSION);

        return $this->suppliers->find($code) ?? throw new \DomainException('El proveedor no existe.');
    }

    public function save(string $user, ?string $code, SupplierData $data): string
    {
        $this->permissions->assertGranted($user, self::PERMISSION);
        if ('' === trim($data->address) || mb_strlen($data->address) > 100) {
            throw new \DomainException('La dirección es obligatoria y admite hasta 100 caracteres.');
        }
        if (null === $code) {
            if ('' === trim($data->name) || mb_strlen($data->name) > 100) {
                throw new \DomainException('El nombre es obligatorio y admite hasta 100 caracteres.');
            }
            if (1 !== strlen($data->personType)) {
                throw new \DomainException('Seleccione el tipo de persona.');
            }
            $natural = 'a' === strtolower($data->personType);
            if (!preg_match($natural ? '/^\d{8}$/D' : '/^\d{11}$/D', $natural ? $data->dni : $data->ruc)) {
                throw new \DomainException($natural ? 'El DNI debe contener ocho dígitos.' : 'El RUC debe contener once dígitos.');
            }
            $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $data->date);
            if (false === $date || $date->format('Y-m-d') !== $data->date) {
                throw new \DomainException('La fecha debe ser válida y usar el formato aaaa-mm-dd.');
            }
        }
        foreach ($data->productionLines as $line) {
            if ('' === $line || strlen($line) > 2) {
                throw new \DomainException('La línea de producción no es válida.');
            }
        }

        return $this->suppliers->save($code, $data, $user);
    }
}
