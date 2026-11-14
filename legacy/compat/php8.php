<?php
/**
 * Funciones de compatibilidad PHP 5 -> PHP 8.4 para el codigo legacy.
 * Se cargan siempre via Composer ("autoload.files").
 */

if (!function_exists('fxCount')) {
    /**
     * count()/sizeof() con la semantica de PHP 5/7:
     * null -> 0, escalar -> 1 (en PHP 8 ambos lanzan TypeError).
     */
    function fxCount($p_xValue, int $p_nMode = COUNT_NORMAL): int
    {
        if (is_array($p_xValue) || $p_xValue instanceof Countable) {
            return count($p_xValue, $p_nMode);
        }

        return null === $p_xValue ? 0 : 1;
    }
}

if (!function_exists('utf8_decode_compat')) {
    // utf8_encode/utf8_decode estan deprecadas desde PHP 8.2 (siguen existiendo en 8.4).
    // Si se eliminan en PHP 9, definir aqui equivalentes con mb_convert_encoding().
}
