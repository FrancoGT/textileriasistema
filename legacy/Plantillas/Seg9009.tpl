<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<html>
<head>
   <link href="CSS/Sixavi.css" media="screen" rel="stylesheet" type="text/css" />
   <script src="CSS/Libreria.js" language="JavaScript"></script>
   <title>Seguridad Operativa</title>
</head>
<body>
<form method = "post" action = "Seg9009.php#no-back">
   <div class="wrapper">
   <div id="MH">
      <ul>
      <li style="margin-left: 50px;"><h1>Consulta de Incidentes</h1></li>
      <li style="float:right;margin-right: 100px;"><a onclick="self.location = 'index.php'"> Cerrar Sesión</a></li>
      </ul>
   </div>
   <div id="topnavbar"></div>
   <div id="navigation">
   <div id="navcontainer">
   {if $scBehavior eq '0'}
      <table border=2 style="font-size: 13px">
         <tr>
         <td>Fecha Inicial</td>
         <td><input type = "text" name = "pdFecIni" placeholder="aaaa-mm-dd" value='{$sdFecIni}' onKeyPress="return fDate(event)"/></td>
         </tr><tr>
         <td>Fecha Final</td>
         <td><input type = "text" name = "pdFecFin" placeholder="aaaa-mm-dd" value='{$sdFecFin}' onKeyPress="return fDate(event)"/></td>
         </tr>
      </table>
      <table>
         <tr>
         <td><input type="submit" name="Boton1" value="Aplicar" /></td>
         <td><input type="submit" name="Boton1" value="Salir" /></td>
         </tr>
      </table>   
   {else}
      <table style="font-size: 11px">
         <tr>
         <td>Desde</td>
         <td>{$sdFecIni}</td>
         <td>Hasta</td>
         <td>{$sdFecFin}</td>
         </tr>
      </table>
      <div style="overflow:scroll; height:300px; width:515px; overflow:auto; border:2px solid gray;">
      <table border=2 width=500 style="font-size: 11px">
         <tr>
         <th width=100 align='center'>Número</th>
         <th width=100 align='center'>Fecha</th>
         <th width=300 align='center'>Tren</th>
         <th><img src="CSS/Check.jpg" height="32" width="32"> </th>
         </tr>
         {foreach from = $saData item = i}
            <tr>
            <td align="center">{$i['0']}</td>
            <td>{$i['1']}</td>
            <td>{$i['2']}</td>
            <td align="center"><input type = "radio" name = "pcNumero" value = "{$i['0']}"/></td>
            </tr>
         {/foreach}
      </table>
	  </div>
      <table>
         <tr>
         <td><input type="submit" name="Boton2" value="Aceptar" /></td>
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