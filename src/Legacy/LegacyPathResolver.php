<?php

declare(strict_types=1);

namespace App\Legacy;

use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Traduce una ruta de URL (p.ej. "ERP1000.php", "CSS/Sixavi.css") a un archivo
 * dentro de legacy/, aplicando una lista de bloqueo para que nunca se expongan
 * clases, plantillas, configuracion ni archivos internos.
 */
final class LegacyPathResolver
{
    /** Extensiones servidas como archivo estatico */
    public const STATIC_EXTENSIONS = [
        'css', 'js', 'map', 'png', 'jpg', 'jpeg', 'gif', 'bmp', 'ico', 'svg', 'webp',
        'woff', 'woff2', 'ttf', 'eot', 'otf', 'html', 'htm',
        'pdf', 'doc', 'docx', 'xls', 'xlsx', 'rtf', 'csv', 'zip',
    ];

    /** Solo los scripts en la raiz de legacy/ son "paginas" ejecutables */
    private const PAGE_PATTERN = '#^[A-Za-z0-9_\-]+\.php$#';

    /** Rutas que nunca se sirven (ni se ejecutan) */
    private const DENY_PATTERNS = [
        '#(^|/)\.#',                                                         // ocultos (.htaccess, .idea...)
        '#^(Clases|class|Libs|configs|Plantillas|templates|templates_c|cache|Xls|libFPDF)(/|$)#i',
        '#\.(tpl|sql|txt|log|ini|inc|bak|orig|rar|php~|css~)$#i',
        '#~$#',
    ];

    public function __construct(
        #[Autowire('%app.legacy_dir%')]
        private readonly string $legacyDir,
    ) {
    }

    /**
     * Devuelve la ruta absoluta del script PHP o null si no es una pagina legacy valida.
     */
    public function resolvePage(string $path): ?string
    {
        $path = ltrim($path, '/');
        if (!preg_match(self::PAGE_PATTERN, $path) || $this->isDenied($path)) {
            return null;
        }

        return $this->realFile($path);
    }

    /**
     * Devuelve la ruta absoluta de un recurso estatico o null.
     */
    public function resolveAsset(string $path): ?string
    {
        $path = ltrim(rawurldecode($path), '/');
        $extension = strtolower(pathinfo($path, \PATHINFO_EXTENSION));
        if (!\in_array($extension, self::STATIC_EXTENSIONS, true) || $this->isDenied($path)) {
            return null;
        }

        return $this->realFile($path);
    }

    private function isDenied(string $path): bool
    {
        if (str_contains($path, '..') || str_contains($path, "\0")) {
            return true;
        }
        foreach (self::DENY_PATTERNS as $pattern) {
            if (preg_match($pattern, $path)) {
                return true;
            }
        }

        return false;
    }

    private function realFile(string $path): ?string
    {
        $base = realpath($this->legacyDir);
        $file = realpath($this->legacyDir.'/'.$path);
        if (false === $base || false === $file || !is_file($file)) {
            return null;
        }
        // Defensa extra contra path traversal / enlaces simbolicos
        if (!str_starts_with($file, $base.\DIRECTORY_SEPARATOR)) {
            return null;
        }

        return $file;
    }
}
