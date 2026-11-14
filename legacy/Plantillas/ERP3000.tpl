<!DOCTYPE html>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/> 
<html>
<head>
   <link href="CSS/Sixavi.css" media="screen" rel="stylesheet" type="text/css" />
   <title>TEXTILERIA</title>
   <script src="CSS/Libreria.js" language="JavaScript"></script>
</head>
<body>
<form action="ERP3000.php#no-back" method="post">
   <div class="wrapper">
   <div id="MH">
           <ul>
                <li style="margin-left: 50px;"><h1>Reporte de Pedidos</h1></li>
				<li style="float:right;margin-right: 100px;"><a onclick="self.location = 'index.php'"> Cerrar Sesión</a></li>
           </ul>
   </div>
   <div id="topnavbar"></div>
   <div id="navigation">
   <div id="navcontainer">
   {if $scBehavior eq "0"}
      <table border=2 style="font-size:11px">
         <th bgcolor="#F29818" width="25" align="center">Nro</th>
         <th bgcolor="#F29818" width="100" align="center">Codigo de Pedido</th>
		 <th bgcolor="#F29818" width="100" align="center">Codigo Cliente</th>
		 <th bgcolor="#F29818" width="220" align="center">Nombre / Razon Social</th>
		 <th bgcolor="#F29818" width="70" align="center">Estado</th>
		 <th bgcolor="#F29818" width="70" align="center">Prioridad</th>
		 <th bgcolor="#F29818" width="100" align="center">Fecha Listo</th>
         <th width='25' align="center"><img src="CSS/Edit.png" style="width: 100%; height: 10%;"/></th>
      </table>
      
      <div style="overflow:scroll; height:500px; width:1200px; overflow:auto; border:1px">
      <table border=2 style="font-size:11px">
         {foreach from = $saDataPed item = i name = count}
            <tr>
            <td width="25" align="center"><font style="font-size:10px">{$smarty.foreach.count.index + 1}</font></td>
            <td width="100" align="center">{$i['0']}</td>
            <td width="100"align="center">{$i['1']}</td>
            <td width="220"align="center">{$i['2']}</td>
			<td width="70" align="center">{$i['3']}</td>
			<td width="70" align="center">{$i['4']}</td>
			<td width="100" align="center">{$i['5']}</td>
			{if {$i['3']} eq 'COMPLETO'}
			<th width='25' align="center"><img src="CSS/Check.jpg" style="width: 100%; height: 10%;"/></th>
			{else}
			<td width="20" align="center"><input type="radio" name="pcCodPed" value="{$i[0]}"/></td>
			{/if}
            </tr>
         {/foreach}
      </table>
      </div>
      <table>
         <tr>
         <td><input type="submit" name="Boton0" value="Detalle" /></td>
		 <td><input type="submit" name="Boton0" value="Salir" /></td>
         </tr>
      </table>   
	{elseif $scBehavior eq "1"}
  <table border=2 style="font-size:11px">
         <th bgcolor="#F29818" width="25" align="center">Nro</th>
         <th bgcolor="#F29818" width="100" align="center">Codigo de Pedido</th>
		 <th bgcolor="#F29818" width="100" align="center">Codigo Producto</th>
		 <th bgcolor="#F29818" width="220" align="center">Descripcion</th>
		 <th bgcolor="#F29818" width="90" align="center">Cantidad</th>
		 <th bgcolor="#F29818" width="100" align="center">Estado</th>
         <th width='25' align="center"><img src="CSS/Edit.png" style="width: 100%; height: 10%;"/></th>
      </table>
      
      <div style="overflow:scroll; height:500px; width:1200px; overflow:auto; border:1px">
      <table border=2 style="font-size:11px">
         {foreach from = $saDataProd item = i name = count}
            <tr>
            <td width="25" align="center"><font style="font-size:10px">{$smarty.foreach.count.index + 1}</font></td>
            <td width="100" align="center">{$i['0']}</td>
            <td width="100"align="center">{$i['1']}</td>
            <td width="220"align="center">{$i['2']}</td>
			<td width="90" align="center">{$i['3']}</td>
			<td width="100" align="center">{$i['4']}</td>
			{if {$i['4']} eq 'COMPLETO'}
			<th width='25' align="center"><img src="CSS/Check.jpg" style="width: 100%; height: 10%;"/></th>
			{else}
			<td width="20" align="center"><input type="radio" name="pcCodProd" value="{$i[1]}"/></td>
			{/if}
            </tr>
         {/foreach}
      </table>
      </div>
      <table>
         <tr>
         <td><input type="submit" name="Boton1" value="Asignar" /></td>
		 <td><input type="submit" name="Boton1" value="Cancelar" /></td>
         </tr>
      </table>   
	 {elseif $scBehavior eq "2"}
	     <table style="font-size:11px">
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
      </table>
	  <table>
	  <tr>
         <td>Maquinas:</td>
         <td>
            <select name="pcCodMaq">
            {foreach from = $saCodMaq item = i}
               <option value="{$i[0]}" {if $i[0] eq $scCodMaq} selected {/if}>{$i[1]}</option>
            {/foreach}
            </select>
			<input type="submit" name="BotonM" value="Agregar" />
         </td>
         </tr>
		 </table>
		<fieldset style="width:500px">
      <legend align="left"><font face="calibri" size=3 color=blue>Detalle de Maquinaria</font></legend>
      <div id="content-table" style="overflow:scroll; height:520; width:200; overflow:auto; border: 1px;">
      <table border=1 style="font-size:11px">
         <tr>
         <th width='20' bgcolor="#F29818"align="center">Id</th>
         <th width='100' bgcolor="#F29818"align="center">Codigo</th>
         <th width='200' bgcolor="#F29818"align="center">Descripcion</th>
         <th width='200' bgcolor="#F29818"align="center">Tipo</th>
         <th style="visibility: hidden"></th>
         </tr>
         {foreach from = $saDataM item = i name = count}
            <tr>
            <td width="20" align="center"><font style="font-size:10px">{$smarty.foreach.count.index + 1}</font></td>
            <td width="100" align="center">{$i['0']}<input type="hidden" name="paDataM[{$i[0]}][0]" value="{$i[0]}" /></td>
            <td width="200"align="center">{$i['1']}<input type="hidden" name="paDataM[{$i[0]}][1]" value="{$i[1]}" /></td>
            <td width="200"align="center">{$i['2']}<input type="hidden" name="paDataM[{$i[0]}][2]" value="{$i[2]}" /></td>
            </tr>
         {/foreach}
      </table>
      </div>
      </fieldset>
	  <table>
	  <tr>
         <td>Personal:</td>
         <td>
            <select name="pcCodPer">
            {foreach from = $saCodPer item = i}
               <option value="{$i[0]}" {if $i[0] eq $scCodPer} selected {/if}>{$i[1]}</option>
            {/foreach}
            </select>
			<input type="submit" name="BotonP" value="Agregar" />
         </td>
         </tr>
		 </table>
	  <fieldset style="width:500px">
      <legend align="left"><font face="calibri" size=3 color=blue>Detalle de Personal</font></legend>
      <div id="content-table" style="overflow:scroll; height:520; width:200; overflow:auto; border: 1px;">
      <table border=1 style="font-size:11px">
         <tr>
         <th width='20' bgcolor="#F29818"align="center">Id</th>
         <th width='200' bgcolor="#F29818"align="center">Codigo</th>
         <th width='300' bgcolor="#F29818"align="center">Nombre</th>
         <th style="visibility: hidden"></th>
         </tr>
         {foreach from = $saDataP item = i name = count}
            <tr>
            <td width="20" align="center"><font style="font-size:10px">{$smarty.foreach.count.index + 1}</font></td>
            <td width="200" align="center">{$i['0']}<input type="hidden" name="paDataP[{$i[0]}][0]" value="{$i[0]}" /></td>
            <td width="300"align="center">{$i['1']}<input type="hidden" name="paDataP[{$i[0]}][1]" value="{$i[1]}" /></td>
			</tr>
         {/foreach}
      </table>
      </div>
      </fieldset>
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