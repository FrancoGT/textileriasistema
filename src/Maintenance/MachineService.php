<?php

declare(strict_types=1);

namespace App\Maintenance;

use App\Repository\CatalogRepository;
use App\Repository\MachineRepository;
use App\Security\ModulePermission;

final class MachineService
{
    private const PERMISSION = 'ERP1040';
    private const STATUS_TABLE = '001';
    private const TYPE_TABLE = '005';

    public function __construct(
        private readonly MachineRepository $machines,
        private readonly CatalogRepository $catalogs,
        private readonly ModulePermission $permissions,
    ) {
    }

    /** @return list<array{0: string, 1: string}> */
    public function choices(string $user): array
    {
        $this->permissions->assertGranted($user, self::PERMISSION);

        return $this->machines->choices();
    }

    /** @return array{statuses: list<array{0: string, 1: string}>, types: list<array{0: string, 1: string}>} */
    public function options(string $user): array
    {
        $this->permissions->assertGranted($user, self::PERMISSION);

        return ['statuses' => $this->catalogs->choices(self::STATUS_TABLE, true), 'types' => $this->catalogs->choices(self::TYPE_TABLE, true)];
    }

    public function find(string $user, string $code): array
    {
        $this->permissions->assertGranted($user, self::PERMISSION);

        return $this->machines->find($code) ?? throw new \DomainException('La máquina no existe.');
    }

    public function save(string $user, ?string $code, MachineData $data): string
    {
        $options = $this->options($user);
        $description = mb_strtoupper(trim($data->description));
        // E01MMAQ.cDescri is VARCHAR(50), although the legacy form allowed 90 characters.
        if ('' === $description || mb_strlen($description) > 50) {
            throw new \DomainException('La descripción es obligatoria y admite hasta 50 caracteres.');
        }
        if (!\in_array($data->status, array_column($options['statuses'], 0), true)) {
            throw new \DomainException('Seleccione un estado válido.');
        }
        if (!\in_array($data->type, array_column($options['types'], 0), true)) {
            throw new \DomainException('Seleccione un tipo de máquina válido.');
        }

        return $this->machines->save($code, new MachineData($description, $data->status, $data->type), $user);
    }
}
