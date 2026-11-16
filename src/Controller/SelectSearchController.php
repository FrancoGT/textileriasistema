<?php

declare(strict_types=1);

namespace App\Controller;

use App\Session\ErpSession;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class SelectSearchController
{
    #[Route('/api/select-search', name: 'app_select_search', methods: ['GET'])]
    public function __invoke(Request $request, ErpSession $session): JsonResponse
    {
        $headers = ['Cache-Control' => 'private, no-store'];
        if ('' === $session->user()) {
            return new JsonResponse(['error' => 'La sesión ha vencido.'], 401, $headers);
        }
        $list = ($session->get('_maintenance_select_lists') ?? [])[$request->query->getString('key')] ?? null;
        if (null === $list || $list['created'] < time() - 7200) {
            return new JsonResponse(['error' => 'Recargue el panel para buscar.'], 410, $headers);
        }
        $normalize = static fn (string $text): string => strtolower(iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text) ?: $text);
        $query = $normalize(substr($request->query->getString('q'), 0, 200));
        $options = array_values(array_filter($list['options'], static fn (array $option): bool => str_contains($normalize($option['label'].' '.$option['value']), $query)));

        return new JsonResponse(['results' => array_slice($options, 0, 50), 'more' => count($options) > 50], 200, $headers);
    }
}
