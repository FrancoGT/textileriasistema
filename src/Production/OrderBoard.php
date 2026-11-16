<?php

declare(strict_types=1);

namespace App\Production;

use Symfony\Component\DependencyInjection\Attribute\Exclude;

/**
 * One order list screen. $canOpen tells whether the user can open an order
 * to plan it or to assign work; the other boards are reports.
 */
#[Exclude]
final readonly class OrderBoard
{
    public function __construct(
        public string $screen,
        public string $permission,
        public string $title,
        public OrderFilter $filter,
        public bool $canOpen,
    ) {
    }
}
