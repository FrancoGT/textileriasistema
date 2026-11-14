<?php

declare(strict_types=1);

namespace App\Security;

interface ModulePermission
{
    public function assertGranted(string $user, string $option): void;
}
