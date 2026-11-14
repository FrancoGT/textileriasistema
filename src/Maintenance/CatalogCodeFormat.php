<?php

declare(strict_types=1);

namespace App\Maintenance;

use Symfony\Component\DependencyInjection\Attribute\Exclude;

/**
 * Code rule of one catalog table. The header row (cTipReg = '0') stores a
 * sample code in cCodigo: detail codes must have the same length. Table 021
 * stores numeric keys such as "1.2", up to that length.
 */
#[Exclude]
final readonly class CatalogCodeFormat
{
    private const NUMERIC_KEY_TABLE = '021';
    private const MAX_LENGTH = 10;

    private function __construct(private int $length, private bool $numericKey)
    {
    }

    public static function forTable(string $table, string $sample): self
    {
        return new self(\strlen(trim($sample)), self::NUMERIC_KEY_TABLE === $table);
    }

    /** Codes of table 021 can change; other tables keep the stored codes. */
    public function allowsCodeChanges(): bool
    {
        return $this->numericKey;
    }

    public function assertValid(string $code): void
    {
        if ('' === $code || \strlen($code) > self::MAX_LENGTH) {
            throw new \DomainException('El código ['.$code.'] es obligatorio y admite hasta '.self::MAX_LENGTH.' caracteres.');
        }
        if (0 === $this->length) {
            return;
        }
        if ($this->numericKey) {
            if (\strlen($code) > $this->length || !preg_match('/^[0-9.]+$/D', $code)) {
                throw new \DomainException('El código ['.$code.'] debe tener hasta '.$this->length.' dígitos o puntos.');
            }

            return;
        }
        if (\strlen($code) !== $this->length) {
            throw new \DomainException('El código ['.$code.'] debe tener '.$this->length.' caracteres.');
        }
    }
}
