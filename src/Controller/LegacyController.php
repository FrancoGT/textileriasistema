<?php

declare(strict_types=1);

namespace App\Controller;

use App\Legacy\LegacyPathResolver;
use App\Legacy\LegacyPageExecutor;
use App\Legacy\LegacyAssetResponseFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Ruta "catch-all" (prioridad minima): cualquier URL que no tenga un controlador
 * Symfony propio se resuelve contra la aplicacion legacy en legacy/.
 *
 * Para migrar una pantalla basta con crear un controlador Symfony con la misma
 * ruta (p.ej. #[Route('/ERP1000.php')]): tendra prioridad sobre este.
 */
final class LegacyController
{
    public function __construct(
        private readonly LegacyPathResolver $resolver,
        private readonly LegacyPageExecutor $runner,
        private readonly LegacyAssetResponseFactory $assets,
    ) {
    }

    #[Route('/{path}', name: 'legacy', requirements: ['path' => '.+'], priority: -1000)]
    public function __invoke(Request $request, string $path): Response
    {
        if ($script = $this->resolver->resolvePage($path)) {
            return $this->runner->run($script);
        }

        if ($asset = $this->resolver->resolveAsset($path)) {
            return $this->assets->create($request, $path, $asset);
        }

        throw new NotFoundHttpException(\sprintf('Recurso legacy no encontrado: "%s".', $path));
    }
}
