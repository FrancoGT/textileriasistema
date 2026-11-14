<?php

declare(strict_types=1);

namespace App\Repository;

interface ProductRepository
{
    /** @return list<array{0: string, 1: string, 2: string, 3: string}> code, description, status, type */
    public function all(): array;

    /** @return array{code: string, description: string, status: string}|null */
    public function find(string $code): ?array;

    /** @return list<string> supply codes */
    public function recipe(string $code): array;

    public function create(string $description, string $status, string $user): string;

    /** @param list<string> $supplies */
    public function saveRecipe(string $code, string $description, array $supplies, string $user): void;
}
