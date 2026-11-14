<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<html>
<head>
   <link href="CSS/Sixavi.css" media="screen" rel="stylesheet" type="text/css" />
   <style>
      #navcontainer a { font-size:small; display:block; color:#333; background-color:#E8E8E8; border:#333 1px solid;  width:250px; padding:5px 5px 5px 5px; text-decoration:none; }
      #navcontainer a:hover { background-color: FF6600; color: #FFF; }
   </style>
   <title>Datos Adicionales de Incidentes</title>
</head>
<body>
<form method = "post" action = "Seg2270.php#no-back" enctype="multipart/form-data">
   <div class="wrapper">
   <div id="MH">
      <ul>
      <li style="margin-left: 50px;"><h1>Archivos Anexos a Incidentes</h1></li>
      <li style="float:right;margin-right: 100px;"><a onclick="self.location = 'index.php'"> Cerrar Sesión</a></li>
      </ul>
   </div>
   <div id="topnavbar"></div>
   <div id="navigation">
   <div id="navcontainer">
   {if $scBehavior eq '0'}
      <fieldset style="width:500px" align="center">
      <table border = 0>
         <tr>
         <td>Número Incidente</td>
         <td><input type = "text" name = "pcNroInc" value="" size='6'/></td>
         </tr>
      </table>  
      </fieldset>
      <table>
         <tr>
         <td><input type="submit" name="Aplicar" value="Aplicar"/></td>
         <td><input type="submit" name="Buscar" value="Buscar"/></td>
         <td><input type="button" value="Salir" onclick="self.location = 'Mnu0000.php?Id=4002'"/></td>
         </tr>
      </table>
   {elseif $scBehavior eq '1'}
      <fieldset style="width:1000px" align="center">
      <legend><font face = "calibri" size = 4 color = blue>Incidentes</font></legend>
      <table border = 0 cellspacing="0" cellpadding="0" style="font-size:11px">
         <tr>
         <th>Número de Incidente</th>
         <th>Datos</th>
         </tr><tr>
         <td>{$scNroInc}<input type = "hidden" name = "pcNroInc" value="{$scNroInc}" size='6'/></td>
         <td><textarea class="memo" name = "pmDatos" disabled>{$smDatos}</textarea></td>
         </tr>
      </table>
      </fieldset>
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
         <td><input type="file" name="file" id="file"></input></td>
      </table>
      <table>	
	     <td><input type="submit" name="Subir" value="Subir"/></td>
         <td><input type="submit" name="Cancelar" value="Cancelar"/></td>
		 <td><input type="submit" name="Boton2" value="Siguiente"/></td>
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
