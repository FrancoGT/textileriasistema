<!DOCTYPE html>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/> 
<html>
<head>
   <link href="CSS/Sixavi.css" media="screen" rel="stylesheet" type="text/css" />
   <link rel="stylesheet" href="CSS/jquery-ui-1.11.0/jquery-ui.css">
   <script type="text/javascript" src="CSS/jquery-1.10.2.min.js"></script> 
   <script type="text/javascript" src="CSS/jquery-ui-1.11.0/jquery-ui.js"></script>
   <script type="text/javascript" src="CSS/jquery.validate.js"></script>
   <script type="text/javascript" src="CSS/Libgen.js"></script>
   <script>
   $(document).ready(function() {
   $("#pdFecLis").datepicker();
   });
   </script>
   <title>TEXTILERIA</title>
   <script src="CSS/Libreria.js" language="JavaScript"></script>
</head>
<body>
<form action="ERP2010.php#no-back" method="post">
   <div class="wrapper">
   <div id="MH">
           <ul>
                <li style="margin-left: 50px;"><h1>Mantenimiento de Pedidos</h1></li>
				<li style="float:right;margin-right: 100px;"><a onclick="self.location = 'index.php'"> Cerrar Sesión</a></li>
           </ul>
   </div>
   <div id="topnavbar"></div>
   <div id="navigation">
   <div id="navcontainer">
   {if $scBehavior eq "0"}
	  <table>
         <tr>
         <td>Tipo de Persona:</td>
         <td>
            <select name="pcTipo">
            {foreach from = $saTipo item = i}
               <option value="{$i[0]}">{$i[1]}</option>
            {/foreach}
            </select>
         </td>
         </tr>
      </table>
	  
      <table>
         <td><input type="submit" name="Boton0" value="Aceptar" /></td>
         <td><input type="submit" name="Boton0" value="Salir" /></td>
      </table>
   {elseif $scBehavior eq "1"}
      <table>
	  <tr>
	     <td><input type="submit" name="Boton" value="Nuevo Cliente" /></td>
		 <td><input type="submit" name="Boton" value="Cargar Datos" /></td>
		 </tr>
	   {if {$scTipo} eq 'A'}
         <tr>
         <td>DNI:</td>
		 <td>
		    <select name="pcNroDNI">
            {foreach from = $saNroDNI item = i}
               <option value="{$i[0]}"{if $i[0] eq $scNroDNI} selected {/if}>{$i[1]}</option>
            {/foreach}
            </select>
		 </td>
		 <td>Nombre/Razon Social:</td>
		 <td>
		    <input type="text" name="pcNombre" value="{$scNombre}" disabled>
		 </td>
         </tr>
		{else}
		 <tr>
         <td>RUC:</td>
		 <td>
		    <select name="pcNroRUC">
            {foreach from = $saNroRUC item = i}
               <option value="{$i[0]}" {if $i[0] eq $scNroRUC} selected {/if}>{$i[1]}</option>
            {/foreach}
            </select>
		 </td>
		 <td>Nombre/Razon Social:</td>
		 <td>
		    <input type="text" name="pcNombre" value="{$scNombre}" disabled>
		 </td>
         </tr>
		 {/if}
		 <!--<tr>
         <td>Nombre/Razon Social:</td>
         <td>
		    <input type="text" name="pcNombre" value="{$scNombre}" disabled>
		 </td>
         </tr>-->
		 <tr>
         <td>Direccion: </td><td><input type="text" maxlength="50" size="50" name="pcDirec" value="{$scDirec}" disabled></td>
		 <td>Telefono: </td><td><input type="text" maxlength="11" size="11" name="pcTelef" value="{$scTelef}" disabled></td>
         </tr>
		 <tr>
         <td>Prioridad:</td>
         <td>
		    <select name="pcPriori">
            {foreach from = $saPriori item = i}
               <option value="{$i[0]}" {if $i[0] eq $scPriori} selected {/if}>{$i[1]}</option>
            {/foreach}
            </select>
		 </td>
         </tr><tr>
         <td>Fecha Listo:</td>
         <td>
			<input type="text" maxlength="10" id="pdFecLis"name="pdFecLis" value="{$sdFecLis}" size="12" placeholder="aaaa-mm-dd"/>
         </td>
         </tr><tr>
		 <td>Producto:</td>
		 <td>
		    <select name="pcCodProd">
		    {foreach from=$saCodProd item=i}
		      <option value="{$i[0]}">{$i[1]}</option>
            {/foreach}
			</select>
		 </td>
		 </tr>
      </table>
      <table>
         <tr>
         <td><input type="submit" name="Boton2" value="Agregar"/></td>
         <td><input type="submit" name="Boton2" value="Nuevo"/></td>
         </tr>
      </table>
		  <fieldset style="width:500px">
		  <legend align="left"><font face="calibri" size=3 color=blue>Detalle de Productos</font></legend>
		  <div id="content-table" style="overflow:scroll; height:400px; width:800px; overflow:auto; border: 1px;">
		  <table border=1 style="font-size:11px">
			 <tr>
			 <th width='20' align="center">Id</th>
			 <th width='20' align="center">Codigo</th>
			 <th width='30' align="center">Descripcion</th>
			 <th width='80' align="center">Precio c/u</th>
			 <th width='100' align="center">Cantidad</th>
			 <th width='100' align="center">P.Total</th>
			 </tr>
         {foreach from = $saDataP item = i name = count}
            <tr>
            <td width="25" align="center"><font style="font-size:10px">{$smarty.foreach.count.index + 1}</font></td>
            <td width="50" align="center">{$i['0']}<input type="hidden" name="paDataP[{$i[0]}][0]" value="{$i[0]}" /></td>
            <td width="100"align="center">{$i['1']}<input type="hidden" name="paDataP[{$i[0]}][1]" value="{$i[1]}" /></td>
            <td width="100"align="center">{$i['2']}<input type="hidden" name="paDataP[{$i[0]}][2]" value="{$i[2]}" /></td>
			<td width="100"align="center"><input type="text" name="paDataP[{$i[0]}][3]" value="{$i[3]}"/></td>
			<td width="100"align="center"><input type="text" name="paDataP[{$i[0]}][4]" value="{$i[4]}" disabled/></td>
			</tr>
         {/foreach}
		  </table>
		  </div>
      </fieldset>
	  <table>
         <tr>
         <td><input type="submit" name="Boton3" value="Calcular"/>
		 VALOR VENTA  <input type="text" name="pcVV" value="{$scVV}" disabled></td>
		 IGV 18% <input type="text" name="pcIGV" value="{$scIGV}" disabled></td>
		 TOTAL  <input type="text" name="pcTotal" value="{$scTotal}" disabled></td>
         </tr>
      </table>
	  <table>
         <tr>
         <td><input type="submit" name="Boton4" value="Grabar"/></td>
         <td><input type="submit" name="Boton5" value="Cancelar"/></td>
         </tr>
      </table>
	{elseif $scBehavior eq "2"}
         <fieldset style="width:1000px" align="center">
            <legend><font face="calibri" size=4 color=blue>Archivos Anexos</font></legend>
            <table border=0 cellspacing="0" cellpadding="0" style="font-size:11px">
                  <tr>
                  <td align='center'>
				  <a href='{$scFilRet}' target="_blank">FACTURA</a>
                  </tr>
            </table>
         </fieldset>
         <table>
            <tr>
			    <td><input type="submit" name="Boton0" value="Salir"/></td>>
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