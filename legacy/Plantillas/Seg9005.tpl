<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<html>
<head>
   <script type="text/javascript" src="CSS/Libreria.js"></script>
   <link href="CSS/Sixavi.css" media="screen" rel="stylesheet" type="text/css" />
   <title>Seguridad Operativa</title>
</head>
<body>
<form method = "post" action = "Seg9005.php">
   <div class="wrapper">
   <div id="MH">
      <ul>
      <li style="margin-left: 50px;"><h1>Búsqueda</h1></li>
      <li style="float:right;margin-right: 100px;"><a onclick="self.location = 'index.php'"> Cerrar Sesión</a></li>
      </ul>
   </div>
   <div id="topnavbar"></div>
   <div id="navigation">
   <div id="navcontainer">
   {if $scBehavior eq '0'}
      <table>
         <tr>              
         <td>Nombre:</td>
         <td><input maxlength="15" type="text"  name="pcNombre" value=''></td>
         </tr>
      </table>
      <table>
         <tr>
         <td><input type="submit" name="Aplicar" value="Aplicar"/></td>
         <td><input type="button" value="Salir" onclick="self.location = '{$scVolver}'" /></td>
         </tr>
      </table>
   {elseif $scBehavior eq '1'}
      <div style="overflow:scroll; height:500px; width:800px; overflow:auto; border: 1px">
      <table border = 2 style = "font-size:11px">
         <th align="center">Código</th>
         <th width="500" align="center">Nombre</th>
         <th align="center"><img src = "CSS/Check.jpg" width="25" height="25"></th>
         {foreach from = $saCodPer item = i}               
            <tr>
            <td>{$i['0']}</td><td>{$i['1']}</td>
            <td><input type = "radio" name = "pcCodPer" value = "{$i['0']}"/></td>
            </tr>
         {/foreach}
      </table>
      </div>
      <table>
         <tr>
         <td><input type="submit" name="Aceptar" value="Aceptar" /></td>         
         <td><input type="button" value="Salir" onclick="self.location = '{$scVolver}'" /></td>
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
