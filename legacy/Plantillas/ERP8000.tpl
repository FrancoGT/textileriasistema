<!DOCTYPE html>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/> 
<html>
<head>
   <link href="CSS/Sixavi.css" media="screen" rel="stylesheet" type="text/css" />
   <title>Textileria</title>
   <script src="CSS/Libreria.js" language="JavaScript"></script>
</head>
<body>
<form action="ERP8000.php#no-back" method="post">
   <div class="wrapper">
   <div id="MH">
           <ul>
                <li style="margin-left: 50px;"><h1>Trabajos Asignados</h1></li>
				<li style="float:right;margin-right: 100px;"><a onclick="self.location = 'index.php'"> Cerrar Sesión</a></li>
           </ul>
   </div>
   <div id="topnavbar"></div>
   <div id="navigation">
   <div id="navcontainer">
   {if $scBehavior eq "0"}
   	     <table style="font-size:11px">
		 <tr>
         <td>Trabajo Asignado A:</td>
         <td><input type="text" maxlength="90" name="pcNombre" value="{$scNombre}" size="60" disabled/></td>
         </tr>
		 <tr>
         <td>Código de Pedido:</td>
         <td><input type="text" maxlength="90" name="pcCodPed" value="{$scCodPed}" size="60" disabled/></td>
         </tr>
         <tr>
         <td>Código de Producto:</td>
         <td><input type="text" maxlength="90" name="pcCodProd" value="{$scCodProd}" size="60" disabled/></td>
         </tr>
		 <tr>
         <td>Nombre de Producto:</td>
         <td><input type="text" maxlength="90" name="pcDescri" value="{$scDescri}" size="60" disabled/></td>
         </tr><tr>
		 <td>Cantidad:</td>
         <td><input type="text" maxlength="90" name="pnCant" value="{$snCant}" size="60" disabled/></td>
         </tr>
         <tr>
		 <td>Maquina:</td>
         <td><input type="text" maxlength="90" name="pcDescriM" value="{$scDescriM}" size="60" disabled/></td>
         </tr>
         <tr>
		 <td>Tipo:</td>
         <td><input type="text" maxlength="90" name="pcTipo" value="{$scTipo}" size="60" disabled/></td>
         </tr>		 
		 <tr>
		 <td>PROCESO:</td>
         <td><input type="text" maxlength="90" name="pcProc" value="{$scProc}" size="60" disabled/></td>
         </tr>		 
         <table>
         <tr>
         <td><input type="submit" name="Boton0" value="Hecho" /></td>
		 <td><input type="submit" name="Boton0" value="Salir" /></td>
         </tr>
      </table>

	   {/if}
   </div>
   </div>
   <div id="content"></div>
   <div id="footer"></div>
   </div>
</form>
</body>
</html>