#!/bin/sh
set -e

cd /var/www/html

# Directorios de escritura (cache Symfony, sesiones, Smarty compilado, archivos generados)
mkdir -p var/cache var/log var/sessions \
         legacy/Ficheros legacy/FILES legacy/templates_c legacy/cache

# En desarrollo el codigo se monta como volumen: instalar dependencias si faltan
if [ "${APP_ENV:-prod}" != "prod" ] && [ ! -f vendor/autoload_runtime.php ]; then
    echo "[entrypoint] Instalando dependencias de Composer..."
    composer install --prefer-dist --no-progress --no-interaction
fi

# Esperar a la base de datos (max ~60s) usando DATABASE_HOST/PORT
if [ -n "${DATABASE_HOST}" ]; then
    echo "[entrypoint] Esperando a la base de datos ${DATABASE_HOST}:${DATABASE_PORT:-3306}..."
    i=0
    until php -r '
        mysqli_report(MYSQLI_REPORT_OFF);
        $c = @mysqli_connect(getenv("DATABASE_HOST"), getenv("DATABASE_USER"), getenv("DATABASE_PASSWORD"), "", (int)(getenv("DATABASE_PORT") ?: 3306));
        exit($c ? 0 : 1);' ; do
        i=$((i+1))
        if [ "$i" -ge 30 ]; then echo "[entrypoint] BD no disponible, continuando de todos modos."; break; fi
        sleep 2
    done
fi

# Migraciones de esquema (src/Migrations). Si fallan, el contenedor se detiene:
# servir la aplicacion con un esquema viejo truncaria las claves cifradas.
# RUN_MIGRATIONS=0 las omite, por ejemplo para aplicarlas a mano en una ventana de mantenimiento.
if [ "${RUN_MIGRATIONS:-1}" = "1" ]; then
    echo "[entrypoint] Aplicando migraciones de base de datos..."
    php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration
fi

if [ "${APP_ENV:-prod}" = "prod" ]; then
    php bin/console cache:warmup --no-interaction || true
fi

# Permisos para Apache (www-data). En Windows (bind mount) chown no tiene efecto, no importa.
chown -R www-data:www-data var legacy/Ficheros legacy/FILES legacy/templates_c legacy/cache 2>/dev/null || true
setfacl -R -m u:www-data:rwX -m u:"$(whoami)":rwX var 2>/dev/null || true

exec docker-php-entrypoint "$@"
