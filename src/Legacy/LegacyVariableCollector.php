<?php

declare(strict_types=1);

namespace App\Legacy;

final class LegacyVariableCollector
{
    private const RESERVED_NAMES = [
        'this', 'GLOBALS', '_GET', '_POST', '_REQUEST', '_SERVER', '_COOKIE', '_FILES', '_ENV', '_SESSION',
        'http_response_header', 'argc', 'argv',
    ];

    /** @var array<string, list<string>> */
    private array $cache = [];

    /** @return list<string> */
    public function collect(string $script): array
    {
        if (isset($this->cache[$script])) {
            return $this->cache[$script];
        }

        $names = [];
        foreach (token_get_all((string) file_get_contents($script)) as $token) {
            if (\is_array($token) && \T_VARIABLE === $token[0]) {
                $names[substr($token[1], 1)] = true;
            }
        }
        foreach (self::RESERVED_NAMES as $reserved) {
            unset($names[$reserved]);
        }

        $names = array_keys(array_filter($names, static fn ($name) => !str_starts_with((string) $name, '__legacy'), \ARRAY_FILTER_USE_KEY));

        return $this->cache[$script] = array_map('strval', $names);
    }
}
