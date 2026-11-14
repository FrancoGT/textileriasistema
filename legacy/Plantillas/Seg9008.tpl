<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<html>
<head>
   <script type="text/javascript" src="CSS/Sixavi.js"></script>
   <script src="CSS/Libreria.js" language="JavaScript"></script>
   <link href="CSS/Sixavi.css" media="screen" rel="stylesheet" type="text/css" />
   <title>Seguridad Operativa</title>
</head>
<body>
<form method="post" action="Seg9008.php#no-back">
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
      <table border=2 style="font-size:11px">
         <tr>
         <td>Desde</td>
         <td><input type="text" name="pdFecIni" value="{$sdFecIni}" maxlength="10" onKeyPress="return fDate(event)"></td>
         </tr><tr>
         <td>Hasta</td>
         <td><input type="text" name="pdFecFin" value="{$sdFecFin}" maxlength="10" onKeyPress="return fDate(event)"></td>
         </tr><tr>
         <td>Estado</td>
         <td>
            <select name="pcEstado">
            {foreach from = $saEstado item = i}
               <option value="{$i[0]}"> {$i[1]} </option>
            {/foreach}
            </select>
         </td>
      </table>
      <table>
         <tr>
         <td><input type="submit" name="Boton1" value="Aplicar" /></td>
         <td><input type="submit" name="Boton1" value="Salir" /></td>
         </tr>
      </table>  
   {elseif $scBehavior eq '1' or $scBehavior eq '2'}
      <div style="overflow:scroll; height:400px; width:900px; overflow:auto; border: 3px;">     
      <table border=2 width=650 style="font-size:11px">
         <tr>
         <th width=100 align='center'>Número</th>
         <th width=400 align='center'>Descripción</th>
         <th width=100 align='center'>Fecha</th>
         <th width=100 align='center'>Estado</th>
         <th width=30 align='center'><img src="CSS/Check.jpg" height="32" width="32"> </th>
         </tr>
         {foreach from = $saNroInc item = i}
            <tr>
            <td align="center">{$i['0']}</td>
            <td>{$i['1']}</td>
            <td align="center">{$i['2']}</td>
            <td align="center">{$i['3']}</td>
            {if $scBehavior eq '1'}
               <td align="center"><input type="radio" name="pcNroInc" value="{$i['0']}"/></td>
            {else}
               <td align="center"><input type="checkbox" name="paIncNro[]" value="{$i['0']}"/></td>
            {/if}
            </tr>
         {/foreach}
      </table>
     </div>
      <table>
         <tr>
         <td><input type="submit" name="Boton2" value="Aceptar" /></td>
         <td><input type="submit" name="Boton2" value="Filtrar" /></td>
         <td><input type="submit" name="Boton2" value="Salir" /></td>
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