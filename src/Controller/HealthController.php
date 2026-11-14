<?php

declare(strict_types=1);

namespace App\Controller;

use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Endpoint de salud para Docker HEALTHCHECK, balanceadores y monitoreo.
 */
final class HealthController
{
    #[Route('/health', name: 'app_health', methods: ['GET'])]
    public function __invoke(Connection $connection): JsonResponse
    {
        try {
            $connection->executeQuery('SELECT 1')->fetchOne();
            $database = 'ok';
        } catch (\Throwable) {
            $database = 'error';
        }

        return new JsonResponse(
            ['status' => 'ok' === $database ? 'ok' : 'degraded', 'php' => \PHP_VERSION, 'database' => $database],
            'ok' === $database ? 200 : 503,
            ['Cache-Control' => 'no-store'],
        );
    }
}
