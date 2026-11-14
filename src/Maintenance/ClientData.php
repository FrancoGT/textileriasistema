<?php

declare(strict_types=1);

namespace App\Maintenance;

use Symfony\Component\DependencyInjection\Attribute\Exclude;

#[Exclude]
final readonly class ClientData
{
    public function __construct(
        public ClientType $type,
        public string $name,
        public string $dni,
        public string $ruc,
        public string $address,
        public string $phone,
        public string $status,
        public string $ciiu,
        public string $acronym,
        public string $priority,
    ) {
    }
}
