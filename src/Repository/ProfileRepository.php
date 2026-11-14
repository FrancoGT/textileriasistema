<?php

declare(strict_types=1);

namespace App\Repository;

interface ProfileRepository
{
    /** @return list<array{0: string, 1: string, 2: string}> option code, option description, option status */
    public function granted(string $profile): array;

    /** @return list<array{0: string, 1: string}> active options that the profile does not have */
    public function available(string $profile): array;

    public function profileOf(string $user): ?string;

    public function grant(string $profile, string $option, string $status, string $user): void;

    public function revoke(string $profile, string $option): void;
}
