# Despliegue con Docker en Windows

Guía de comandos para levantar **Textilería ERP** (Symfony 8.2 + PHP 8.4.27 + MariaDB 10.11)
con Docker, en local (desarrollo) y en un servidor remoto (producción).

> Todos los comandos son para **PowerShell** en la carpeta del proyecto:
> `C:\proyectos en Symfony\textileriasistema`

---

## 1. Requisitos

| Herramienta | Versión | Notas |
|---|---|---|
| Windows 10/11 | 64 bits | Con **WSL 2** habilitado |
| Docker Desktop | 4.x o superior (Engine 25+) | *Settings → General → Use the WSL 2 based engine* |
| Git (opcional) | cualquiera | Para clonar o versionar el proyecto |

No hace falta instalar PHP, Composer ni MySQL en Windows: todo corre dentro de los contenedores.

Verificar la instalación:

```powershell
docker --version
docker compose version
docker info --format '{{.ServerVersion}}'
```

---

## 2. Arquitectura

```
                 http://localhost:8080
                          │
┌─────────────────────────▼──────────────────────────┐
│ app  (php:8.4.27-apache)                           │
│   public/index.php ── Symfony 8.2 kernel           │
│      ├─ /            HomeController (Twig)         │
│      ├─ /health      HealthController (Doctrine)   │
│      └─ /*.php, CSS/ LegacyController ──► legacy/  │
│                         (Smarty 4, mysqli)         │
└─────────────────────────┬──────────────────────────┘
                          │ red interna Docker
┌─────────────────────────▼──────────────────────────┐
│ db   (mariadb:10.11)   volumen db_data             │
│   docker/mysql/init/01-textileria.sql (1ª vez)     │
└────────────────────────────────────────────────────┘
```

| Archivo | Uso |
|---|---|
| `Dockerfile` | Imagen multi-etapa: `base` → `dev` (local) / `prod` (remoto) |
| `compose.yaml` | Entorno **local** de desarrollo (código montado como volumen) |
| `compose.prod.yaml` | Entorno de **producción** (código dentro de la imagen) |
| `.env.docker.prod.dist` | Plantilla de variables para producción |
| `docker/apache/vhost.conf` | VirtualHost de Apache (DocumentRoot = `public/`) |
| `docker/php/conf.d/*.ini` | Configuración PHP común / dev / prod |
| `docker/entrypoint.sh` | Crea carpetas de escritura, espera la BD, calienta caché |
| `docker/mysql/init/01-textileria.sql` | Esquema y datos iniciales (generado desde `db/textileria.sql`) |

---

## 3. Entorno LOCAL (desarrollo)

### 3.1 Primer arranque

```powershell
# Construir imágenes y levantar app + base de datos en segundo plano
docker compose up -d --build

# Ver el estado (esperar a que "db" esté "healthy")
docker compose ps

# Seguir los logs
docker compose logs -f app
```

La primera vez el contenedor `db` importa automáticamente `docker/mysql/init/01-textileria.sql`
y el contenedor `app` ejecuta `composer install` si falta la carpeta `vendor/`.

En cada arranque el contenedor `app` aplica las migraciones pendientes de `src/Migrations`
(`doctrine:migrations:migrate`). Así una base nueva y una base existente quedan con el mismo
esquema. Si una migración falla, el contenedor se detiene y `docker compose logs app` muestra
el error. Con `RUN_MIGRATIONS=0` el contenedor arranca sin aplicarlas.

| URL | Descripción |
|---|---|
| http://localhost:8080 | Aplicación (inicio de sesión) |
| http://localhost:8080/health | Estado de la app y de la BD (JSON) |
| http://localhost:8080/_profiler | Profiler de Symfony (solo dev) |
| http://localhost:8081 | phpMyAdmin (opcional, ver 3.3) |
| `localhost:3307` | MariaDB desde Windows (HeidiSQL, DBeaver, Workbench…) |

Usuario de prueba incluido en el dump: código **9999**. La clave inicial es el valor que trae el
dump en `E01TUSU`. Después de las migraciones la tabla solo guarda el hash, pero la clave no cambia.

### 3.2 Comandos del día a día

```powershell
# Detener (conserva los datos)
docker compose stop

# Volver a iniciar
docker compose start

# Detener y eliminar contenedores (los volúmenes/datos se conservan)
docker compose down

# Reconstruir la imagen tras cambiar Dockerfile o docker/*
docker compose up -d --build

# Abrir una terminal dentro del contenedor de la app
docker compose exec app bash

# Consola de Symfony
docker compose exec app php bin/console about
docker compose exec app php bin/console debug:router
docker compose exec app php bin/console cache:clear

# Composer
docker compose exec app composer install
docker compose exec app composer require nombre/paquete
docker compose exec app composer update "symfony/*"

# Cliente MariaDB
docker compose exec db mariadb -utextileria -ptextileria textileria
```

### 3.3 phpMyAdmin (opcional)

```powershell
docker compose --profile tools up -d
# http://localhost:8081
docker compose --profile tools stop phpmyadmin
```

### 3.4 Cambiar puertos o credenciales

Crear un archivo `.env` **junto a compose.yaml** no es necesario: `compose.yaml` ya trae valores
por defecto. Para cambiarlos en una sola ejecución:

```powershell
$env:APP_PORT = "8000"; $env:DB_PORT = "3308"; docker compose up -d
Remove-Item Env:APP_PORT, Env:DB_PORT
```

Variables disponibles: `APP_PORT` (8080), `DB_PORT` (3307), `PMA_PORT` (8081),
`DB_NAME`, `DB_USER`, `DB_PASSWORD`, `DB_ROOT_PASSWORD`, `INSTALL_XDEBUG` (0/1).

### 3.5 Xdebug (opcional)

```powershell
$env:INSTALL_XDEBUG = "1"; docker compose build app; Remove-Item Env:INSTALL_XDEBUG
docker compose up -d
```

Configurado con `xdebug.start_with_request=trigger` y `client_host=host.docker.internal` (puerto 9003).

### 3.6 Base de datos: reiniciar, respaldar, restaurar

```powershell
# Volver a importar la BD desde cero (BORRA los datos locales)
docker compose down -v
docker compose up -d

# Respaldo a un archivo .sql en Windows
docker compose exec -T db mariadb-dump -uroot -proot --routines --single-transaction textileria > backup.sql

# Restaurar un respaldo
Get-Content backup.sql -Raw | docker compose exec -T db mariadb -uroot -proot textileria
```

Si se actualiza `db/textileria.sql`, regenerar el script de inicialización (requiere Python 3):

```powershell
python docker/mysql/convert_dump.py db/textileria.sql docker/mysql/init/01-textileria.sql
docker compose down -v; docker compose up -d
```

---

## 4. Entorno de PRODUCCIÓN

La imagen `prod` contiene el código, las dependencias sin `require-dev`, OPcache sin
revalidación, caché de Symfony precalentada y `APP_ENV=prod`. La base de datos **no** se
publica fuera de la red de Docker.

### 4.1 Preparar las variables

```powershell
Copy-Item .env.docker.prod.dist .env.docker.prod
notepad .env.docker.prod
```

Generar un `APP_SECRET` aleatorio:

```powershell
docker run --rm php:8.4.27-cli php -r "echo bin2hex(random_bytes(16)), PHP_EOL;"
```

> `.env.docker.prod` contiene contraseñas: **no** lo suba a git (ya está en `.gitignore`).

### 4.2 Probar producción en la propia PC (Windows)

```powershell
docker compose -f compose.prod.yaml --env-file .env.docker.prod up -d --build
docker compose -f compose.prod.yaml --env-file .env.docker.prod ps
curl.exe http://localhost/health
```

(Si el puerto 80 está ocupado, ponga `APP_PORT=8090` en `.env.docker.prod`.)

### 4.3 Desplegar en un servidor remoto

Hay dos formas. En ambas el servidor (Linux con Docker Engine + plugin compose) necesita
`compose.prod.yaml`, `.env.docker.prod` y la carpeta `docker/mysql/init/`.

#### Opción A — Construir en el servidor (más simple)

```powershell
# Desde Windows: copiar el proyecto al servidor (OpenSSH viene con Windows 10/11)
scp -r "C:\proyectos en Symfony\textileriasistema" usuario@servidor:/opt/textileria

# Conectarse y levantar
ssh usuario@servidor
cd /opt/textileria
docker compose -f compose.prod.yaml --env-file .env.docker.prod up -d --build
```

> Para no copiar `vendor/` ni `var/`, es preferible usar `git clone` en el
> servidor, o comprimir excluyendo esas carpetas.

#### Opción B — Construir en Windows y publicar en un registro (Docker Hub, GHCR, ECR…)

```powershell
# 1. Iniciar sesión en el registro
docker login                       # Docker Hub
# docker login ghcr.io -u USUARIO  # GitHub Container Registry

# 2. Construir la imagen de producción para Linux x86_64 y etiquetarla
docker build --target prod --platform linux/amd64 -t usuario/textileria-app:1.0.0 .

# 3. Publicarla
docker push usuario/textileria-app:1.0.0
```

En el servidor, con `APP_IMAGE=usuario/textileria-app` y `APP_TAG=1.0.0` en `.env.docker.prod`:

```bash
docker compose -f compose.prod.yaml --env-file .env.docker.prod pull app
docker compose -f compose.prod.yaml --env-file .env.docker.prod up -d --no-build
```

### 4.4 Actualizar a una nueva versión

Antes de actualizar, haga un respaldo de la base (ver 4.5). El contenedor `app` aplica las
migraciones al arrancar y estas no se pueden revertir: la versión que cifra las claves de
`E01TUSU` no puede recuperar las claves originales.

```bash
# (Opción A) en el servidor
git pull   # o copiar los archivos nuevos
docker compose -f compose.prod.yaml --env-file .env.docker.prod up -d --build app

# (Opción B) cambiar APP_TAG en .env.docker.prod y:
docker compose -f compose.prod.yaml --env-file .env.docker.prod pull app
docker compose -f compose.prod.yaml --env-file .env.docker.prod up -d --no-build app
```

Los volúmenes `db_data`, `app_ficheros`, `app_files`, `app_sessions` y `app_log` se conservan
entre versiones (documentos generados, archivos subidos, sesiones y logs).

### 4.5 Operación en producción

```bash
# Estado y salud
docker compose -f compose.prod.yaml --env-file .env.docker.prod ps
curl http://localhost/health

# Logs de Apache/PHP y de Symfony
docker compose -f compose.prod.yaml --env-file .env.docker.prod logs -f app
docker compose -f compose.prod.yaml --env-file .env.docker.prod exec app tail -f var/log/prod.log

# Respaldo de la BD
docker compose -f compose.prod.yaml --env-file .env.docker.prod exec -T db \
  sh -c 'mariadb-dump -uroot -p"$MARIADB_ROOT_PASSWORD" --single-transaction textileria' > backup_$(date +%F).sql
```

### 4.6 HTTPS

El contenedor escucha HTTP en el puerto 80. Para HTTPS, ponga delante un proxy inverso
(Nginx, Caddy, Traefik o el balanceador del proveedor cloud) y defina en `.env.docker.prod`
`TRUSTED_PROXIES` con la IP del proxy, y `DEFAULT_URI=https://su-dominio`.

---

## 5. Solución de problemas

| Síntoma | Causa / Solución |
|---|---|
| `dependency failed to start: container ... db ... is unhealthy` | Error al importar el SQL. Ver `docker compose logs db`. Corregir y ejecutar `docker compose down -v; docker compose up -d`. |
| `Bind for 0.0.0.0:8080 failed: port is already allocated` | Puerto ocupado: `$env:APP_PORT="8000"; docker compose up -d`. |
| `/health` responde `503` / `"database":"error"` | La BD no está lista o las credenciales `DATABASE_*` no coinciden con `DB_*`. |
| Página en blanco o error 500 en una pantalla legacy | `docker compose exec app tail -n 50 var/log/dev.log` (en prod: `var/log/prod.log`). |
| Lentitud en Windows | Normal con *bind mounts*. `var/` ya va en un volumen nativo. Mover el proyecto dentro de WSL (`\\wsl$\...`) acelera mucho. |
| `docker-entrypoint: not found` / `\r` | Archivos con fin de línea CRLF: el Dockerfile ya los normaliza; si persiste, `git config core.autocrlf input`. |
| Cambios en `.ini`, `vhost.conf` o `Dockerfile` no se aplican | Reconstruir: `docker compose up -d --build`. |
| El contenedor `app` se reinicia y el log dice `doctrine:migrations:migrate` | Una migración falló. Ver `docker compose logs app`. Si dice que un código de usuario aparece en más de una fila de `E01TUSU`, elimine las filas sobrantes y reinicie. |
| Tablas "no existen" (`E01TUSU` vs `e01tusu`) | La BD debe arrancar con `--lower-case-table-names=1` (ya está en los compose). Solo se aplica a un volumen nuevo: `docker compose down -v`. |
