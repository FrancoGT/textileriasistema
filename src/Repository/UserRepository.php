<?php

declare(strict_types=1);

namespace App\Repository;

interface UserRepository
{
    /** @return list<array{0: string, 1: string, 2: string}> code, name, profile, without the reserved administrator */
    public function all(): array;

    /** @return array{code: string, name: string, status: string, end_date: string, profile: string}|null */
    public function find(string $code): ?array;

    /** @return list<array{0: string, 1: string}> active personnel without a user account */
    public function candidates(): array;

    public function create(string $personnel, string $profile, string $user): string;

    public function update(string $code, string $status, string $profile, string $endDate, string $user): void;
}
