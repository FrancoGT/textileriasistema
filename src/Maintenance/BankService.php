<?php

declare(strict_types=1);

namespace App\Maintenance;

use App\Repository\BankRepository;
use App\Repository\CatalogRepository;
use App\Security\ModulePermission;

final class BankService
{
    private const PERMISSION = 'ERP1093';

    public function __construct(
        private readonly BankRepository $banks,
        private readonly CatalogRepository $catalogs,
        private readonly ModulePermission $permissions,
    ) {
    }

    /** @return list<array{0: string, 1: string}> */
    public function choices(string $user): array
    {
        $this->permissions->assertGranted($user, self::PERMISSION);

        return $this->banks->choices();
    }

    /** @return array{statuses: list<array{0: string, 1: string}>, cities: list<array{0: string, 1: string}>} */
    public function options(string $user): array
    {
        $this->permissions->assertGranted($user, self::PERMISSION);

        return ['statuses' => $this->catalogs->choices('001', true), 'cities' => $this->catalogs->choices('013')];
    }

    public function find(string $user, string $code): array
    {
        $this->permissions->assertGranted($user, self::PERMISSION);

        return $this->banks->find($code) ?? throw new \DomainException('El banco no existe.');
    }

    public function save(string $user, ?string $code, BankData $data): string
    {
        $options = $this->options($user);
        $name = mb_strtoupper(trim($data->name));
        if ('' === $name || mb_strlen($name) > 50) {
            throw new \DomainException('El nombre es obligatorio y admite hasta 50 caracteres.');
        }
        if (!\in_array($data->status, array_column($options['statuses'], 0), true)) {
            throw new \DomainException('Seleccione un estado válido.');
        }
        if (!\in_array($data->city, array_column($options['cities'], 0), true)) {
            throw new \DomainException('Seleccione una ciudad válida.');
        }

        return $this->banks->save($code, new BankData($name, $data->status, $data->city), $user);
    }
}
