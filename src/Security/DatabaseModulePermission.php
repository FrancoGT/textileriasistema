<?php

declare(strict_types=1);

namespace App\Security;

use App\Repository\ModulePermissionRepository;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

final class DatabaseModulePermission implements ModulePermission
{
    public function __construct(private readonly ModulePermissionRepository $permissions)
    {
    }

    public function assertGranted(string $user, string $option): void
    {
        if ('' === $user || !$this->permissions->allows($user, $option)) {
            throw new AccessDeniedHttpException('No tiene permiso para esta opción.');
        }
    }
}
