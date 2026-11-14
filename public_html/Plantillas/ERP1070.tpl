<!DOCTYPE html>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/> 
<html>
<head>
   <link href="CSS/Sixavi.css" media="screen" rel="stylesheet" type="text/css" />
   <title>TEXTILERIA</title>
   <script src="CSS/Libreria.js" language="JavaScript"></script>
</head>
<body>
<form action="ERP1070.php#no-back" method="post">
   <div class="wrapper">
   <div id="MH">
           <ul>
                <li style="margin-left: 50px;"><h1>Mantenimiento de Productos</h1></li>
				<li style="float:right;margin-right: 100px;"><a onclick="self.location = 'index.php'"> Cerrar Sesión</a></li>
           </ul>
   </div>
   <div id="topnavbar"></div>
   <div id="navigation">
   <div id="navcontainer">
   {if $scBehavior eq "0"}
      <div style="overflow:scroll; height:500px; width:800px; overflow:auto; border:1px">
      <table border=2 style="font-size:11px">
         <th bgcolor="#F29818" width="25" align="center">Nro</th>
         <th bgcolor="#F29818" width="120" align="center">Codigo de Producto</th>
		 <th bgcolor="#F29818" width="210" align="center">Descripcion de Producto</th>
         <th bgcolor="#F29818" width="100" align="center">Estado</th>
         <th bgcolor="#F29818" width="100" width="300" align="center">NUEVO/ANTIGUO</th>
         <th width='25' align="center"><img src="CSS/Edit.png" style="width: 100%; height: 10%;"/></th>
         {foreach from = $saDataP item = i name = count}
            <tr>
            <td width="25" align="center"><font style="font-size:10px">{$smarty.foreach.count.index + 1}</font></td>
            <td width="120" align="center">{$i['0']}</td>
            <td width="210"align="center">{$i['1']}</td>
            <td width="100"align="center">{$i['2']}</td>
			<td width="100"align="center">{$i['3']}</td>
			<td width="20" align="center"><input type="radio" name="pcCodProd" value="{$i[0]}"/></td>
            </tr>
         {/foreach}
      </table>
      </div>
      <table>
         <tr>
		 <td><input type="submit" name="Boton0" value="Nuevo" /></td>
         <td><input type="submit" name="Boton0" value="Detalle" /></td>
         <td><input type="submit" name="Boton0" value="Salir" /></td>
         </tr>
      </table>
   {elseif $scBehavior eq "1"}
      <table style="font-size:11px">
         <tr>
         <td>Código:</td>
         {if empty($scCodProd)}
            <td></td>
         {else}
            <td><font color=blue>{$scCodProd}</font><input type="hidden" maxlength="6" name="pcCodProd" value="{$scCodProd}"/></td>
         {/if}
         </tr><tr>
         <td>Nombre de Producto:</td>
         <td><input type="text" maxlength="90" name="pcDescri" value="{$scDescri}" size="60" style="text-transform:uppercase;"/></td>
         </tr>

		 <tr>
         <td>Insumos:</td>
         <td>
            <select name="pcCodIns1">
            {foreach from = $saCodIns item = i}
               <option value="{$i[0]}" {if $i[0] eq $scCodIns} selected {/if}>{$i[1]}</option>
            {/foreach}
            </select>
			<input type="submit" name="Boton1" value="Agregar" />
         </td>
         </tr>
      </table>
	        <fieldset style="width:500px">
      <legend align="left"><font face="calibri" size=3 color=blue>Detalle de Insumos</font></legend>
      <div id="content-table" style="overflow:scroll; height:400px; width:800px; overflow:auto; border: 1px;">
      <table border=1 style="font-size:11px">
         <tr>
         <th bgcolor="#F29818"width='20' align="center">Id</th>
         <th bgcolor="#F29818"width='150' align="center">Codigo Insumo</th>
         <th bgcolor="#F29818"width='80' align="center">Descripcion</th>
         <th bgcolor="#F29818"width='100' align="center">Unidad de Medida</th>
		 <th bgcolor="#F29818"width='100' align="center">Precio</th>
         <th bgcolor="#F29818"width='25' align="center"><img src="CSS/anular.bmp" style="width: 100%; height: 100%;"/></th>
         <th style="visibility: hidden"></th>
         </tr>
         {foreach from = $saData item = i name = count}
            <tr>
            <td width="25" align="center"><font style="font-size:10px">{$smarty.foreach.count.index + 1}</font></td>
            <td width="50" align="center">{$i['0']}</td>
            <td width="100"align="center">{$i['1']}</td>
            <td width="100"align="center">{$i['2']}</td>
			<td width="100"align="center">{$i['3']}</td>
			<td align="center"><input type="radio" name="paCheck[]" value="{$i[0]}"/></td>
            </tr>
         {/foreach}
      </table>
      </div>
      </fieldset>

       <table>
         <tr>
         <td><input type="submit" name="Boton1" value="Grabar" /></td>
         <td><input type="submit" name="Boton1" value="Eliminar" /></td>
         <td><input type="submit" name="Boton1" value="Cancelar" /></td>
         </tr>
      </table>
	  {elseif $scBehavior eq "2"}
      <table style="font-size:11px">
         <tr>
         <td>Código:</td>
         {if empty($scCodProd)}
            <td></td>
         {else}
            <td><font color=blue>{$scCodProd}</font><input type="hidden" maxlength="6" name="pcCodProd" value="{$scCodProd}"/></td>
         {/if}
         </tr><tr>
         <td>Nombre de Producto:</td>
         <td><input type="text" maxlength="90" name="pcDescri" value="{$scDescri}" size="60" style="text-transform:uppercase;"/></td>
         </tr>

		 <tr>
         <td>Estado:</td>
         <td>
            <select name="pcEstado">
            {foreach from = $saEstado item = i}
               <option value="{$i[0]}" {if $i[0] eq $scEstado} selected {/if}>{$i[1]}</option>
            {/foreach}
            </select>
         </td>
         </tr>
      </table>
       <table>
         <tr>
         <td><input type="submit" name="Boton2" value="Grabar" /></td>
         <td><input type="submit" name="Boton2" value="Cancelar" /></td>
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