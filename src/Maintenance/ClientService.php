<?php

declare(strict_types=1);

namespace App\Maintenance;

use App\Repository\CatalogRepository;
use App\Repository\ClientRepository;
use App\Security\ModulePermission;

final class ClientService
{
    private const PERMISSION = 'ERP1030';

    public function __construct(
        private readonly ClientRepository $clients,
        private readonly CatalogRepository $catalogs,
        private readonly ModulePermission $permissions,
    ) {
    }

    /** @return array{types: list<array{0: string, 1: string}>, statuses: list<array{0: string, 1: string}>, priorities: list<array{0: string, 1: string}>, ciiu: list<array{0: string, 1: string}>} */
    public function options(string $user): array
    {
        $this->permissions->assertGranted($user, self::PERMISSION);

        return ['types' => $this->catalogs->choices('098', true), 'statuses' => $this->catalogs->choices('001', true),
            'priorities' => $this->catalogs->choices('002', true), 'ciiu' => $this->catalogs->choices('099')];
    }

    /** @return list<array{0: string, 1: string}> */
    public function choices(string $user, ClientType $type): array
    {
        $this->permissions->assertGranted($user, self::PERMISSION);

        return $this->clients->choices($type);
    }

    /** @return array<string, string> */
    public function find(string $user, string $code): array
    {
        $this->permissions->assertGranted($user, self::PERMISSION);

        return $this->clients->find($code) ?? throw new \DomainException('El cliente no existe.');
    }

    /**
     * On update, type, name, DNI and RUC keep their stored values, because the
     * legacy edit form shows them read-only.
     */
    public function save(string $user, ?string $code, ClientData $data): string
    {
        $options = $this->options($user);
        $new = null === $code;
        if (!$new) {
            $row = $this->find($user, $code);
            $type = ClientType::tryFrom($row['type']) ?? throw new \DomainException('El cliente tiene un tipo desconocido.');
            $data = new ClientData($type, $row['name'], $row['dni'], $row['ruc'], $data->address, $data->phone,
                $data->status, $data->ciiu, $data->acronym, $data->priority);
        }
        $natural = $data->type->isNatural();
        $name = mb_strtoupper(trim($data->name));
        $address = mb_strtoupper(trim($data->address));
        if ('' === $name || mb_strlen($name) > 100) {
            throw new \DomainException('El nombre es obligatorio y admite hasta 100 caracteres.');
        }
        if ($new && $natural && !preg_match('/^\d{8}$/D', $data->dni)) {
            throw new \DomainException('El DNI debe contener ocho dígitos.');
        }
        if ($new && !$natural && !preg_match('/^\d{11}$/D', $data->ruc)) {
            throw new \DomainException('El RUC debe contener once dígitos.');
        }
        if ('' === $address || mb_strlen($address) > 200) {
            throw new \DomainException('La dirección es obligatoria y admite hasta 200 caracteres.');
        }
        if (!preg_match('/^[0-9 ()+-]{1,15}$/D', $data->phone)) {
            throw new \DomainException('El teléfono es obligatorio y admite hasta 15 caracteres numéricos.');
        }
        $this->assertChoice($data->status, $options['statuses'], 'Seleccione un estado válido.');
        $this->assertChoice($data->priority, $options['priorities'], 'Seleccione una prioridad válida.');
        $ciiu = ClientType::CIIU_PLACEHOLDER;
        $acronym = ClientType::ACRONYM_PLACEHOLDER;
        if (!$natural) {
            $this->assertChoice($data->ciiu, $options['ciiu'], 'Seleccione un código CIIU válido.');
            $ciiu = $data->ciiu;
            $acronym = mb_strtoupper(trim($data->acronym));
            if ('' === $acronym || mb_strlen($acronym) > 4) {
                throw new \DomainException('Las siglas son obligatorias y admiten hasta 4 caracteres.');
            }
        }
        $dni = $data->dni;
        $ruc = $data->ruc;
        if ($new) {
            $dni = $natural ? $data->dni : ClientType::DNI_PLACEHOLDER;
            $ruc = $natural ? ClientType::RUC_PLACEHOLDER : $data->ruc;
        }

        return $this->clients->save($code, new ClientData($data->type, $name, $dni, $ruc, $address, $data->phone,
            $data->status, $ciiu, $acronym, $data->priority), $user);
    }

    /** @param list<array{0: string, 1: string}> $choices */
    private function assertChoice(string $value, array $choices, string $message): void
    {
        if (!\in_array($value, array_column($choices, 0), true)) {
            throw new \DomainException($message);
        }
    }
}
