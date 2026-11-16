<?php

declare(strict_types=1);

namespace App\Finance;

use App\Repository\PayableRepository;
use App\Security\ModulePermission;

/** Shows supplier invoices and records their payment from a bank account (ERP7010, ERP7020). */
final class PayableService
{
    public function __construct(private readonly PayableRepository $payables, private readonly ModulePermission $permissions)
    {
    }

    /** @return list<list<string>> */
    public function invoices(string $user, PayableBoard $board): array
    {
        $this->permissions->assertGranted($user, $board->value);

        return $this->payables->invoices(!$board->canPay());
    }

    /**
     * @return array{invoice: array{supplier: string, paid: bool, date: string, name: string, personType: string,
     *     personTypeName: string, dni: string, ruc: string, address: string}, lines: list<list<string>>, totals: TaxBreakdown}
     */
    public function detail(string $user, PayableBoard $board, string $code): array
    {
        $this->permissions->assertGranted($user, $board->value);
        if ('' === $code) {
            throw new \DomainException('Seleccione una factura.');
        }
        $invoice = $this->payables->invoice($code);
        if (null === $invoice || $invoice['paid'] === $board->canPay()) {
            throw new \DomainException('La factura no está en esta lista.');
        }
        $lines = $this->payables->lines($code);

        return ['invoice' => $invoice, 'lines' => $lines, 'totals' => TaxBreakdown::fromTotal(array_sum(array_map(static fn (array $line): float => (float) $line[5], $lines)))];
    }

    /**
     * @return array{name: string, supplierAccounts: list<array{0: string, 1: string}>, banks: list<array{0: string, 1: string}>}
     */
    public function paymentOptions(string $user, PayableBoard $board, string $code): array
    {
        $invoice = $this->payable($user, $board, $code);
        $accounts = $this->payables->supplierAccounts($invoice['supplier']);
        if ([] === $accounts) {
            throw new \DomainException('El proveedor no tiene cuentas designadas.');
        }
        $banks = $this->payables->banks();
        if ([] === $banks) {
            throw new \DomainException('No hay bancos registrados.');
        }

        return ['name' => $invoice['name'], 'supplierAccounts' => $accounts, 'banks' => $banks];
    }

    /** @return list<array{0: string, 1: string}> */
    public function bankAccounts(string $user, PayableBoard $board, string $code, string $bank): array
    {
        $options = $this->paymentOptions($user, $board, $code);
        if (!\in_array($bank, array_column($options['banks'], 0), true)) {
            throw new \DomainException('Seleccione un banco.');
        }
        $accounts = $this->payables->bankAccounts($bank);
        if ([] === $accounts) {
            throw new \DomainException('El banco no tiene cuentas designadas.');
        }

        return $accounts;
    }

    public function pay(string $user, PayableBoard $board, string $code, string $bank, string $account): void
    {
        if (!\in_array($account, array_column($this->bankAccounts($user, $board, $code, $bank), 0), true)) {
            throw new \DomainException('Seleccione una cuenta del banco.');
        }
        $this->payables->pay($code, $bank, $account);
    }

    /** @return array{supplier: string, paid: bool, date: string, name: string, personType: string, personTypeName: string, dni: string, ruc: string, address: string} */
    private function payable(string $user, PayableBoard $board, string $code): array
    {
        if (!$board->canPay()) {
            throw new \DomainException('Esta pantalla es solo de consulta.');
        }

        return $this->detail($user, $board, $code)['invoice'];
    }
}
