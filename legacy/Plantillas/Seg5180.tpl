<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<html>
<head>
   <link href="CSS/Sixavi.css" media="screen" rel="stylesheet" type="text/css" />
   <title>Seguridad Operativa</title>
</head>
<body>
<form method = "post" action = "Seg5180.php">
   <div class="wrapper">
   <div id="MH">
      <ul>
      <li style="margin-left: 50px;"><h1>Reporte de Evaluaciones Por Personal</h1></li>
      <li style="float:right;margin-right: 100px;"><a onclick="self.location = 'index.php'"> Cerrar Sesión</a></li>
      </ul>
   </div>
   <div id="topnavbar"></div>
   <div id="navigation">
   <div id="navcontainer">
   {if $scBehavior eq "0"}
      <fieldset style='width:500px' align='center'>
      <table border = 0>
      <tr>
      <td>Tipo: </td>
      <td align='center'>
        <select width="200" style="width: 190px; font-size:11px" name = "pcTipo">
        {foreach from = $saTipo item = j}
           <option value = "{$j[0]}" > {$j[1]} </option>
        {/foreach}
        </select>
      </td>
	  </tr><tr>
      <td>Desde: </td>
      <td><input type='text' name='pdFecIni' value='{$sdFecIni}'/></td>
	  </tr><tr>
      <td>Hasta: </td>
      <td><input type='text' name='pdFecFin' value='{$sdFecFin}'/></td>
     <!--</tr><tr>
      <td>Supervisor: </td>
      <td align='center'>
        <select width="200" style="width: 190px; font-size:11px" name = "pcSuperv">
        {foreach from = $saSuperv item = j}
           <option value = "{$j[0]}" > {$j[1]} </option>
        {/foreach}
        </select>
      </td>-->
      </tr>
      </table>  
      </fieldset>
      <table>
         <tr>
         <td><input type='submit' name='Boton1' value='Reporte'/></td>
         <td><input type='submit' name='Boton1' value='Salir'/></td>
         </tr>
      </table>
   {elseif $scBehavior eq '1'}
      <fieldset style="width:1000px" align="center">
      <table border = 0 cellspacing="0" cellpadding="0" style="font-size:11px">
         <a href="{$scFilRep}" target="_blank">Evaluaciones de Personal</a>
      </table>
      </fieldset>
      <table>
         <tr>
         <td><input type = "submit" name = "Boton2" value = "Cancelar" /></td>
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