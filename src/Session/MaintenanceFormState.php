<?php

declare(strict_types=1);

namespace App\Legacy;

use Symfony\Component\DependencyInjection\Attribute\Exclude;

/**
 * Form token, selected record and status message of one maintenance screen.
 * The values live in the native session, which the legacy screens share.
 */
#[Exclude]
final class MaintenanceFormState
{
    public function __construct(private readonly LegacySession $session, private readonly string $scope)
    {
    }

    public function token(): string
    {
        return $this->session->token($this->scope);
    }

    public function isValidToken(string $token): bool
    {
        return $this->session->verifyToken($this->scope, $token);
    }

    public function open(?string $code): void
    {
        $this->session->set($this->key('edit'), ['code' => $code]);
    }

    public function close(): void
    {
        $this->session->set($this->key('edit'), null);
    }

    /**
     * Returns the code opened by Nuevo (null) or Editar, and rejects a save
     * whose submitted code differs from it.
     */
    public function openedCode(string $submittedCode): ?string
    {
        $edit = $this->session->get($this->key('edit'));
        if (!\is_array($edit) || !\array_key_exists('code', $edit)) {
            throw new \DomainException('Seleccione un registro o pulse Nuevo antes de grabar.');
        }
        if (($edit['code'] ?? '') !== $submittedCode) {
            throw new \DomainException('El registro seleccionado cambió. Vuelva a abrir el formulario.');
        }

        return $edit['code'];
    }

    public function remember(string $name, ?string $value): void
    {
        $this->session->set($this->key($name), $value);
    }

    public function recall(string $name): ?string
    {
        $value = $this->session->get($this->key($name));

        return \is_string($value) ? $value : null;
    }

    public function flash(string $message): void
    {
        $this->session->set($this->key('message'), $message);
    }

    public function takeFlash(): string
    {
        $message = (string) ($this->session->get($this->key('message')) ?? '');
        $this->session->set($this->key('message'), null);

        return $message;
    }

    private function key(string $name): string
    {
        return '_maintenance_'.$this->scope.'_'.$name;
    }
}
