<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Session\ErpSession;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

#[AsEventListener(priority: -15)]
final class SelectSearchListener
{
    public function __construct(private readonly ErpSession $session)
    {
    }

    public function __invoke(ResponseEvent $event): void
    {
        $response = $event->getResponse();
        if (!$event->isMainRequest() || $response->isRedirection()
            || $response->headers->has('Content-Disposition')
            || !str_contains($response->headers->get('Content-Type', ''), 'text/html')) {
            return;
        }
        $html = $response->getContent();
        if (!is_string($html) || !str_contains(strtolower($html), '<select') || '' === $this->session->user()) {
            return;
        }
        $lists = $this->session->get('_maintenance_select_lists') ?? [];
        $html = preg_replace_callback('/<select\b[^>]*>[\s\S]*?<\/select>/i', static function (array $match) use (&$lists): string {
            $document = \Dom\HTMLDocument::createFromString('<!DOCTYPE html><html><head><meta charset="UTF-8"></head><body>'.$match[0].'</body></html>', LIBXML_NOERROR);
            $options = [];
            foreach ($document->getElementsByTagName('option') as $index => $option) {
                if ($option->hasAttribute('disabled') || $option->parentElement?->hasAttribute('disabled')) {
                    continue;
                }
                $options[] = ['index' => $index, 'value' => $option->getAttribute('value') ?? $option->textContent, 'label' => trim($option->textContent)];
            }
            $key = bin2hex(random_bytes(16));
            $lists[$key] = ['created' => time(), 'options' => $options];

            return preg_replace('/<select\b/i', '<select data-search-key="'.$key.'"', $match[0], 1);
        }, $html);
        $lists = array_filter($lists, static fn (array $list): bool => $list['created'] >= time() - 7200);
        $this->session->set('_maintenance_select_lists', array_slice($lists, -300, null, true));
        $assets = '<link rel="stylesheet" href="/css/select-search.css"><script src="/js/select-search.js" defer></script>';
        $response->setContent(str_ireplace('</head>', $assets.'</head>', $html));
        $response->headers->set('Cache-Control', 'private, no-store');
    }
}
