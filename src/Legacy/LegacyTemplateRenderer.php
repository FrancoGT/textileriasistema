<?php

declare(strict_types=1);

namespace App\Legacy;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Response;

final class LegacyTemplateRenderer
{
    public function __construct(#[Autowire('%app.legacy_dir%')] private readonly string $directory)
    {
    }

    public function render(string $template, array $data, int $status = 200): Response
    {
        $smarty = new \Smarty();
        $smarty->setTemplateDir($this->directory);
        $smarty->setCompileDir($this->directory.'/templates_c');
        $smarty->setEscapeHtml(true);
        $smarty->assign($data);

        return new Response($smarty->fetch($template, null, 'symfony-maintenance'), $status, ['Cache-Control' => 'private, no-store']);
    }
}
