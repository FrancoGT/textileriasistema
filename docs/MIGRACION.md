# Migración a Symfony 8.2 / PHP 8.4.27

## Resumen

| | Antes (`public_html/`) | Ahora |
|---|---|---|
| PHP | 5.3 (hosting compartido) | **8.4.27** |
| Framework | Ninguno (scripts sueltos) | **Symfony 8.2** (referencia: `C:\Users\LENOVO\Desktop\symfony-8.2`) |
| Plantillas | Smarty 3.1.8 (incluido en `Libs/`) | Smarty **4.5** vía Composer + **Twig** para pantallas nuevas |
| Excel | PHPExcel 1.8 (abandonado) | **PhpSpreadsheet 5** vía shim `legacy/class/PHPExcel.php` |
| Base de datos | MariaDB 10.0, credenciales en código | MariaDB **10.11**, credenciales por variables `DATABASE_*` |
| Acceso a BD nuevo | — | **Doctrine DBAL 4 / ORM 3** |
| Despliegue | FTP | **Docker** (`Dockerfile`, `compose.yaml`, `compose.prod.yaml`) |

> Symfony 8.2 está en fase **BETA** (8.2.0-BETA2, 13/11/2026). `composer.json` usa
> `minimum-stability: beta` + `prefer-stable: true`; cuando salga 8.2.0 estable basta con
> `docker compose exec app composer update "symfony/*"` (y luego se puede quitar `minimum-stability`).

## Estrategia: "Strangler Fig"

El sistema tiene ~150 pantallas y ~45 000 líneas de PHP. Reescribirlas todas a la vez es
arriesgado, así que **todas las peticiones pasan por el kernel de Symfony** y:

1. Si existe una ruta Symfony (controlador con `#[Route]`), responde Symfony.
2. Si no, `LegacyController` (prioridad −1000) ejecuta el script correspondiente de `legacy/`.

```
src/
├── Controller/
│   ├── HomeController.php      # "/" e "/index.php": login migrado a Twig (pantalla 1 migrada)
│   ├── HealthController.php    # "/health": chequeo para Docker / monitoreo
│   └── LegacyController.php    # catch-all -> legacy/
└── Legacy/
    ├── LegacyPathResolver.php  # URL -> archivo; bloquea Clases/, Plantillas/, .tpl, .sql, etc.
    └── LegacyScriptRunner.php  # ejecuta el script y convierte su salida en un Response
legacy/                         # copia de public_html/ con correcciones para PHP 8.4
templates/                      # plantillas Twig nuevas
```

`LegacyScriptRunner`:

* hace `chdir()` a `legacy/` (el código usa rutas relativas `Clases/…`, `./Ficheros/…`);
* liga las variables del script a `$GLOBALS` (las funciones legacy usan `global $loSmarty;`);
* captura `echo`, `header()` y `http_response_code()` y los devuelve como `Response`;
* registra los *warnings/notices/deprecations* del legacy en el log en vez de romper la página;
* comparte la sesión nativa de PHP con Symfony (`session.storage.factory.php_bridge`).

Seguridad: antes, cualquier archivo de `public_html/` era accesible por URL (clases, plantillas,
dump SQL, administrador de archivos). Ahora el DocumentRoot es `public/` y solo se sirven
páginas `*.php` de la raíz de `legacy/` y recursos estáticos con extensiones permitidas.

## Cambios aplicados al código legacy (`legacy/`)

| Archivo | Cambio | Motivo |
|---|---|---|
| `Clases/CBase.php` | `CSql::omConnect()` lee `DATABASE_HOST/PORT/USER/PASSWORD/NAME/CHARSET` | Credenciales fijas `localhost/root` |
| `Clases/CBase.php` | `mysqli_report(MYSQLI_REPORT_OFF)` | PHP 8.1+ lanza excepciones; el legacy espera `false` |
| `Clases/CBase.php` | `fetch()`, `omExec()`, `omDisconnect()` toleran conexión/resultados `false` | `TypeError` en PHP 8 (aparecía en `.logs/php_error.log`) |
| `Clases/CBase.php` | Nuevos `begin()`, `commit()`, `rollback()` | `$loSql->rollback()` se invocaba sin existir (error fatal) |
| `Clases/CMtoNormas.php` | `break;` fuera de bucle → `return false;` | Error de compilación en PHP 7+ |
| `Clases/CIncidentes.php` | `$thi->paEstado` → `$this->paEstado` | Error fatal en PHP 8 |
| `Seg4110…Seg4180.php` | `= &new Cezpdf` → `= new Cezpdf` | Sintaxis eliminada en PHP 7 |
| `class/class.ezpdf.php` | `each()` → `key()/current()` | `each()` eliminada en PHP 8 |
| `*.php`, `Clases/*.php` | `count()/sizeof()` → `fxCount()` (184 llamadas) | `count(null)` lanza `TypeError` en PHP 8 |
| `compat/php8.php` | Nuevo: `fxCount()` | Semántica PHP 5 (`null`→0), cargado por Composer |
| `Libs/Smarty.class.php` | Shim: Smarty 4.5 vía Composer | Smarty 3.1.8 no funciona en PHP 8.4 |
| `class/PHPExcel.php` | Shim: `PHPExcel`, `PHPExcel_IOFactory`, `PHPExcel_Style_*` sobre PhpSpreadsheet | PHPExcel no compila en PHP 8 |

Eliminados: Smarty 3.1.8, PHPExcel 1.8, `_file-manager/` (elFinder sin autenticación),
`default.php` (listado de directorios), `diego.php`, `ejemplo_ajax_proceso.php` (pruebas).
Primero se movieron a `docs/legacy-removido/`, y después se eliminaron.

## Archivos eliminados porque ningún código los usa

Una revisión de referencias (includes, `display()` de Smarty, `src`/`href` de las plantillas,
rutas de Symfony y el menú de la base de datos) marcó estos archivos como no usados:

* Los scripts `ERP1000…ERP1094.php` y `Mnu1000.php` de `legacy/`. Las rutas de Symfony responden
  a GET y POST en esas URL, así que esos scripts ya no se ejecutaban. Sus plantillas siguen en
  `legacy/Plantillas/` porque los controladores de Symfony las usan. También se eliminaron las
  clases que solo esos scripts incluían (`CMtoEmpleados`, `CMtoUsuarios`, `CMtoClientes`, ...).
* El subsistema `Seg*` (seguridad operativa: proyectos, cursos, incidentes, licencias...): 80
  scripts, sus plantillas y 41 clases. El menú de la base de datos no lo enlaza, y sus tablas
  `S02*` no existen en la base de datos.
* `ERP7000.php` (no está en el menú y su plantilla no existía), `index.php` y
  `Plantillas/Index.tpl` (reemplazados por `HomeController`) y copias sueltas de páginas
  (`Clases/ERP1070.php`, `Plantillas/ERP1030.php`, `ERP1060.tpl`).
* En `legacy/CSS/`: `contacto-modal/` (otra copia de jQuery 1.8.3 más fancybox, solo para
  `Seg1510a`), copias de jQuery y de `Sixavi.css`, los archivos sin minificar de jQuery UI,
  `jquery.validate.js`, `funciones.js` y `funMarcado.js`.
* `public_html/` (respaldo del código original), `docs/legacy-removido/` y `DO_NOT_UPLOAD_HERE`.

El historial de git conserva todos estos archivos. `node bin/migration-inventory.cjs`
lista los scripts legacy que quedan.

La base de datos: `db/textileria.sql` tenía los identificadores entre comillas simples (SQL
inválido) y un `DEFINER` del hosting anterior. `docker/mysql/convert_dump.py` genera
`docker/mysql/init/01-textileria.sql` válido para MariaDB 10.11.

## Verificación realizada

* `php -l` de todo `legacy/` en PHP 8.4.27: sin errores.
* Recorrido de las 133 páginas con sesión iniciada: 129 responden 200.
  Las 4 restantes fallan por errores **que ya existían** en el código original:

  | Página | Error preexistente |
  |---|---|
  | `ERP7000.php` | No existe `Plantillas/ERP7000.tpl` |
  | `Seg9901.php` | No existe `Plantillas/Sis1000.tpl` |
  | `Seg9010.php` | No existe el método `CMtoUnidad::omInitUnidades()` |
  | `Seg8001.php` | No existe el método `CIncidentes::omCargarHeridos()` |

* Login legacy (`Mnu0000.php`) y menú principal funcionando en dev y prod.

## Riesgos conocidos de PHP 8 (pendientes de pruebas funcionales)

* Aritmética con cadenas vacías/no numéricas (`'' + 1`) lanza `TypeError` en PHP 8.
* Funciones de cadena con `null` emiten *deprecations* (se registran, no rompen).
* `utf8_encode()/utf8_decode()` están deprecadas (siguen existiendo en 8.4).
* Las consultas SQL de las pantallas legacy se arman concatenando `$_REQUEST` → **inyección SQL**.
  El login, el cambio de clave y las pantallas de mantenimiento ya usan DBAL con parámetros.
* Las claves de `E01TUSU` se guardan con `password_hash()` (bcrypt) desde la migración
  `Version20261114000003`. El login acepta todavía una clave en texto plano, por ejemplo de un
  respaldo antiguo, y la reemplaza por un hash cuando el usuario inicia sesión.

## Cómo migrar una pantalla (ejemplo `ERP1000.php`)

1. Crear el controlador; la ruta coincide con la URL antigua, así los menús siguen funcionando:

   ```php
   // src/Controller/EmpleadoController.php
   final class EmpleadoController extends AbstractController
   {
       #[Route('/ERP1000.php', name: 'erp1000')]
       public function index(Connection $db): Response
       {
           $empleados = $db->fetchAllAssociative('SELECT CCODPER, CNOMBRE FROM E01MPER ORDER BY CNOMBRE');

           return $this->render('empleado/index.html.twig', ['empleados' => $empleados]);
       }
   }
   ```

2. Pasar `Plantillas/ERP1000.tpl` a `templates/empleado/index.html.twig`
   (`{$var}` → `{{ var }}`, `{foreach from=$a item=x}` → `{% for x in a %}`).
3. Mover la lógica de `Clases/CMtoEmpleados.php` a un servicio en `src/` con consultas
   parametrizadas (Doctrine DBAL) o entidades Doctrine.
4. Verificar `php bin/console debug:router` y borrar `legacy/ERP1000.php` cuando ya no se use.
