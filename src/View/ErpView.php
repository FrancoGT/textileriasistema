<?php

declare(strict_types=1);

namespace App\View;

use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

/**
 * Renders one ERP screen from templates/erp/. The screens show user data, so
 * browsers and proxies must not store them.
 */
final class ErpView
{
    public function __construct(private readonly Environment $twig)
    {
    }

    /** @param array<string, mixed> $data */
    public function render(string $screen, array $data, int $status = 200): Response
    {
        return new Response($this->twig->render('erp/'.$screen.'.html.twig', $data), $status, ['Cache-Control' => 'private, no-store']);
    }
}
