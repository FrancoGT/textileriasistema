<?php

declare(strict_types=1);

namespace App\Legacy;

use Symfony\Component\HttpFoundation\Response;

interface LegacyPageExecutor
{
    public function run(string $script): Response;
}
