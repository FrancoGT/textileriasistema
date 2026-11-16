<?php

declare(strict_types=1);

namespace App\Production;

use App\Repository\ProductionRepository;
use App\Security\ModulePermission;

/** Shows the job of the logged-in worker and marks it as done (ERP8000). */
final class WorkService
{
    private const PERMISSION = 'ERP8000';

    public function __construct(private readonly ProductionRepository $production, private readonly ModulePermission $permissions)
    {
    }

    /**
     * @return array{order: string, product: string, machine: string, person: string, name: string, description: string,
     *     quantity: string, process: string, machineDescription: string, machineType: string}
     */
    public function job(string $user): array
    {
        $this->permissions->assertGranted($user, self::PERMISSION);

        return $this->production->activeJob(self::person($user)) ?? throw new \DomainException('El personal no tiene trabajo asignado.');
    }

    public function finish(string $user): void
    {
        $job = $this->job($user);
        $this->production->finish($job['order'], $job['product'], $job['machine'], $job['person']);
    }

    // A worker's staff code (6 characters) is the user code (4 characters) with "00" in front.
    private static function person(string $user): string
    {
        return '00'.$user;
    }
}
