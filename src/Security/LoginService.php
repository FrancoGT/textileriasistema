<?php

declare(strict_types=1);

namespace App\Security;

use App\Repository\CredentialRepository;
use Psr\Log\LoggerInterface;

final class LoginService
{
    public const FAILURE = 'No se pudo iniciar sesión. Verifique su código y clave e intente nuevamente.';
    public const MIN_LENGTH = 8;
    public const MAX_LENGTH = 64;
    // Hash of a random value. Verifying an unknown user against it takes as long
    // as a real verification, so the response time does not reveal valid codes.
    private const UNKNOWN_USER_HASH = '$2y$13$85ZJWy8c/GUI3iI31F2e3u7soEK933s/5WMs9tuaQwz/reMyBdi0a';

    public function __construct(
        private readonly CredentialRepository $credentials,
        private readonly UserPasswordHasher $hasher,
        private readonly LoggerInterface $logger,
    ) {
    }

    /** @return array{code: string, name: string, must_change: bool} */
    public function authenticate(string $code, #[\SensitiveParameter] string $password): array
    {
        $credential = '' === $code ? null : $this->credentials->find($code);
        if (null === $credential) {
            $this->hasher->verify(self::UNKNOWN_USER_HASH, $password);
            throw new \DomainException(self::FAILURE);
        }
        // The status is checked after the verification, so an inactive user takes as long as an active one.
        if (!$this->hasher->verify($credential['password'], $password) || 'A' !== $credential['status']) {
            throw new \DomainException(self::FAILURE);
        }
        if ($this->hasher->needsRehash($credential['password'])) {
            if ($this->credentials->canStoreHashes()) {
                $this->credentials->rehash($credential['code'], $credential['password'], $this->hasher->hash($password));
            } else {
                $this->logger->warning('E01TUSU.cClave is too short for a hash. Run doctrine:migrations:migrate.');
            }
        }

        // The legacy login asked for a new password when dFecFin was NULL.
        return ['code' => $credential['code'], 'name' => $credential['name'], 'must_change' => null === $credential['end_date']];
    }

    public function changePassword(string $user, #[\SensitiveParameter] string $current, #[\SensitiveParameter] string $new, #[\SensitiveParameter] string $repeated): void
    {
        $credential = $this->credentials->find($user) ?? throw new \DomainException('La sesión no es válida. Inicie sesión nuevamente.');
        if (!$this->hasher->verify($credential['password'], $current)) {
            throw new \DomainException('La clave actual es incorrecta.');
        }
        $length = mb_strlen($new);
        if ($length < self::MIN_LENGTH || $length > self::MAX_LENGTH) {
            throw new \DomainException(\sprintf('La nueva clave debe tener entre %d y %d caracteres.', self::MIN_LENGTH, self::MAX_LENGTH));
        }
        if (!hash_equals($new, $repeated)) {
            throw new \DomainException('La nueva clave y su confirmación no coinciden.');
        }
        if (hash_equals($current, $new)) {
            throw new \DomainException('La nueva clave debe ser distinta de la actual.');
        }
        $this->credentials->changePassword($credential['code'], $credential['password'], $this->hasher->hash($new));
    }
}
