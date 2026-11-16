<?php

declare(strict_types=1);

namespace App\Session;

final class ErpSession
{
    public function user(): string
    {
        if (\PHP_SESSION_ACTIVE !== session_status()) {
            session_start();
        }

        return (string) ($_SESSION['gcCodUsu'] ?? '');
    }

    public function get(string $key): mixed
    {
        $this->user();

        return $_SESSION[$key] ?? null;
    }

    public function set(string $key, mixed $value): void
    {
        $this->user();
        $_SESSION[$key] = $value;
    }

    public function verifyToken(string $scope, string $token): bool
    {
        $stored = $this->get('_maintenance_token_'.$scope);

        return \is_string($stored) && '' !== $token && hash_equals($stored, $token);
    }

    public function token(string $scope): string
    {
        $key = '_maintenance_token_'.$scope;
        $token = $this->get($key);
        if (!\is_string($token)) {
            $token = bin2hex(random_bytes(32));
            $this->set($key, $token);
        }

        return $token;
    }

    /** Stores the legacy identity in a new session id, which prevents session fixation. */
    public function login(string $user, string $name): void
    {
        $this->logout();
        session_regenerate_id(true);
        $_SESSION['gcCodUsu'] = $user;
        $_SESSION['gcNombre'] = $name;
    }

    public function logout(): void
    {
        // The user identity is stored in the native session, outside the Symfony session attributes.
        if (\PHP_SESSION_ACTIVE !== session_status()) {
            session_start();
        }

        unset($_SESSION['gcNombre'], $_SESSION['gcCodUsu'], $_SESSION['gcTipo'], $_SESSION['login_change_password']);
        foreach (array_keys($_SESSION) as $key) {
            if (str_starts_with((string) $key, '_maintenance_')) {
                unset($_SESSION[$key]);
            }
        }
    }
}
