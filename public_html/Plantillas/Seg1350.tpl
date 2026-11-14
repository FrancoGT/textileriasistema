<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<html>
<head>
   <title>Seguridad Operativa</title>
   <link href="CSS/Sixavi.css" media="screen" rel="stylesheet" type="text/css" />
</head>
<body>
<form method="post" action="Seg1350.php">
   <div class="wrapper">
   <div id="MH">
      <ul>
      <li style="margin-left: 50px;"><h1>Mantenimiento de Emails de Areas</h1></li>
      <li style="float:right;margin-right: 100px;"><a onclick="self.location = 'index.php'"> Cerrar Sesión</a></li>
      </ul>
   </div>
   <div id="topnavbar"></div>
   <div id="navigation">
   <div id="navcontainer">
      <table border=2 style="font-size:11px">
         <tr>
         <th>Id</th>
         <th>Código</th>
         <th>Descripción</th>
         <th>Emails</th>
         </tr>
         {foreach from = $saData item = i}               
            <tr>
            <td align='center'>{$i[0]}<input type="hidden" name="paData[{$i[0]}][0]" value="{$i[0]}"/></td>
            <td align='center'>{$i[1]}<input type="hidden" name="paData[{$i[0]}][1]" value="{$i[1]}"/>
			                          <input type="hidden" name="paData[{$i[0]}][4]" value="{$i[4]}"/></td>
            <td>{$i[2]}<input type="hidden" name="paData[{$i[0]}][2]" value="{$i[2]}"/></td>
            <td><textarea name="paData[{$i[0]}][3]" rows="4" cols="100">{$i[3]}</textarea></td>
            </tr>
         {/foreach}
      </table>
   <table>
      <tr>
      <td><input type="submit" name="Boton1" value="Grabar" /></td>
      <td><input type="submit" name="Boton1" value="Salir" /></td>
      </tr>
   </table> 
   </div>
   </div>
   <div id="content"></div>
   <div id="footer"></div>
   </div>   
</form>
</body> 
</html>
