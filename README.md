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

* [docs/ARCHITECTURE.md](docs/ARCHITECTURE.md): layers, modules, changes from the original screens and verification commands.

* [docs/DESPLIEGUE-DOCKER-WINDOWS.md](docs/DESPLIEGUE-DOCKER-WINDOWS.md) — todos los comandos
  de Docker para Windows: local, producción, despliegue remoto, respaldos y solución de problemas.
* [docs/MIGRACION.md](docs/MIGRACION.md): what was migrated and which code was removed.

## Estructura

```
├── Dockerfile               # PHP 8.4.27 + Apache, etapas base/dev/prod
├── compose.yaml             # desarrollo local
├── compose.prod.yaml        # producción
├── config/                  # configuración Symfony
├── public/index.php         # front controller (único punto de entrada web)
├── src/                     # controladores, servicios y repositorios DBAL
├── templates/               # Twig (pantallas ERP en templates/erp)
├── docker/                  # Apache, PHP, entrypoint, script SQL inicial
├── db/textileria.sql        # dump original
└── docs/
```

El código original (`public_html/` y `legacy/`) se eliminó del repositorio. Está en el
historial de git, en los commits anteriores a su eliminación.
