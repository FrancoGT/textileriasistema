<?php

declare(strict_types=1);

namespace App\Controller;

use App\Legacy\LegacyPathResolver;
use App\Legacy\LegacyScriptRunner;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Mime\MimeTypes;
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
        private readonly LegacyScriptRunner $runner,
    ) {
    }

    #[Route('/{path}', name: 'legacy', requirements: ['path' => '.+'], priority: -1000)]
    public function __invoke(Request $request, string $path): Response
    {
        if ($script = $this->resolver->resolvePage($path)) {
            return $this->runner->run($script);
        }

        if ($asset = $this->resolver->resolveAsset($path)) {
            $response = new BinaryFileResponse($asset, autoEtag: true, autoLastModified: true);
            // MIME por extension (fileinfo detecta CSS/JS como text/plain)
            $extension = strtolower(pathinfo($asset, \PATHINFO_EXTENSION));
            $response->headers->set('Content-Type', MimeTypes::getDefault()->getMimeTypes($extension)[0] ?? 'application/octet-stream');
            // Documentos generados (Ficheros/) no deben cachearse; CSS/JS/imagenes si
            if (preg_match('#^/?(Ficheros|FILES)/#i', $path)) {
                $response->setPrivate()->headers->addCacheControlDirective('no-store');
            } else {
                $response->setPublic()->setMaxAge(86400);
            }
            $response->isNotModified($request);

            return $response;
        }

        throw new NotFoundHttpException(\sprintf('Recurso legacy no encontrado: "%s".', $path));
    }
}
