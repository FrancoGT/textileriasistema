<?php

declare(strict_types=1);

namespace App\Legacy;

use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Response;

/**
 * Ejecuta un script PHP legacy (carpeta legacy/) dentro del kernel de Symfony
 * y convierte su salida (echo, header(), http_response_code()) en un Response.
 *
 * Patron "strangler fig": cada pantalla legacy sigue funcionando sin cambios
 * mientras se migra, una por una, a controladores Symfony + Twig + Doctrine.
 */
final class LegacyScriptRunner
{
    /** Errores no fatales que el codigo legacy genera de forma rutinaria en PHP 8.4 */
    private const TOLERATED_ERRORS = E_WARNING | E_NOTICE | E_USER_WARNING | E_USER_NOTICE | E_DEPRECATED | E_USER_DEPRECATED;

    /** Cabeceras que no deben copiarse al Response */
    private const SKIPPED_HEADERS = ['x-powered-by'];

    /** Variables que nunca se convierten en globales */
    private const RESERVED_VARS = [
        'this', 'GLOBALS', '_GET', '_POST', '_REQUEST', '_SERVER', '_COOKIE', '_FILES', '_ENV', '_SESSION',
        'http_response_header', 'argc', 'argv',
    ];

    /** @var array<string, list<string>> */
    private array $variableCache = [];

    public function __construct(
        #[Autowire('%app.legacy_dir%')]
        private readonly string $legacyDir,
        private readonly ?LoggerInterface $logger = null,
    ) {
    }

    public function getLegacyDir(): string
    {
        return $this->legacyDir;
    }

    public function run(string $script): Response
    {
        $previousCwd = getcwd();
        $previousIncludePath = get_include_path();
        $bufferLevel = ob_get_level();

        // El legacy usa rutas relativas ('Clases/...', './Ficheros/...', 'Plantillas/...')
        chdir($this->legacyDir);
        set_include_path($this->legacyDir.\PATH_SEPARATOR.$previousIncludePath);
        mysqli_report(\MYSQLI_REPORT_OFF);

        set_error_handler(function (int $type, string $message, string $file, int $line): bool {
            $this->logger?->info('[legacy] {message} en {file}:{line}', [
                'message' => $message,
                'file' => str_replace($this->legacyDir, 'legacy', $file),
                'line' => $line,
            ]);

            return true; // silenciar: el legacy depende de warnings/notices "silenciosos"
        }, self::TOLERATED_ERRORS);

        ob_start();
        try {
            self::requireInGlobalScope($script, $this->collectVariables($script));
        } catch (\Throwable $e) {
            while (ob_get_level() > $bufferLevel) {
                ob_end_clean();
            }
            throw $e;
        } finally {
            restore_error_handler();
            set_include_path($previousIncludePath);
            if (false !== $previousCwd) {
                chdir($previousCwd);
            }
        }

        $content = '';
        while (ob_get_level() > $bufferLevel) {
            $content = ob_get_clean().$content;
        }

        return $this->buildResponse($content);
    }

    /**
     * Incluye el script con sus variables "de nivel superior" ligadas a $GLOBALS,
     * porque el legacy declara `global $loSmarty;` dentro de sus funciones.
     *
     * @param list<string> $__legacyVars
     */
    private static function requireInGlobalScope(string $__legacyScript, array $__legacyVars): void
    {
        foreach ($__legacyVars as $__legacyVar) {
            global $$__legacyVar;
        }
        unset($__legacyVar);

        require $__legacyScript;
    }

    /**
     * @return list<string>
     */
    private function collectVariables(string $script): array
    {
        if (isset($this->variableCache[$script])) {
            return $this->variableCache[$script];
        }

        $names = [];
        foreach (token_get_all((string) file_get_contents($script)) as $token) {
            if (\is_array($token) && \T_VARIABLE === $token[0]) {
                $names[substr($token[1], 1)] = true;
            }
        }
        foreach (self::RESERVED_VARS as $reserved) {
            unset($names[$reserved]);
        }
        $names = array_keys(array_filter($names, static fn ($k) => !str_starts_with((string) $k, '__legacy'), \ARRAY_FILTER_USE_KEY));

        return $this->variableCache[$script] = array_map('strval', $names);
    }

    private function buildResponse(string $content): Response
    {
        $status = http_response_code();
        $response = new Response($content, \is_int($status) && $status > 0 ? $status : Response::HTTP_OK);

        if (!headers_sent()) {
            foreach (headers_list() as $header) {
                [$name, $value] = array_map('trim', explode(':', $header, 2)) + [1 => ''];
                if (\in_array(strtolower($name), self::SKIPPED_HEADERS, true)) {
                    continue;
                }
                // Set-Cookie puede repetirse; el resto reemplaza los valores por defecto de Symfony
                $response->headers->set($name, $value, 'set-cookie' !== strtolower($name));
            }
            header_remove();
            http_response_code(Response::HTTP_OK);
        }

        if (!$response->headers->has('Content-Type')) {
            $response->headers->set('Content-Type', 'text/html; charset=UTF-8');
        }

        return $response;
    }
}
