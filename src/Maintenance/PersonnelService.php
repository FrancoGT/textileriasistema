<?php

declare(strict_types=1);

namespace App\Maintenance;

use App\Repository\PersonnelRepository;
use App\Security\ModulePermission;

final class PersonnelService
{
    public function __construct(private readonly PersonnelRepository $people, private readonly ModulePermission $permissions)
    {
    }

    public function choices(string $user): array
    {
        $this->permissions->assertGranted($user, 'ERP1000');

        return $this->people->choices();
    }

    public function find(string $user, string $code): array
    {
        $this->permissions->assertGranted($user, 'ERP1000');

        return $this->people->find($code) ?? throw new \DomainException('El código de personal no existe.');
    }

    public function save(string $user, ?string $code, PersonnelData $data): string
    {
        $this->permissions->assertGranted($user, 'ERP1000');
        if ('' === trim($data->name) || mb_strlen($data->name) > 90) {
            throw new \DomainException('El nombre es obligatorio y admite hasta 90 caracteres.');
        }
        if ('' === trim($data->address) || mb_strlen($data->address) > 90) {
            throw new \DomainException('La dirección es obligatoria y admite hasta 90 caracteres.');
        }
        if (!preg_match('/^\d{8}$/D', $data->dni)) {
            throw new \DomainException('El DNI debe contener ocho dígitos.');
        }
        if (false === filter_var($data->email, FILTER_VALIDATE_EMAIL) || mb_strlen($data->email) > 50) {
            throw new \DomainException('El correo electrónico no es válido o supera 50 caracteres.');
        }
        foreach ([$data->sex, $data->civilStatus, $data->status] as $value) {
            if (1 !== strlen($value)) {
                throw new \DomainException('Seleccione sexo, estado civil y estado.');
            }
        }

        return $this->people->save($code, $data, $user);
    }
}
