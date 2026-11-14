<?php

declare(strict_types=1);

namespace App\Security;

use App\Repository\CatalogRepository;
use App\Repository\ProfileRepository;

final class ProfileService
{
    private const PERMISSION = 'ERP1020';

    public function __construct(
        private readonly ProfileRepository $profiles,
        private readonly CatalogRepository $catalogs,
        private readonly ModulePermission $permissions,
    ) {
    }

    /** @return array{statuses: list<array{0: string, 1: string}>, profiles: list<array{0: string, 1: string}>} */
    public function options(string $user): array
    {
        $this->permissions->assertGranted($user, self::PERMISSION);

        return ['statuses' => $this->catalogs->choices('001', true), 'profiles' => $this->catalogs->choices('049', true)];
    }

    /** @return array{description: string, granted: list<array{0: string, 1: string, 2: string}>} */
    public function open(string $user, string $profile): array
    {
        return ['description' => $this->description($user, $profile), 'granted' => $this->profiles->granted($profile)];
    }

    /** @return list<array{0: string, 1: string}> */
    public function available(string $user, string $profile): array
    {
        $this->description($user, $profile);

        return $this->profiles->available($profile) ?: throw new \DomainException('No hay opciones libres para el perfil.');
    }

    public function grant(string $user, string $profile, string $option, string $status): void
    {
        if (!\in_array($option, array_column($this->available($user, $profile), 0), true)) {
            throw new \DomainException('Seleccione una opción disponible.');
        }
        if (!\in_array($status, array_column($this->options($user)['statuses'], 0), true)) {
            throw new \DomainException('Seleccione un estado válido.');
        }
        $this->profiles->grant($profile, $option, $status, $user);
    }

    public function revoke(string $user, string $profile, string $option): void
    {
        $this->description($user, $profile);
        if ('' === $option) {
            throw new \DomainException('Seleccione una opción.');
        }
        if (self::PERMISSION === $option && $this->profiles->profileOf($user) === $profile) {
            throw new \DomainException('No puede quitar '.self::PERMISSION.' de su propio perfil.');
        }
        $this->profiles->revoke($profile, $option);
    }

    private function description(string $user, string $profile): string
    {
        foreach ($this->options($user)['profiles'] as [$code, $description]) {
            if ($code === $profile) {
                return $description;
            }
        }
        throw new \DomainException('Seleccione un perfil válido.');
    }
}
