<?php

declare(strict_types=1);

namespace App\Production;

use Symfony\Component\DependencyInjection\Attribute\Exclude;

/** Report of the order lines that are in one production process. */
#[Exclude]
final readonly class StageBoard
{
    public function __construct(
        public string $screen,
        public string $permission,
        public string $title,
        public ProductionStage $stage,
        public string $emptyMessage,
    ) {
    }
}
