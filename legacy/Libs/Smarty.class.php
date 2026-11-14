<?php
/**
 * Shim de compatibilidad.
 *
 * Smarty 3.1.8 (incluido originalmente en esta carpeta) no es compatible con PHP 8.4.
 * Ahora Smarty se instala con Composer (smarty/smarty ^4.5) y la clase global
 * \Smarty se carga mediante el autoloader de Composer que Symfony ya registro.
 *
 * Se mantiene este archivo para no tener que modificar los ~150
 * `require_once 'Libs/Smarty.class.php'` del codigo legacy.
 */
if (!class_exists('Smarty')) {
    throw new RuntimeException('Smarty no esta disponible: ejecute "composer install".');
}
