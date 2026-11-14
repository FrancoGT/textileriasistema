<?php

declare(strict_types=1);

namespace App\Security;

use App\Repository\CatalogRepository;
use App\Repository\DbalUserRepository;
use App\Repository\UserRepository;

final class UserService
{
    private const PERMISSION = 'ERP1010';

    public function __construct(
        private readonly UserRepository $users,
        private readonly CatalogRepository $catalogs,
        private readonly ModulePermission $permissions,
    ) {
    }

    /** @return list<array{0: string, 1: string, 2: string}> */
    public function all(string $user): array
    {
        $this->permissions->assertGranted($user, self::PERMISSION);

        return $this->users->all();
    }

    /** @return array{statuses: list<array{0: string, 1: string}>, profiles: list<array{0: string, 1: string}>} */
    public function options(string $user): array
    {
        $this->permissions->assertGranted($user, self::PERMISSION);

        return ['statuses' => $this->catalogs->choices('001', true), 'profiles' => $this->catalogs->choices('049', true)];
    }

    /** @return list<array{0: string, 1: string}> */
    public function candidates(string $user): array
    {
        $this->permissions->assertGranted($user, self::PERMISSION);

        return $this->users->candidates() ?: throw new \DomainException('No hay personal activo disponible para ser usuario.');
    }

    /** The reserved administrator is not listed and cannot be edited from this screen, as in the legacy list. */
    public function find(string $user, string $code): array
    {
        $this->permissions->assertGranted($user, self::PERMISSION);
        if ('' === $code || DbalUserRepository::ADMINISTRATOR === $code) {
            throw new \DomainException('Seleccione un usuario.');
        }

        return $this->users->find($code) ?? throw new \DomainException('El usuario no existe.');
    }

    public function create(string $user, string $personnel, string $profile): string
    {
        $options = $this->options($user);
        $this->assertChoice($profile, $options['profiles'], 'Seleccione un perfil válido.');

        return $this->users->create($personnel, $profile, $user);
    }

    public function update(string $user, string $code, string $status, string $profile, string $endDate): void
    {
        $options = $this->options($user);
        $this->find($user, $code);
        $this->assertChoice($status, $options['statuses'], 'Seleccione un estado válido.');
        $this->assertChoice($profile, $options['profiles'], 'Seleccione un perfil válido.');
        $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $endDate);
        if (false === $date || $date->format('Y-m-d') !== $endDate) {
            throw new \DomainException('La fecha de expiración de clave debe ser válida y usar el formato aaaa-mm-dd.');
        }
        $this->users->update($code, $status, $profile, $endDate, $user);
    }

    /** @param list<array{0: string, 1: string}> $choices */
    private function assertChoice(string $value, array $choices, string $message): void
    {
        if (!\in_array($value, array_column($choices, 0), true)) {
            throw new \DomainException($message);
        }
    }
}
