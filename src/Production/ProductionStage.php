<?php

declare(strict_types=1);

namespace App\Production;

/**
 * Process of an order line (E01DPED.CPROCESO, catalog table 012). Each work
 * assignment moves the line to the next process.
 */
enum ProductionStage: string
{
    case Pending = 'I';
    case Dyeing = 'A';
    case Preparation = 'B';
    case Weaving = 'C';
    case Assembly = 'D';
    case Finishing = 'E';

    public function next(): self
    {
        return match ($this) {
            self::Pending => self::Dyeing,
            self::Dyeing => self::Preparation,
            self::Preparation => self::Weaving,
            self::Weaving => self::Assembly,
            self::Assembly => self::Finishing,
            self::Finishing => throw new \DomainException('El producto ya terminó todos los procesos.'),
        };
    }
}
