<?php

declare(strict_types=1);

namespace App\Maintenance;

use App\Repository\CatalogRepository;
use App\Repository\ProductRepository;
use App\Repository\StockRepository;
use App\Security\ModulePermission;

final class ProductService
{
    private const PERMISSION = 'ERP1070';

    public function __construct(
        private readonly ProductRepository $products,
        private readonly StockRepository $stock,
        private readonly CatalogRepository $catalogs,
        private readonly ModulePermission $permissions,
    ) {
    }

    /** @return list<array{0: string, 1: string, 2: string, 3: string}> */
    public function all(string $user): array
    {
        $this->permissions->assertGranted($user, self::PERMISSION);

        return $this->products->all();
    }

    /** @return list<array{0: string, 1: string}> */
    public function statuses(string $user): array
    {
        $this->permissions->assertGranted($user, self::PERMISSION);

        return $this->catalogs->choices('001', true);
    }

    /** @return array<string, array{0: string, 1: string, 2: string, 3: string}> code => code, description, unit, price */
    public function supplies(string $user): array
    {
        $this->permissions->assertGranted($user, self::PERMISSION);

        return $this->stock->supplies();
    }

    /** @return array{code: string, description: string, status: string, recipe: list<string>} */
    public function find(string $user, string $code): array
    {
        $this->permissions->assertGranted($user, self::PERMISSION);
        $product = $this->products->find($code) ?? throw new \DomainException('Seleccione un producto.');

        return $product + ['recipe' => $this->products->recipe($code)];
    }

    public function create(string $user, string $description, string $status): string
    {
        $description = $this->description($description);
        if (!\in_array($status, array_column($this->statuses($user), 0), true)) {
            throw new \DomainException('Seleccione un estado válido.');
        }

        return $this->products->create($description, $status, $user);
    }

    /** @param list<string> $supplies */
    public function saveRecipe(string $user, string $code, string $description, array $supplies): void
    {
        $known = $this->supplies($user);
        if ([] === $supplies) {
            throw new \DomainException('Agregue al menos un insumo al producto.');
        }
        foreach ($supplies as $supply) {
            if (!isset($known[$supply])) {
                throw new \DomainException('El insumo '.$supply.' no existe.');
            }
        }
        $this->products->saveRecipe($code, $this->description($description), array_values(array_unique($supplies)), $user);
    }

    private function description(string $description): string
    {
        $description = mb_strtoupper(trim($description));
        if ('' === $description || mb_strlen($description) > 100) {
            throw new \DomainException('La descripción es obligatoria y admite hasta 100 caracteres.');
        }

        return $description;
    }
}
