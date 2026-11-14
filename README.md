# Textilería ERP

Sistema ERP textil (personal, clientes, pedidos, producción, logística, cuentas por cobrar)
migrado a **Symfony 8.2** sobre **PHP 8.4.27**, empaquetado con **Docker**.

## Inicio rápido (Windows + Docker Desktop)

```powershell
cd "C:\proyectos en Symfony\textileriasistema"
docker compose up -d --build
```

* Aplicación: http://localhost:8080
* Salud: http://localhost:8080/health
* MariaDB: `localhost:3307` (usuario `textileria` / `textileria`)

Test user credentials:

* Username: `9999`
* Password: `999999`

Producción:

```powershell
Copy-Item .env.docker.prod.dist .env.docker.prod   # editar contraseñas y APP_SECRET
docker compose -f compose.prod.yaml --env-file .env.docker.prod up -d --build
```

## Documentación

* [docs/ARCHITECTURE.md](docs/ARCHITECTURE.md): service responsibilities, migration boundaries and verification commands.

* [docs/DESPLIEGUE-DOCKER-WINDOWS.md](docs/DESPLIEGUE-DOCKER-WINDOWS.md) — todos los comandos
  de Docker para Windows: local, producción, despliegue remoto, respaldos y solución de problemas.
* [docs/MIGRACION.md](docs/MIGRACION.md) — qué se migró, cambios al código legacy,
  verificación realizada y cómo migrar cada pantalla a Symfony.

## Estructura

```
├── Dockerfile               # PHP 8.4.27 + Apache, etapas base/dev/prod
├── compose.yaml             # desarrollo local
├── compose.prod.yaml        # producción
├── config/                  # configuración Symfony
├── public/index.php         # front controller (único punto de entrada web)
├── src/                     # código Symfony (controladores, puente legacy)
├── templates/               # Twig
├── legacy/                  # aplicación original adaptada a PHP 8.4 (ejecutada vía Symfony)
├── docker/                  # Apache, PHP, entrypoint, script SQL inicial
├── db/textileria.sql        # dump original
├── public_html/             # código ORIGINAL sin modificar (respaldo; puede eliminarse)
└── docs/
```

`public_html/` se conserva solo como respaldo del código original: no se usa ni se incluye en
la imagen Docker. Puede eliminarse cuando se haya validado la migración.
