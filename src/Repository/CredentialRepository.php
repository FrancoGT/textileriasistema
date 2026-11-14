<?php

declare(strict_types=1);

namespace App\Repository;

interface CredentialRepository
{
    /** @return array{code: string, password: string, end_date: ?string, status: string, name: string}|null */
    public function find(string $code): ?array;

    /** Replaces the stored value only if it is still $current, so a concurrent change is kept. */
    public function rehash(string $code, string $current, string $hash): void;

    /** Stores the new hash and sets the next expiration date, as the legacy password change did. */
    public function changePassword(string $code, string $current, string $hash): void;

    /** False while E01TUSU.cClave is too short for a hash, before the migrations ran. */
    public function canStoreHashes(): bool;
}
