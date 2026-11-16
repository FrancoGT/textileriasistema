<?php

declare(strict_types=1);

namespace App\Repository;

use App\Production\ProductionStage;

interface ProductionRepository
{
    /** @return list<array{0: string, 1: string}> machines reserved for the line and not working now */
    public function plannedMachines(string $order, string $product): array;

    /** @return list<array{0: string, 1: string}> staff reserved for the line and not working now */
    public function plannedStaff(string $order, string $product): array;

    /**
     * Starts the next process of an order line with one reserved machine and
     * one reserved staff member. $from is the process the caller saw, so a
     * line that changed in the meantime is rejected.
     */
    public function assign(string $order, string $product, ProductionStage $from, string $machine, string $person, string $user): void;

    /**
     * @return array{order: string, product: string, machine: string, person: string, name: string, description: string,
     *     quantity: string, process: string, machineDescription: string, machineType: string}|null
     */
    public function activeJob(string $person): ?array;

    /**
     * Ends a job, frees its machine and staff member, and completes the order
     * when every line finished the last process.
     */
    public function finish(string $order, string $product, string $machine, string $person): void;
}
