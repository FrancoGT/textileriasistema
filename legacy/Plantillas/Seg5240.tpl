<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<html>
<head>
   <link href="CSS/Sixavi.css" media="screen" rel="stylesheet" type="text/css" />
   <title>Seguridad Operativa</title>
</head>
<body>
<form method="post" action="Seg5240.php">
   <div class="wrapper">
   <div id="header">
   <table>
      <tr>
      <td><img src="CSS/Softia.png" width="46" height="38" /></td>
      <td><p class="titulo">Reporte de Normas por Tipo de Charla - Resumen</p></td>
      </tr>
   </table>
   </div>
   <div id="topnavbar"></div>
   <div id="navigation">
   <div id="navcontainer">
   {if $scBehavior eq "0"}
      <table>
         <tr>
         <td>Tipo de Charla:</td>
         <td>
            <select name="pcTipo">
            {foreach from = $saTipo item = i}
               <option value="{$i[0]}">{$i[1]}</option>
            {/foreach}
            </select>
         </td>
         </tr><tr>
         <td>Desde: </td>
         <td><input type='text' name='pdFecIni' value='{$sdFecIni}'/></td>
         </tr><tr>
         <td>Hasta: </td>
         <td><input type='text' name='pdFecFin' value='{$sdFecFin}'/></td>
      </table>
      <table>
         <td><input type="submit" name="Boton1" value="Reporte" /></td>
         <td><input type="submit" name="Boton1" value="Salir" /></td>
      </table>
   {elseif $scBehavior eq '1'}
      <fieldset style="width:1000px" align="center">
      <legend><font face="calibri" size=4 color=blue>Archivos Anexos</font></legend>
      <table border=0 cellspacing="0" cellpadding="0" style="font-size:11px">
         <a href="{$scFilRep}" target="_blank">Capacitaciones de Personal por Tipo de Charla</a>
      </table>
      </fieldset>
      <table>
         <tr>
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