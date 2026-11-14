<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<html>
<head>
   <link href="CSS/Sixavi.css" media="screen" rel="stylesheet" type="text/css" />
   <title>Seguridad Operativa</title>
</head>
<body>
<form method="post" action="Seg2280.php#no-back" name="formulario">
   <div class="wrapper">
   <div id="MH">
      <ul>
      <li style="margin-left: 50px;"><h1>Descripción Detallada del Incidente</h1></li>
      <li style="float:right;margin-right: 100px;"><a onclick="self.location = 'index.php'"> Cerrar Sesión</a></li>
      </ul>
   </div>
   <div id="topnavbar"></div>
   <div id="navigation">
   <div id="navcontainer">
   {if $scBehavior eq '0'}
      <fieldset style="width:500px" align="center">
      <table border=0>
         <tr>
         <td>Número Incidente</td>
         <td><input type="text" name="pcNroInc" value="" size='6'/></td>
         </tr>
      </table>  
      </fieldset>
      <table>
         <tr>
         <td><input type="submit" name="Aplicar" value="Aplicar"/></td>
         <td><input type="submit" name="Buscar" value="Buscar"/></td>
         <td><input type="button" value="Salir" onclick="self.location='Mnu0000.php?Id=4002'"/></td>
         </tr>
      </table>
   {elseif $scBehavior eq '1'}
      <fieldset style="width:900px" align="center">
      <table border=0 cellspacing="0" cellpadding="0" style="font-size:11px">
         <tr>
         <td>Número de Incidente</td>
         <td><font color="blue">{$scNroInc}<input type="hidden" name="pcNroInc" value="{$scNroInc}"/></td>
         <tr></tr>
         <td>Datos</td>
         <td><textarea class="memo" name="pmDatos" style="background:#FFFFFF" disabled>{$smDatos}</textarea></td>
         <tr></tr>
         <td>Descripción a Detalle del Incidente</td>
         <td><textarea class="memo" name="pmDesDet">{$smDesDet}</textarea></td>
         <tr></tr>
         <td>Análisis Cronológico del Incidente</td>
         <td><textarea class="memo" name="pmEveCro">{$smEveCro}</textarea></td>
         <tr></tr>
      </table>
      <table>
         <tr>
         <td><input type="submit" name="Grabar" value="Grabar"/></td>
         <td><input type="submit" name="Cancelar" value="Cancelar"/></td>
         </tr>
      </table>
      </fieldset>
   {/if}
   </div>
   </div>
   <div id="content"></div>
   <div id="footer"></div>
   </div>
</form>
</body> 
</html>