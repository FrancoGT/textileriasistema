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
    private const LIST_FIELD = 'paTipArs';
    // Table fields posted as paData[row][column] and paCheck[].
    private const GRID_FIELDS = ['paData', 'paDataI', 'paDataP', 'paCheck'];
    private const MAX_GRID_ROWS = 1000;
    private const MAX_GRID_CELLS = 10;
    // Submit button names used by the templates, in order of precedence.
    private const BUTTONS = ['Boton2', 'Boton1', 'Boton0', 'Boton', 'Boton3', 'Boton4', 'Boton5', 'BotonM', 'BotonP', 'Aceptar', 'Aplicar', 'Grabar', 'Cancelar', 'Salir'];
    private const ACTIONS = ['', 'Aceptar', 'Aplicar', 'Nuevo', 'Salir', 'Grabar', 'Cancelar', 'Editar', 'Eliminar',
        'Detalle', 'Asignar Insumos', 'Agregar', 'Actualizar', 'Asignar', 'Hecho', 'Pagar', 'Cargar', 'Nuevo Cliente',
        'Cargar Datos', 'Calcular'];

    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if (MaintenanceInput::class !== $argument->getType()) {
            return [];
        }
        $data = $request->request->all();
        $fields = [];
        $grids = [];
        foreach ($data as $name => $value) {
            if (self::LIST_FIELD === $name) {
                continue;
            }
            if (\in_array($name, self::GRID_FIELDS, true)) {
                $grids[$name] = $this->grid($name, $value);
                continue;
            }
            if (!\is_string($value)) {
                throw new BadRequestHttpException('El campo '.$name.' debe ser texto.');
            }
            $fields[(string) $name] = $value;
        }
        $lines = $data[self::LIST_FIELD] ?? [];
        if (!\is_array($lines) || \count($lines) > 100) {
            throw new BadRequestHttpException('Las líneas de producción no son válidas.');
        }
        foreach ($lines as $line) {
            if (!\is_string($line)) {
                throw new BadRequestHttpException('La línea de producción debe ser texto.');
            }
        }
        $action = '';
        $pressed = '';
        foreach (self::BUTTONS as $button) {
            if ('' !== ($fields[$button] ?? '')) {
                $action = $fields[$button];
                $pressed = $button;
                break;
            }
        }
        if (!\in_array($action, self::ACTIONS, true)) {
            throw new BadRequestHttpException('La acción no es válida.');
        }

        yield new MaintenanceInput($action, $fields, array_values(array_unique($lines)), $grids, $pressed);
    }

    /** @return array<array-key, string|array<array-key, string>> */
    private function grid(string $name, mixed $value): array
    {
        if (!\is_array($value) || \count($value) > self::MAX_GRID_ROWS) {
            throw new BadRequestHttpException('El campo '.$name.' no es válido.');
        }
        foreach ($value as $row) {
            if (\is_string($row)) {
                continue;
            }
            if (!\is_array($row) || \count($row) > self::MAX_GRID_CELLS) {
                throw new BadRequestHttpException('El campo '.$name.' no es válido.');
            }
            foreach ($row as $cell) {
                if (!\is_string($cell)) {
                    throw new BadRequestHttpException('El campo '.$name.' debe contener texto.');
                }
            }
        }

        return $value;
    }
}
