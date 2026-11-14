<?php

declare(strict_types=1);

namespace App\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

#[AsEventListener(priority: -10)]
final class InterfaceStylesListener
{
    public function __invoke(ResponseEvent $event): void
    {
        $response = $event->getResponse();
        if (!$event->isMainRequest() || $response->isRedirection()
            || $response->headers->has('Content-Disposition')
            || !str_contains($response->headers->get('Content-Type', ''), 'text/html')) {
            return;
        }

        $html = $response->getContent();
        if (false === $html || !str_contains(strtolower($html), '</head>')) {
            return;
        }

        $assets = '<link rel="stylesheet" href="/css/ui-feedback.css" media="screen">';
        if (!str_contains($html, '/js/ui-feedback.js')) {
            $assets .= '<script src="/js/ui-feedback.js" defer></script>';
        }
        if (!preg_match('/name=["\']viewport["\']/i', $html)) {
            $assets .= '<meta name="viewport" content="width=device-width, initial-scale=1">';
        }
        $response->setContent(preg_replace('/<\/head>/i', $assets.'</head>', $html, 1));
    }
}
