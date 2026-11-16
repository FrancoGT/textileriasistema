<?php

declare(strict_types=1);

namespace App\Production;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Configuration of the order boards. A new board needs an entry here and a
 * route in the controller, and no change to the services.
 */
final class Boards
{
    public function planning(string $screen): OrderBoard
    {
        return match ($screen) {
            'ERP3000' => new OrderBoard($screen, 'ERP3000', 'Reporte de Pedidos', OrderFilter::PlanningPending, true),
            'ERP3010' => new OrderBoard($screen, 'ERP3010', 'Reporte de Pedidos Completos', OrderFilter::PlanningDone, false),
            'ERP3020' => new OrderBoard($screen, 'ERP3020', 'Reporte de Pedidos Completos e Incompletos', OrderFilter::PlanningAll, false),
            default => throw new NotFoundHttpException(),
        };
    }

    public function production(string $screen): OrderBoard
    {
        return match ($screen) {
            'ERP4000' => new OrderBoard($screen, 'ERP4000', 'Reporte de Pedidos Para Produccion', OrderFilter::ProductionPending, true),
            'ERP4060' => new OrderBoard($screen, 'ERP4060', 'Reporte de Pedidos Completos', OrderFilter::ProductionDone, false),
            'ERP4070' => new OrderBoard($screen, 'ERP4070', 'Reporte de Pedidos (Todos)', OrderFilter::ProductionAll, true),
            default => throw new NotFoundHttpException(),
        };
    }

    public function stage(string $screen): StageBoard
    {
        $title = 'Reporte de Pedidos Para Produccion Proceso de ';

        return match ($screen) {
            'ERP4010' => new StageBoard($screen, 'ERP4010', $title.'Teñido', ProductionStage::Dyeing, 'NO HAY PRODUCTOS EN PROCESO DE TEÑIDO'),
            'ERP4020' => new StageBoard($screen, 'ERP4020', $title.'Preparacion', ProductionStage::Preparation, 'NO HAY PRODUCTOS EN PROCESO DE PREPARACION'),
            'ERP4030' => new StageBoard($screen, 'ERP4030', $title.'Tejido', ProductionStage::Weaving, 'NO HAY PRODUCTOS EN PROCESO DE TEJIDO'),
            'ERP4040' => new StageBoard($screen, 'ERP4040', $title.'Ensamblaje', ProductionStage::Assembly, 'NO HAY PRODUCTOS EN PROCESO DE ENSAMBLAJE'),
            // The legacy ERP4050 script showed the weaving lines with the ERP4030 permission.
            'ERP4050' => new StageBoard($screen, 'ERP4050', $title.'Acabado', ProductionStage::Finishing, 'NO HAY PRODUCTOS EN PROCESO DE ACABADO'),
            default => throw new NotFoundHttpException(),
        };
    }
}
