<meta HTTP-EQUIV="Content-Type" content="text/html; charset=utf-8" />
<html>
<head>
<title>TEXTILERIA</title>
   <link href="CSS/Sixavi.css" media="screen" rel="stylesheet" type="text/css" />
   <script language="javascript" type="text/javascript" src="CSS/funMarcado.js"></script>
</head>
<body>
   <span id="che" style="display: none;visibility: hidden;">{$scNivel}</span>
   <div class="wrapper">
   <div id="MH">
           <ul>
                <li style="margin-left: 50px;"><h1>SISTEMA ERP-X</h1></li>
                <li style="float:right;margin-right: 100px;"><a onclick="self.location = 'index.php'"> Cerrar Sesión</a></li>
           </ul>
   </div>
   <font color = blue size=1>{$scCodigo} - {$scNombre}</font>
   <div id="MT">
         <ol class="tree">
             <li>
                <label for="folder01">Mantenimiento Maestros</label><input type="checkbox" id="folder01" onClick="desmarcar('folder01')"/>
                <ol>
				            <li class="file"><a href="ERP1000.php"> Mantenimiento de Personal</a></li>
							<li class="file"><a href="ERP1010.php"> Mantenimiento de Usuarios</a></li>
							<li class="file"><a href="ERP1020.php"> Mantenimiento de Usuarios de Perfiles</a></li>
							<li class="file"><a href="ERP1030.php"> Mantenimiento de Clientes</a></li>
							<li class="file"><a href="ERP1040.php"> Mantenimiento de Maquinarias</a></li>
							<li class="file"><a href="ERP1050.php"> Mantenimiento de Insumos</a></li>
							<li class="file"><a href="ERP1060.php"> Mantenimiento de Proveedores</a></li>
							<!--<li class="file"><a href="ERP1061.php"> Asignar Insumos a Proveedores</a></li>-->
							<li class="file"><a href="ERP1070.php"> Mantenimiento de Productos</a></li>
							<li class="file"><a href="ERP1080.php"> Mantenimiento de Tabla de Tablas</a></li>
                            <li class="file"><a href="ERP1090.php"> Mantenimiento de Almacenes</a></li>
							<li class="file"><a href="ERP1091.php"> Asignar Insumos a Almacenes</a></li>
							<li class="file"><a href="ERP1093.php"> Mantenimiento de Bancos</a></li>
							<li class="file"><a href="ERP1094.php"> Mantenimiento de Cuentas</a></li>
							<!--<li class="file"><a href="ERP1092.php"> Asignar Productos a Almacenes</a></li>-->
                </ol>
            </li>
            <li>
                <label for="folder02">Pedidos</label><input type="checkbox" id="folder02" onClick="desmarcar('folder02')"/>
                <ol>
				            <li class="file"><a href="ERP1070.php"> Nuevo Producto</a></li>
				            <li class="file"><a href="ERP1030.php"> Registrar Cliente</a></li>
							<li class="file"><a href="ERP2010.php"> Registrar Pedido</a></li>
							<li class="file"><a href="ERP2020.php"> Reporte de Pedidos</a></li>
							<!--<li class="file"><a href="ERP2030.php"> Reporte de Orden(Devolucion)</a></li>
							<li class="file"><a href="ERP2040.php"> Rectificacion de Orden</a></li>-->
                </ol>
            </li>
            <li>
                <label for="folder03">Planificacion</label><input type="checkbox" id="folder03" onClick="desmarcar('folder03')"/>
                <ol>
				            <li class="file"><a href="ERP3000.php"> Reporte de Pedidos Pendientes</a></li>
							<li class="file"><a href="ERP3010.php"> Reporte de Pedidos Completos</a></li>
							<li class="file"><a href="ERP3020.php"> Reporte de Pedidos (Todos)</a></li>
							<!--<li class="file"><a href="ERP3030.php"> Devoluciones</a></li>-->
                </ol>
            </li>
            <li>
                <label for="folder04">Produccion</label><input type="checkbox" id="folder04" onClick="desmarcar('folder04')"/>
                <ol>
				            <li class="file"><a href="ERP4000.php"> Reporte de Produccion (Pendientes)</a></li>
							<li class="file"><a href="ERP4010.php"> Reporte de Produccion en Proceso Teñido</a></li>
							<li class="file"><a href="ERP4020.php"> Reporte de Produccion en Proceso Preparacion</a></li>
							<li class="file"><a href="ERP4030.php"> Reporte de Produccion en Proceso Tejido</a></li>
							<li class="file"><a href="ERP4040.php"> Reporte de Produccion en Proceso Ensanblaje</a></li>
							<li class="file"><a href="ERP4050.php"> Reporte de Produccion en Proceso Acabado</a></li>
							<li class="file"><a href="ERP4060.php"> Reporte de Produccion Completos</a></li>
							<li class="file"><a href="ERP4070.php"> Reporte de Produccion (Todos)</a></li>
                </ol>
            </li>
            <li>
                <label for="folder05">Logistica</label><input type="checkbox" id="folder05" onClick="desmarcar('folder05')"/>
                <ol>
				            <li class="file"><a href="ERP5010.php"> Generar Factura</a></li>
							<!--<li class="file"><a href="ERP5020.php"> Reporte de Orden Correcta</a></li>
							<li class="file"><a href="ERP5030.php"> Reporte de Orden Incorrecta (Planificacion)</a></li>-->
                </ol>
            </li>
            <li>
                <label for="folder06">Cuentas por Cobrar </label><input type="checkbox" id="folder06" onClick="desmarcar('folder06')"/>
                <ol>
				            <li class="file"><a href="ERP6010.php"> Reporte de Facturas</a></li>
<!--							<li class="file"><a href="ERP6020.php"> Reporte de Facturas Completas (TEST)</a></li>
							<li class="file"><a href="ERP6030.php"> Reporte de Facturas Incompletas (TEST)</a></li>-->
                </ol>
            </li>
			<li>
                <label for="folder07">Cuentas por Pagar</label><input type="checkbox" id="folder07" onClick="desmarcar('folder07')"/>
                <ol>        
				            <li class="file"><a href="ERP1093.php"> Registro de Bancos</a></li>
							<li class="file"><a href="ERP1094.php"> Registro de Cuentas (Proveedores y Bancos)</a></li>
				            <li class="file"><a href="ERP7010.php"> Reporte de Facturas Pendientes</a></li>
							<li class="file"><a href="ERP7020.php"> Reporte de Facturas Pagadas</a></li>
                </ol>
            </li>
			<li>
                <label for="folder08">Trabajo de Personal</label><input type="checkbox" id="folder08" onClick="desmarcar('folder08')"/>
                <ol>
				            <li class="file"><a href="ERP8000.php"> Trabajos Asignados</a></li>
							
                </ol>
            </li>
			<li>
                <label for="folder09">Despacho</label><input type="checkbox" id="folder09" onClick="desmarcar('folder09')"/>
                <ol>
				            <li class="file"><a href="ERP9010.php"> Entrega en Despacho</a></li>
							
                </ol>
            </li>
        </ol>
   </div>
   <div id="topnavbar"></div>
   <div id="navigation">
   <div id="navcontainer">  
         </div>
       </div>
       <div id="content">
       </div>
       <div id="footer">
       </div>
    </div>   
</body>
</html>