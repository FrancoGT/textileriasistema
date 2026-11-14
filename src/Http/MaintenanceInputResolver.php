<?php

declare(strict_types=1);

namespace App\Http;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsTargetedValueResolver;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

#[AsTargetedValueResolver]
final class MaintenanceInputResolver implements ValueResolverInterface
{
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if (MaintenanceInput::class !== $argument->getType()) {
            return [];
        }
        $data = $request->request->all();
        $fields = [];
        foreach (['pcCodPer', 'pcCodPrv', 'pcNombre', 'pcDirecc', 'pcEmail', 'pcNroDni', 'pcSexo', 'pcEstCiv', 'pcEstado', 'pcNroRuc', 'pcTipPer', 'pdFecNac', 'pcTexCon', '_token', 'Boton0', 'Boton1', 'Boton2'] as $name) {
            if (isset($data[$name]) && !\is_string($data[$name])) {
                throw new BadRequestHttpException('El campo '.$name.' debe ser texto.');
            }
            $fields[$name] = $data[$name] ?? '';
        }
        $lines = $data['paTipArs'] ?? [];
        if (!\is_array($lines) || \count($lines) > 100) {
            throw new BadRequestHttpException('Las líneas de producción no son válidas.');
        }
        foreach ($lines as $line) {
            if (!\is_string($line)) {
                throw new BadRequestHttpException('La línea de producción debe ser texto.');
            }
        }
        $action = $fields['Boton2'] ?: ($fields['Boton1'] ?: $fields['Boton0']);
        if (!\in_array($action, ['', 'Aplicar', 'Nuevo', 'Salir', 'Grabar', 'Cancelar', 'Editar'], true)) {
            throw new BadRequestHttpException('La acción no es válida.');
        }

        yield new MaintenanceInput($action, $fields, array_values(array_unique($lines)));
    }
}
