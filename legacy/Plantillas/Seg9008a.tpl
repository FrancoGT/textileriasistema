<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<html>
<head>
   <script type="text/javascript" src="CSS/Sixavi.js"></script>
   <link href="CSS/Sixavi.css" media="screen" rel="stylesheet" type="text/css" />
   <title>Seguridad Operativa</title>
</head>
<body onload="cargar()">
<form method = "post" action = "Seg9008.php">
   <div class="wrapper">
   <div id="header">
   <table>
      <tr>
      <td><img src="CSS/Softia.png" width="46" height="38" /></td>
      <td><p class="titulo">Consulta de Incidentes</p></td>
      </tr>
   </table>
   </div>
   <div id="topnavbar"></div>
   <div id="navigation">
   <div id="navcontainer">
   {if $scBehavior eq '0'}
   <table border=2 width=650 style="font-size:11px">
      <tr>
      <th width=100 align='center'>Número</th>
      <th width=400 align='center'>Descripción</th>
      <th width=100 align='center'>Estado</th>
      <th width=30 align='center'><img src="CSS/Check.jpg" height="32" width="32"> </th>
      </tr>
      {foreach from = $saNroInc item = i}
         <tr>
         <td align="center">{$i['0']}</td>
         <td>{$i['1']}</td>
         <td align="center">{$i['2']}</td>
         <td align="center"><input type = "radio" name = "pcNroInc" value = "{$i['0']}"/></td>
         </tr>
      {/foreach}
   </table>
   <table>
      <tr>
      <td><input type="submit" name="Boton1" value="Siguiente" class="boton"/></td>
      <td><input type="submit" name="Boton1" value="Anterior" class="boton"/></td>
	  </tr>
   </table>
   <table>
      <tr>
      <td><input type="submit" name="Boton2" value="Aceptar" /></td>
      <td><input type="submit" name="Boton2" value="Salir" /></td>
      </tr>
   </table>  
   {/if}
   {if $scBehavior eq '1'}
   <table border=2 width=650 style="font-size:11px">
      <tr>
      <th width=100 align='center'>Número</th>
      <th width=400 align='center'>Descripción</th>
      <th width=100 align='center'>Estado</th>
      <th width=30 align='center'><img src="CSS/Check.jpg" height="32" width="32"> </th>
      </tr>
      {foreach from = $saNroInc item = i}
         <tr>
         <td align="center">{$i['0']}</td>
         <td>{$i['1']}</td>
         <td align="center">{$i['2']}</td>
         <td align="center"><input type = "checkbox" name = "pcNroInc" value = "{$i['0']}"/></td>
         </tr>
      {/foreach}
   </table>
   <table>
      <tr>
      <td><input type="submit" name="Boton1" value="Siguiente" class="boton" /></td>
      <td><input type="submit" name="Boton1" value="Anterior" class="boton" /></td>
      </tr>
   </table>
   <table>
      <tr>
      <td><input type="submit" name="Boton4" value="Aceptar" /></td>
      <td><input type="submit" name="Boton4" value="Salir" /></td>
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