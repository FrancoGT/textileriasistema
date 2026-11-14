<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>TEXTILERIA</title>
    <link href="/CSS/Sixavi.css" media="screen" rel="stylesheet" type="text/css">
    <link href="/css/menu.css" rel="stylesheet" type="text/css">
    <script src="/js/ui-feedback.js" defer></script>
    <script src="/js/menu.js" defer></script>
</head>
<body class="menu-page">
    <div class="wrapper menu-wrapper">
        <header id="MH" class="menu-header">
            <button type="button" id="menu-toggle" aria-controls="menu-sidebar" aria-expanded="false" aria-label="Abrir menú">
                <svg width="24" height="24" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                    <path class="menu-icon-bars" d="M4 6h16M4 12h16M4 18h16" />
                    <path class="menu-icon-close" d="m6 6 12 12M6 18 18 6" />
                </svg>
            </button>
            <h1><span>SISTEMA ERP-X</span></h1>
            <div class="user-account" id="user-account">
                <button type="button" id="user-toggle" aria-controls="user-panel" aria-expanded="false" aria-label="Abrir opciones de usuario">
                    <span class="user-avatar" aria-hidden="true">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="8" r="4"/><path d="M4 21v-2a8 8 0 0 1 16 0v2"/></svg>
                    </span>
                    <span class="user-toggle-name">{$scNombre|escape:'html'}</span>
                    <svg class="user-chevron" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
                </button>
                <section id="user-panel" aria-label="Opciones de usuario" hidden>
                    <div class="user-panel-identity">
                        <span class="user-avatar" aria-hidden="true">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="8" r="4"/><path d="M4 21v-2a8 8 0 0 1 16 0v2"/></svg>
                        </span>
                        <div><strong>{$scNombre|escape:'html'}</strong><span>Código: {$scCodigo|escape:'html'}</span></div>
                    </div>
                    <nav aria-label="Acciones de la cuenta">
                        <a class="user-account-link" href="/Mnu1000.php" target="erp-workspace"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="5" y="10" width="14" height="11" rx="2"/><path d="M8 10V6a4 4 0 0 1 8 0v4"/></svg>Cambiar clave</a>
                        <a class="user-signout" href="/index.php"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M9 21H4V3h5M13 7l5 5-5 5M8 12h13"/></svg>Cerrar sesión</a>
                    </nav>
                </section>
            </div>
        </header>
        <div class="menu-layout">
        <aside id="menu-sidebar" class="menu-sidebar">
        <p class="menu-user">{$scCodigo|escape:'html'} - {$scNombre|escape:'html'}</p>
        <nav class="menu-modules" aria-label="Módulos del sistema">
            <details class="menu-group">
                <summary aria-expanded="false">Mantenimiento Maestros</summary>
                <ul>
                    <li><a href="/ERP1000.php" target="erp-workspace">Mantenimiento de Personal</a></li>
                    <li><a href="/ERP1010.php" target="erp-workspace">Mantenimiento de Usuarios</a></li>
                    <li><a href="/ERP1020.php" target="erp-workspace">Mantenimiento de Usuarios de Perfiles</a></li>
                    <li><a href="/ERP1030.php" target="erp-workspace">Mantenimiento de Clientes</a></li>
                    <li><a href="/ERP1040.php" target="erp-workspace">Mantenimiento de Maquinarias</a></li>
                    <li><a href="/ERP1050.php" target="erp-workspace">Mantenimiento de Insumos</a></li>
                    <li><a href="/ERP1060.php" target="erp-workspace">Mantenimiento de Proveedores</a></li>
                    <li><a href="/ERP1070.php" target="erp-workspace">Mantenimiento de Productos</a></li>
                    <li><a href="/ERP1080.php" target="erp-workspace">Mantenimiento de Tabla de Tablas</a></li>
                    <li><a href="/ERP1090.php" target="erp-workspace">Mantenimiento de Almacenes</a></li>
                    <li><a href="/ERP1091.php" target="erp-workspace">Asignar Insumos a Almacenes</a></li>
                    <li><a href="/ERP1093.php" target="erp-workspace">Mantenimiento de Bancos</a></li>
                    <li><a href="/ERP1094.php" target="erp-workspace">Mantenimiento de Cuentas</a></li>
                </ul>
            </details>
            <details class="menu-group">
                <summary aria-expanded="false">Pedidos</summary>
                <ul>
                    <li><a href="/ERP1070.php" target="erp-workspace">Nuevo Producto</a></li>
                    <li><a href="/ERP1030.php" target="erp-workspace">Registrar Cliente</a></li>
                    <li><a href="/ERP2010.php" target="erp-workspace">Registrar Pedido</a></li>
                    <li><a href="/ERP2020.php" target="erp-workspace">Reporte de Pedidos</a></li>
                </ul>
            </details>
            <details class="menu-group">
                <summary aria-expanded="false">Planificación</summary>
                <ul>
                    <li><a href="/ERP3000.php" target="erp-workspace">Reporte de Pedidos Pendientes</a></li>
                    <li><a href="/ERP3010.php" target="erp-workspace">Reporte de Pedidos Completos</a></li>
                    <li><a href="/ERP3020.php" target="erp-workspace">Reporte de Pedidos (Todos)</a></li>
                </ul>
            </details>
            <details class="menu-group">
                <summary aria-expanded="false">Producción</summary>
                <ul>
                    <li><a href="/ERP4000.php" target="erp-workspace">Reporte de Producción (Pendientes)</a></li>
                    <li><a href="/ERP4010.php" target="erp-workspace">Reporte de Producción en Proceso Teñido</a></li>
                    <li><a href="/ERP4020.php" target="erp-workspace">Reporte de Producción en Proceso Preparación</a></li>
                    <li><a href="/ERP4030.php" target="erp-workspace">Reporte de Producción en Proceso Tejido</a></li>
                    <li><a href="/ERP4040.php" target="erp-workspace">Reporte de Producción en Proceso Ensanblaje</a></li>
                    <li><a href="/ERP4050.php" target="erp-workspace">Reporte de Producción en Proceso Acabado</a></li>
                    <li><a href="/ERP4060.php" target="erp-workspace">Reporte de Producción Completos</a></li>
                    <li><a href="/ERP4070.php" target="erp-workspace">Reporte de Producción (Todos)</a></li>
                </ul>
            </details>
            <details class="menu-group">
                <summary aria-expanded="false">Logística</summary>
                <ul>
                    <li><a href="/ERP5010.php" target="erp-workspace">Generar Factura</a></li>
                </ul>
            </details>
            <details class="menu-group">
                <summary aria-expanded="false">Cuentas por Cobrar</summary>
                <ul>
                    <li><a href="/ERP6010.php" target="erp-workspace">Reporte de Facturas</a></li>
                </ul>
            </details>
            <details class="menu-group">
                <summary aria-expanded="false">Cuentas por Pagar</summary>
                <ul>
                    <li><a href="/ERP1093.php" target="erp-workspace">Registro de Bancos</a></li>
                    <li><a href="/ERP1094.php" target="erp-workspace">Registro de Cuentas (Proveedores y Bancos)</a></li>
                    <li><a href="/ERP7010.php" target="erp-workspace">Reporte de Facturas Pendientes</a></li>
                    <li><a href="/ERP7020.php" target="erp-workspace">Reporte de Facturas Pagadas</a></li>
                </ul>
            </details>
            <details class="menu-group">
                <summary aria-expanded="false">Trabajo de Personal</summary>
                <ul>
                    <li><a href="/ERP8000.php" target="erp-workspace">Trabajos Asignados</a></li>
                </ul>
            </details>
            <details class="menu-group">
                <summary aria-expanded="false">Despacho</summary>
                <ul>
                    <li><a href="/ERP9010.php" target="erp-workspace">Entrega en Despacho</a></li>
                </ul>
            </details>
        </nav>
        </aside>
        <main class="menu-workspace">
            <h2 id="workspace-title">Inicio</h2>
            <p id="workspace-welcome">Seleccione una opción del menú para comenzar.</p>
            <p id="workspace-status" role="status" aria-live="polite"></p>
            <iframe id="erp-workspace" name="erp-workspace" title="Área de trabajo" hidden></iframe>
        </main>
        </div>
    </div>
</body>
</html>
