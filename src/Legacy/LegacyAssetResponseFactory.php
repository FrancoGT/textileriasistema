<?php

declare(strict_types=1);

namespace App\Legacy;

use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mime\MimeTypes;

final class LegacyAssetResponseFactory
{
    public function create(Request $request, string $path, string $file): BinaryFileResponse
    {
        $response = new BinaryFileResponse($file, autoEtag: true, autoLastModified: true);
        $extension = strtolower(pathinfo($file, \PATHINFO_EXTENSION));
        $response->headers->set('Content-Type', MimeTypes::getDefault()->getMimeTypes($extension)[0] ?? 'application/octet-stream');
        if (preg_match('#^/?(Ficheros|FILES)/#i', $path)) {
            $response->setPrivate()->headers->addCacheControlDirective('no-store');
        } else {
            $response->setPublic()->setMaxAge(86400);
        }
        $response->isNotModified($request);

        return $response;
    }
}
