<?php

declare(strict_types=1);

namespace App\Security;

use Symfony\Component\PasswordHasher\Hasher\NativePasswordHasher;
use Symfony\Component\PasswordHasher\PasswordHasherInterface;

/**
 * Hashes passwords with password_hash() and still accepts the plain-text
 * values that the legacy screens stored in E01TUSU.cClave. needsRehash() is
 * true for a plain-text value, so a successful login replaces it with a hash.
 */
final class UserPasswordHasher implements PasswordHasherInterface
{
    private readonly NativePasswordHasher $hasher;

    public function __construct()
    {
        $this->hasher = new NativePasswordHasher();
    }

    public static function isPlainText(string $stored): bool
    {
        return null === password_get_info($stored)['algo'];
    }

    public function hash(#[\SensitiveParameter] string $plainPassword): string
    {
        return $this->hasher->hash($plainPassword);
    }

    public function verify(string $hashedPassword, #[\SensitiveParameter] string $plainPassword): bool
    {
        if ('' === $hashedPassword || '' === $plainPassword) {
            return false;
        }
        if (self::isPlainText($hashedPassword)) {
            return hash_equals($hashedPassword, $plainPassword);
        }

        return $this->hasher->verify($hashedPassword, $plainPassword);
    }

    public function needsRehash(string $hashedPassword): bool
    {
        return self::isPlainText($hashedPassword) || $this->hasher->needsRehash($hashedPassword);
    }
}
