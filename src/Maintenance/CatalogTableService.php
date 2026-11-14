<?php

declare(strict_types=1);

namespace App\Maintenance;

use App\Repository\CatalogTableRepository;
use App\Security\ModulePermission;

final class CatalogTableService
{
    private const PERMISSION = 'ERP1080';

    public function __construct(private readonly CatalogTableRepository $tables, private readonly ModulePermission $permissions)
    {
    }

    /** @return list<array{0: string, 1: string}> */
    public function headers(string $user): array
    {
        $this->permissions->assertGranted($user, self::PERMISSION);

        return $this->tables->headers();
    }

    /** @return array{description: string, entries: list<CatalogEntry>} */
    public function open(string $user, string $table): array
    {
        $this->permissions->assertGranted($user, self::PERMISSION);
        $header = $this->header($table);

        return ['description' => $header['description'], 'entries' => $this->tables->entries($table)];
    }

    /**
     * Entries at the positions of $originalCodes replace those codes. Existing
     * entries cannot be removed, and their codes change only in table 021.
     *
     * @param list<string>       $originalCodes
     * @param list<CatalogEntry> $entries
     */
    public function save(string $user, string $table, array $originalCodes, array $entries): void
    {
        $this->permissions->assertGranted($user, self::PERMISSION);
        $format = CatalogCodeFormat::forTable($table, $this->header($table)['sample']);
        if ([] === $entries) {
            throw new \DomainException('Ingrese al menos una fila.');
        }
        if (\count($entries) < \count($originalCodes)) {
            throw new \DomainException('No se puede eliminar una fila existente.');
        }
        $normalized = [];
        $seen = [];
        foreach ($entries as $index => $entry) {
            $code = mb_strtoupper(trim($entry->code));
            if (isset($originalCodes[$index]) && !$format->allowsCodeChanges()) {
                $code = $originalCodes[$index];
            }
            $format->assertValid($code);
            if (isset($seen[$code])) {
                throw new \DomainException('El código ['.$code.'] está repetido.');
            }
            $seen[$code] = true;
            $description = trim($entry->description);
            if ('' === $description || mb_strlen($description) > 100) {
                throw new \DomainException('La descripción del código ['.$code.'] es obligatoria y admite hasta 100 caracteres.');
            }
            $order = trim($entry->order);
            // nOrden is INT(3).
            if (!preg_match('/^\d{0,3}$/D', $order)) {
                throw new \DomainException('El orden del código ['.$code.'] debe ser un número de hasta tres dígitos.');
            }
            $normalized[] = new CatalogEntry('' === $order ? '0' : $order, $code, $description);
        }
        $this->tables->save($table, $originalCodes, $normalized, $user);
    }

    /** @return array{description: string, sample: string} */
    private function header(string $table): array
    {
        return $this->tables->header($table) ?? throw new \DomainException('La tabla ['.$table.'] no existe.');
    }
}
