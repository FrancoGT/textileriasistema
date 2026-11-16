<?php

declare(strict_types=1);

namespace App\Repository;

use App\Maintenance\AccountOwner;
use Doctrine\DBAL\Connection;

final class DbalPayableRepository implements PayableRepository
{
    public function __construct(private readonly Connection $connection, private readonly TransactionalTables $tables)
    {
    }

    public function invoices(bool $paid): array
    {
        // The legacy screen added up the lines of every purchase for each invoice.
        return $this->rows(
            'SELECT A.cCodCom, A.cCodPrv, B.cNombre, A.dFecLis,
                    (SELECT COALESCE(SUM(D.NCANT * I.nPrecio), 0) FROM E02DCOM D INNER JOIN E01MINS I ON I.cCodIns = D.CCODINS WHERE D.CCODCOM = A.cCodCom)
             FROM E01CCOM A INNER JOIN E01MPRV B ON A.cCodPrv = B.cCodPrv WHERE A.cEstado = :status ORDER BY A.cCodCom',
            ['status' => $paid ? 'I' : 'A'],
        );
    }

    public function invoice(string $code): ?array
    {
        $row = $this->connection->fetchAssociative(
            "SELECT A.cCodPrv, A.cEstado, A.dFecLis, B.cNombre, B.cTipPer, T.cDescri AS cDesTip, B.cNroDni, B.cNroRuc, B.cDirecc
             FROM E01CCOM A LEFT JOIN E01MPRV B ON B.cCodPrv = A.cCodPrv
             LEFT JOIN V_E01TTAB T ON T.cCodigo = B.cTipPer AND T.cCodTab = '098'
             WHERE A.cCodCom = :code",
            ['code' => $code],
        );
        if (false === $row) {
            return null;
        }

        return ['supplier' => (string) $row['cCodPrv'], 'paid' => 'I' === $row['cEstado'], 'date' => (string) $row['dFecLis'],
            'name' => (string) $row['cNombre'], 'personType' => (string) $row['cTipPer'], 'personTypeName' => (string) $row['cDesTip'],
            'dni' => (string) $row['cNroDni'], 'ruc' => (string) $row['cNroRuc'], 'address' => (string) $row['cDirecc']];
    }

    public function lines(string $code): array
    {
        return $this->rows(
            "SELECT A.CCODINS, B.cDescri, C.cDescri, B.nPrecio, A.NCANT, B.nPrecio * A.NCANT
             FROM E02DCOM A INNER JOIN E01MINS B ON A.CCODINS = B.cCodIns
             LEFT JOIN V_E01TTAB C ON C.cCodigo = B.cUniMed AND C.cCodTab = '007'
             WHERE A.CCODCOM = :code ORDER BY A.CCODINS",
            ['code' => $code],
        );
    }

    public function supplierAccounts(string $supplier): array
    {
        return $this->pairs('SELECT NCUENTA, NCUENTA FROM E01PCTA WHERE CCODPRV = :supplier AND CCODBAN = :none ORDER BY NCUENTA',
            ['supplier' => $supplier, 'none' => AccountOwner::PLACEHOLDER]);
    }

    public function banks(): array
    {
        return $this->pairs('SELECT CCODBAN, CNOMBAN FROM E01MBAN WHERE CCODBAN <> :none ORDER BY CCODBAN', ['none' => AccountOwner::PLACEHOLDER]);
    }

    public function bankAccounts(string $bank): array
    {
        return $this->pairs('SELECT NCUENTA, NCUENTA FROM E01PCTA WHERE CCODBAN = :bank AND CCODPRV = :none ORDER BY NCUENTA',
            ['bank' => $bank, 'none' => AccountOwner::PLACEHOLDER]);
    }

    public function pay(string $code, string $bank, string $account): void
    {
        $this->tables->requireInnoDb('E01CCOM');
        $this->connection->transactional(function () use ($code, $bank, $account): void {
            $status = $this->connection->fetchOne('SELECT cEstado FROM E01CCOM WHERE cCodCom = :code FOR UPDATE', ['code' => $code]);
            if ('A' !== $status) {
                throw new \DomainException('La factura ya fue pagada.');
            }
            $this->connection->update('E01CCOM', ['cEstado' => 'I', 'cCtaPag' => $account, 'cCodBan' => $bank,
                'dFecLis' => date('Y-m-d')], ['cCodCom' => $code]);
        });
    }

    /**
     * @param array<string, string> $parameters
     *
     * @return list<list<string>>
     */
    private function rows(string $sql, array $parameters): array
    {
        return array_map(static fn (array $row): array => array_map(static fn (mixed $value): string => (string) $value, $row),
            $this->connection->fetchAllNumeric($sql, $parameters));
    }

    /**
     * @param array<string, string> $parameters
     *
     * @return list<array{0: string, 1: string}>
     */
    private function pairs(string $sql, array $parameters): array
    {
        return array_map(static fn (array $row): array => [(string) $row[0], (string) $row[1]], $this->connection->fetchAllNumeric($sql, $parameters));
    }
}
