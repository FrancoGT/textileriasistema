<?php

declare(strict_types=1);

namespace App\Production;

/**
 * Orders listed by a board. Planning boards show the planning status
 * (E01CPED.CESTADOPL), production boards show the production status (CESTADOPR).
 */
enum OrderFilter
{
    case PlanningPending;
    case PlanningDone;
    case PlanningAll;
    case ProductionPending;
    case ProductionDone;
    case ProductionAll;

    public function showsProductionStatus(): bool
    {
        return \in_array($this, [self::ProductionPending, self::ProductionDone, self::ProductionAll], true);
    }
}
