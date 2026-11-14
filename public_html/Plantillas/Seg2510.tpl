<meta http-equiv='Content-Type' content='text/html; charset=UTF-8'>
<html>
<head>
   <link href='CSS/Sixavi.css' media='screen' rel='stylesheet' type='text/css' />
   <title>Seguridad Operativa</title>
</head>
<body>
<form method = 'post' action = 'Seg2510.php#no-back'>
   <div class='wrapper'>
   <div id="MH">
      <ul>
      <li style="margin-left: 50px;"><h1>Informe de Investigación de Incidentes</h1></li>
      <li style="float:right;margin-right: 100px;"><a onclick="self.location = 'index.php'"> Cerrar Sesión</a></li>
      </ul>
   </div>
   <div id='topnavbar'></div>
   <div id='navigation'>
   <div id='navcontainer'>
   {if $scBehavior eq '0'}
      <fieldset style='width:500px' align='center'>
      <table border = 0>
         <tr>
         <td>Número Incidente</td>
         <td><input type = 'text' name = 'pcNroInc' value='' maxlength = '6' size='10' align='center' autofocus/></td>
         </tr>
      </table>  
      </fieldset>
      <table>
         <tr>
         <td><input type='submit' name='Boton1' value='Aplicar'/></td>
         <td><input type='submit' name='Boton1' value='Buscar'/></td>
         <td><input type='submit' name='Boton1' value='Salir'/></td>
         </tr>
      </table>
   {elseif $scBehavior eq '1'}
      <fieldset style='width:1000px' align='center'>
      <table border = 0 cellspacing='0' cellpadding='0' style='font-size:11px'>
         <tr>
         <td>Número de Incidente: {$scNroInc} </td></tr> 
		 <tr><td>{$smDescri}<input type = 'hidden' name = 'pcNroInc' value='{$scNroInc}' size='6'/></td>
         </tr>
      </table>
      </fieldset>
      <table>
         <tr>
         <td><input type='submit' name='Boton2' value='Reporte'/></td>
         <td><input type='submit' name='Boton2' value='Cancelar'/></td>
         </tr>
      </table>
   {elseif $scBehavior eq "2"}
         <fieldset style="width:1000px" align="center">
            <legend><font face="calibri" size=4 color=blue>Archivos Anexos</font></legend>
            <table border=0 cellspacing="0" cellpadding="0" style="font-size:11px">
                  <tr>
                  <td align='center'>
				  <a href='{$scFilRet}' target="_blank">Reporte de Informe de Investigación</a>
                  </tr>
            </table>
         </fieldset>
         <table>
            <tr>
               <td><input type="submit" name="Boton3" value="Cancelar"/></td>
            </tr>
         </table>
   {/if}
   </div>
   </div>
   <div id='content'></div>
   <div id='footer'></div>
   </div>
</form>
</body> 
</html>