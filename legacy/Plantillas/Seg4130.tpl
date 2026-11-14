<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<html>
<head>
   <link href="CSS/Sixavi.css" media="screen" rel="stylesheet" type="text/css" />
   <title>Seguridad Operativa</title>
</head>
<body>
<form method = "post" action = "Seg4130.php#no-back">
   <div class="wrapper">
   <div id="MH">
      <ul>
      <li style="margin-left: 50px;"><h1>Reporte de Sanciones</h1></li>
      <li style="float:right;margin-right: 100px;"><a onclick="self.location = 'index.php'"> Cerrar Sesión</a></li>
      </ul>
   </div>
   <div id="topnavbar"></div>
   <div id="navigation">
   <div id="navcontainer">
   {if $scBehavior eq '0'}
      <table border = 0>
         <tr>
         <td><input type="radio" name="pcTipo" value="1"> Por Sanción</td>
         </tr><tr>
         <td><input type="radio" name="pcTipo" value="2"> Por Empleado</td>
         </tr>
      </table>
      <table>
         <tr>
         <td><input type="submit" name="Reporte" value="Reporte"/></td>
         <td><input type="submit" name="Excel" value="Excel"/></td>
         <td><input type="button" value="Salir" onclick="self.location = 'Mnu0000.php?Id=6001'"/></td>
         </tr>
      </table>
   {elseif $scBehavior eq '1'}
      <fieldset style="width:1000px" align="center">
      <table border = 0 cellspacing="0" cellpadding="0" style="font-size:11px">
         <a href="{$scFilRet}" target="_blank">Archivo de Salida</a>
      </table>
      </fieldset>
      <table>
         <tr>
         <td><input type = "submit" name = "Boton2" value = "Cancelar" /></td>
         </tr>
      </table>
   {elseif $scBehavior eq "2"}
         <fieldset style="width:1000px" align="center">
            <legend><font face = "calibri" size = 4 color = blue>Archivos Anexos</font></legend>
            <table border = 0 cellspacing="0" cellpadding="0" style="font-size:11px">
               {foreach from = $saData item = i}
                  <tr>
                  <td align='center'>
                  <a href="{$i[0]}" target="_blank">{$i[1]}</a>                
                  </tr>
               {/foreach}            
            </table>
         </fieldset>      
         <table>
            <tr>                        
               <td><input type="submit" name="Boton4" value="Cancelar"/></td>
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