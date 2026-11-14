<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<html>
<head>
   <link href="CSS/Sixavi.css" media="screen" rel="stylesheet" type="text/css" />
   <title>Seguridad Operativa</title>
</head>
<body>
<form method = "post" action = "Seg2410.php">
   <div class="wrapper">
   <div id="header">
   <table>
      <tr>
      <td><img src="CSS/Softia.png" width="46" height="38" /></td>
      <td><p class="titulo">Reporte de Control de Velocidad por Radar</p></td>
      </tr>
   </table>
   </div>
   <div id="topnavbar"></div>
   <div id="navigation">
   <div id="navcontainer">
   {if $scBehavior eq '0'}
      <fieldset style="width:500px" align="center">
      <table border = 0>
         <tr>
         <td>Número de Evaluación</td>
         <td>{$scEvaTip}-<input type = "text" name = "pcNroEva" value="" size='5'/></td>
         </tr>
      </table>  
      </fieldset>
      <table>
         <tr>
         <td><input type="submit" name="Aplicar" value="Aplicar"/></td>
         <td><input type="submit" name="Buscar" value="Buscar"/></td>
         <td><input type="button" value="Salir" onclick="self.location = 'Mnu0000.php?Id=6000'"/></td>
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