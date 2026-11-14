<?php

declare(strict_types=1);

namespace App\Legacy;

final class LegacySession
{
    public function logout(): void
    {
        // Legacy identity is stored outside Symfony's isolated session attributes.
        if (\PHP_SESSION_ACTIVE !== session_status()) {
            session_start();
        }

        unset($_SESSION['gcNombre'], $_SESSION['gcCodUsu'], $_SESSION['gcTipo'], $_SESSION['login_change_password']);
    }
}
