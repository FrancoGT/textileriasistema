<?php

declare(strict_types=1);

namespace App\Finance;

use App\Repository\ReceivableRepository;
use App\Security\ModulePermission;

/** Splits the total of an order into installments (ERP6010 to ERP6030). */
final class ReceivableService
{
    public function __construct(private readonly ReceivableRepository $receivables, private readonly ModulePermission $permissions)
    {
    }

    /** @return list<list<string>> */
    public function orders(string $user, ReceivableBoard $board): array
    {
        $this->permissions->assertGranted($user, $board->value);

        return $this->receivables->orders($board);
    }

    public function total(string $user, ReceivableBoard $board, string $order): int
    {
        $this->permissions->assertGranted($user, $board->value);
        if ('' === $order) {
            throw new \DomainException('Seleccione un pedido.');
        }

        return $this->receivables->total($order) ?? throw new \DomainException('El pedido no existe.');
    }

    /** The legacy screen took the total from the form and ignored the due date. */
    public function createPlan(string $user, ReceivableBoard $board, string $order, string $payments, string $dueDate): void
    {
        $total = $this->total($user, $board, $order);
        if (!ctype_digit($payments) || (int) $payments < 1 || (int) $payments > 120) {
            throw new \DomainException('El número de pagos debe ser un entero entre 1 y 120.');
        }
        if ($total <= 0) {
            throw new \DomainException('El pedido no tiene importe.');
        }
        $date = null;
        if ('' !== $dueDate) {
            $parsed = \DateTimeImmutable::createFromFormat('!Y-m-d', $dueDate);
            if (false === $parsed || $parsed->format('Y-m-d') !== $dueDate) {
                throw new \DomainException('La fecha límite debe tener el formato aaaa-mm-dd.');
            }
            $date = $dueDate;
        }
        $this->receivables->createPlan($order, $total, (int) round($total / (int) $payments), $date, $user);
    }
}
