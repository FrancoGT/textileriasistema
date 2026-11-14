<?php

declare(strict_types=1);

namespace App\Legacy;

use Symfony\Component\HttpFoundation\Response;

final class LegacyResponseFactory
{
    /** @param list<string> $headers */
    public function create(string $content, int $status, array $headers): Response
    {
        $response = new Response($content, $status);
        foreach ($headers as $header) {
            [$name, $value] = array_map('trim', explode(':', $header, 2)) + [1 => ''];
            if ('x-powered-by' === strtolower($name)) {
                continue;
            }
            $response->headers->set($name, $value, 'set-cookie' !== strtolower($name));
        }
        if (!$response->headers->has('Content-Type')) {
            $response->headers->set('Content-Type', 'text/html; charset=UTF-8');
        }

        return $response;
    }
}
