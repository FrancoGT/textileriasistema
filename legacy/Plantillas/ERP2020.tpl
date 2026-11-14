<!DOCTYPE html>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/> 
<html>
<head>
   <link href="CSS/Sixavi.css" media="screen" rel="stylesheet" type="text/css" />
   <title>TEXTILERIA</title>
   <script src="CSS/Libreria.js" language="JavaScript"></script>
</head>
<body>
<form action="ERP2020.php#no-back" method="post">
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
         <th bgcolor="#F29818" width="150" align="center">Codigo de Pedido</th>
		 <th bgcolor="#F29818" width="150" align="center">Codigo Cliente</th>
		 <th bgcolor="#F29818" width="220" align="center">Nombre / Razon Social</th>
		 <th bgcolor="#F29818" width="70" align="center">Estado</th>
		 <th bgcolor="#F29818" width="70" align="center">Prioridad</th>
		 <th bgcolor="#F29818" width="70" align="center">Fecha Listo</th>
         <th width='25' align="center"><img src="CSS/Edit.png" style="width: 100%; height: 10%;"/></th>
      </table>
      
      <div style="overflow:scroll; height:500px; width:1200px; overflow:auto; border:1px">
      <table border=2 style="font-size:11px">
         {foreach from = $saDataPed item = i name = count}
            <tr>
            <td width="25" align="center"><font style="font-size:10px">{$smarty.foreach.count.index + 1}</font></td>
            <td width="150" align="center">{$i['0']}</td>
            <td width="150"align="center">{$i['1']}</td>
            <td width="220"align="center">{$i['2']}</td>
			<td width="70" align="center">{$i['3']}</td>
			<td width="70" align="center">{$i['4']}</td>
			<td width="70" align="center">{$i['5']}</td>
			<td width="20" align="center"><input type="radio" name="pcCodPed" value="{$i[0]}"/></td>
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
         <fieldset style="width:1000px" align="center">
		 <td>Número de Pedido: {$scCodPed}</td>
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
			    <td><input type="submit" name="Boton0" value="Salir"/></td>
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