<?php

declare(strict_types=1);

namespace App\Maintenance;

use App\Repository\CatalogRepository;
use App\Repository\SupplyRepository;
use App\Security\ModulePermission;

final class SupplyService
{
    private const PERMISSION = 'ERP1050';

    public function __construct(
        private readonly SupplyRepository $supplies,
        private readonly CatalogRepository $catalogs,
        private readonly ModulePermission $permissions,
    ) {
    }

    /** @return list<list<string>> */
    public function all(string $user): array
    {
        $this->permissions->assertGranted($user, self::PERMISSION);

        return $this->supplies->all();
    }

    /** @return array{subtypes: list<array{0: string, 1: string}>, units: list<array{0: string, 1: string}>} */
    public function options(string $user): array
    {
        $this->permissions->assertGranted($user, self::PERMISSION);

        return ['subtypes' => $this->catalogs->choices('011'), 'units' => $this->catalogs->choices('007')];
    }

    public function find(string $user, string $code): array
    {
        $this->permissions->assertGranted($user, self::PERMISSION);
        if ('' === $code) {
            throw new \DomainException('Seleccione un insumo.');
        }

        return $this->supplies->find($code) ?? throw new \DomainException('El insumo no existe.');
    }

    /** On update, the subtype and description keep their stored values, as in the legacy screen. */
    public function save(string $user, ?string $code, SupplyData $data): string
    {
        $options = $this->options($user);
        $description = mb_strtoupper(trim($data->description));
        if (null === $code) {
            if (!\in_array($data->subtype, array_column($options['subtypes'], 0), true) || 2 !== \strlen($data->subtype)) {
                throw new \DomainException('Seleccione un subtipo válido.');
            }
            if ('' === $description || mb_strlen($description) > 200) {
                throw new \DomainException('La descripción es obligatoria y admite hasta 200 caracteres.');
            }
        }
        if (!\in_array($data->unit, array_column($options['units'], 0), true)) {
            throw new \DomainException('Seleccione una unidad de medida válida.');
        }
        // nPrecio is DECIMAL(12,2).
        if (!preg_match('/^\d{1,10}(\.\d{1,2})?$/D', $data->price) || 0.0 === (float) $data->price) {
            throw new \DomainException('El precio debe ser mayor que cero, con hasta dos decimales.');
        }
        if (!preg_match('/^[1-9]\d{0,8}$/D', $data->granularity)) {
            throw new \DomainException('El granulado debe ser un número entero mayor que cero.');
        }

        return $this->supplies->save($code, new SupplyData($data->subtype, $description, $data->unit, $data->price, $data->granularity), $user);
    }

    public function delete(string $user, string $code): void
    {
        $this->permissions->assertGranted($user, self::PERMISSION);
        if ('' === $code) {
            throw new \DomainException('Seleccione un insumo.');
        }
        $this->supplies->delete($code);
    }
}
