<?php

declare(strict_types=1);

namespace App\Repository;

interface PlanningRepository
{
    /** @return list<array{0: string, 1: string}> machines that no order uses */
    public function freeMachines(): array;

    /** @return list<array{0: string, 1: string}> staff that no order uses */
    public function freeStaff(): array;

    /** @return array{0: string, 1: string, 2: string}|null code, description, type */
    public function machine(string $code): ?array;

    /** @return array{0: string, 1: string}|null code, name */
    public function person(string $code): ?array;

    /**
     * Reserves the machines and the staff for one order line and completes its
     * planning. Completes the order when no other line is pending.
     *
     * @param list<string> $machines
     * @param list<string> $staff
     */
    public function plan(string $order, string $product, array $machines, array $staff, string $user): void;
}
