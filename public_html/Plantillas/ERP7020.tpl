<!DOCTYPE html>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/> 
<html>
<head>
      <link href="CSS/Sixavi.css" media="screen" rel="stylesheet" type="text/css" />
   <link href="CSS/Sixavi.css" media="screen" rel="stylesheet" type="text/css" />
   <link rel="stylesheet" href="CSS/jquery-ui-1.11.0/jquery-ui.css">
   <script type="text/javascript" src="CSS/jquery-1.10.2.min.js"></script> 
   <script type="text/javascript" src="CSS/jquery-ui-1.11.0/jquery-ui.js"></script>
   <script type="text/javascript" src="CSS/jquery.validate.js"></script>
   <script type="text/javascript" src="CSS/jquery.numeric.js"></script>
   <script type="text/javascript" src="CSS/Libgen.js"></script>
<script>
   $(document).ready(function() {
   $("#pdFecLis").datepicker();
   $("#pnMonto").numeric();
});
   </script>
   
   <title>TEXTILERIA</title>
   <script src="CSS/Libreria.js" language="JavaScript"></script>
</head>
<body>
<form action="ERP7020.php#no-back" method="post">
   <div class="wrapper">
   <div id="MH">
           <ul>
                <li style="margin-left: 50px;"><h1>Reporte de Facturas</h1></li>
				<li style="float:right;margin-right: 100px;"><a onclick="self.location = 'index.php'"> Cerrar Sesión</a></li>
           </ul>
   </div>
   <div id="topnavbar"></div>
   <div id="navigation">
   <div id="navcontainer">
   {if $scBehavior eq "0"}
      <table border=2 style="font-size:11px">
         <th bgcolor="#F29818" width="25" align="center">Nro</th>
         <th bgcolor="#F29818" width="100" align="center">Codigo de Factura</th>
		 <th bgcolor="#F29818" width="100" align="center">Codigo de Proveedor</th>
		 <th bgcolor="#F29818" width="220" align="center">Nombre / Razon Social</th>
		 <th bgcolor="#F29818" width="70" align="center">Fecha de Orden</th>
		 <th bgcolor="#F29818" width="70" align="center">Monto</th>
		 <th width='25' align="center"><img src="CSS/Edit.png" style="width: 100%; height: 10%;"/></th>
      </table>
      
      <div style="overflow:scroll; height:500px; width:1200px; overflow:auto; border:1px">
      <table border=2 style="font-size:11px">
         {foreach from = $saDataFac item = i name = count}
            <tr>
            <td width="25" align="center"><font style="font-size:10px">{$smarty.foreach.count.index + 1}</font></td>
            <td width="100" align="center">{$i['0']}</td>
            <td width="100"align="center">{$i['1']}</td>
            <td width="220"align="center">{$i['2']}</td>
			<td width="70" align="center">{$i['3']}</td>
			<td width="70" align="center">{$i['4']}</td>
			<td width="20" align="center"><input type="radio" name="pcCodFac" value="{$i[0]}"/></td>
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
   <table>
   <tr>    
         <td>Nombre / Razon Social:</td>
         <td><input type="text" maxlength="50" name="pcNombre" value="{$scNombre}" size="50" disabled/></td>
   </tr>
   <tr>    
         <td>Tipo de Persona:</td>
         <td><input type="text" maxlength="30" name="pcTipPer" value="{$scTipPer}" size="50" disabled/></td>
   </tr>
   {if {$scTipo} eq "A"}
    <tr>
    <td>DNI:</td>
    <td><input type="text" class="input_num" maxlength="8" name="pcNroDni" value="{$scNroDNI}" size="8" disabled/></td>
    </tr>
	{else}
	<tr>
    <td>RUC:</td>
    <td><input type="text" class="input_num" maxlength="11" name="pcNroRUC" value="{$scNroRUC}" size="11" disabled/></td>
    </tr>
	{/if}
   <tr>    
         <td>Direccion:</td>
         <td><input type="text" maxlength="50" name="pcDirecc" value="{$scDirecc}" size="50" disabled/></td>
   </tr>
   <tr>    
         <td>Fecha de Pago:</td>
         <td><input type="text" maxlength="10" id="pdFecLis" name="pdFecLis" value="{$sdFecLis}" size="10" disabled/></td>
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
		 <th bgcolor="#F29818"width='100' align="center">Cantidad</th>
		 <th bgcolor="#F29818"width='100' align="center">Total</th>
         <th style="visibility: hidden"></th>
         </tr>
         {foreach from = $saData item = i name = count}
            <tr>
            <td width="25" align="center"><font style="font-size:10px">{$smarty.foreach.count.index + 1}</font></td>
            <td width="50" align="center">{$i['0']}</td>
            <td width="100"align="center">{$i['1']}</td>
            <td width="100"align="center">{$i['2']}</td>
			<td width="100"align="center">{$i['3']}</td>
			<td width="100"align="center">{$i['4']}</td>
			<td width="100"align="center">{$i['5']}</td>
			</tr>
         {/foreach}
      </table>
      </div>
      </fieldset>
	  <tr>
	  <table>
    <td>VALOR VENTA:</td>
    <td><input type="text" class="input_num" maxlength="8" name="VV" value="{$VV}" size="8" disabled/></td>
	<td>I.G.V.:</td>
    <td><input type="text" class="input_num" maxlength="8" name="IGV" value="{$IGV}" size="8" disabled/></td>
	<td>TOTAL:</td>
    <td><input type="text" class="input_num" maxlength="8" name="Total" value="{$Total}" size="8" disabled/></td>
    </tr>
   </table>
         <table>
         <tr>
		 <td><input type="submit" name="Boton1" value="Cancelar" /></td>
         </tr>
      </table>   
   {elseif $scBehavior eq "2"}
   <table>
   <tr>    
         <td>Nombre / Razon Social:</td>
         <td><input type="text" maxlength="50" name="pcNombre" value="{$scNombre}" size="50" disabled/></td>
   </tr>
   <tr>
         <td>Cta de Provedor:</td>
         <td>
            <select name="pcCtaPrv">
            {foreach from = $saCtaPrv item = i}
               <option value="{$i[0]}" {if $i[0] eq $scCtaPrv} selected {/if}>{$i[1]}</option>
            {/foreach}
            </select>
	     </td>
   </tr>
   <tr>
         <td>Banco:</td>
         <td>
            <select name="pcCodBan">
            {foreach from = $saCodBan item = i}
               <option value="{$i[0]}" {if $i[0] eq $scCodBan} selected {/if}>{$i[1]}</option>
            {/foreach}
            </select>
	     </td>
		 <td><input type="submit" name="Boton2" value="Cargar" /></td>
   </tr>
   <tr>
         <td>Cta de Banco:</td>
         <td>
            <select name="pcCtaBan">
            {foreach from = $saCtaBan item = i}
               <option value="{$i[0]}" {if $i[0] eq $scCtaBan} selected {/if}>{$i[1]}</option>
            {/foreach}
            </select>
	     </td>
   </tr>
   </table>
   <table>
         <tr>
         <td><input type="submit" name="Boton2" value="Pagar" /></td>
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