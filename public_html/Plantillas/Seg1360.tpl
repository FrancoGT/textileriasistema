<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<html>
<head>
   <title>Seguridad Operativa</title>
   <link href="CSS/Sixavi.css" media="screen" rel="stylesheet" type="text/css" />
</head>
<body>
<form method="post" action="Seg1360.php#no-back">
   <div class="wrapper">
   <div id="MH">
      <ul>
      <li style="margin-left: 50px;"><h1>Mantenimiento de Tipos de Licencia</h1></li>
      <li style="float:right;margin-right: 100px;"><a onclick="self.location = 'index.php'"> Cerrar Sesión</a></li>
      </ul>
   </div>
   <div id="topnavbar"></div>
   <div id="navigation">
   <div id="navcontainer">
      <table border=2 style="font-size:11px">
         <tr>
         <th width='30' align="center">Tipo</th>
         <th width='400' align="center">Descripción</th>
         <th width='40' align="center">Estado</th>
         <th width='400' align="center">Email</th>
         <th style="width: 30px;" align='center'><img src="CSS/anular.bmp" /></th>
         </tr>
         {foreach from = $saData item = i}               
            <tr>
            {if $i[5] eq 1}
               <td align='center'>{$i[1]}
                  <input type="hidden" name="paData[{$i[0]}][0]" value="{$i[0]}" />
                  <input type="hidden" name="paData[{$i[0]}][1]" value="{$i[1]}" />
               </td>
               <td>{$i[2]}<input type="hidden" name="paData[{$i[0]}][2]" value="{$i[2]}"  size="70"/></td>
            {else}
               <td align='center'>
                  <input type="hidden" name="paData[{$i[0]}][0]" value="{$i[0]}"/>
                  <input type="text" name="paData[{$i[0]}][1]" value="{$i[1]}" size="5" maxlength="3" autofocus/>
               </td>
               <td><input type="text" name="paData[{$i[0]}][2]" value="{$i[2]}" size="67" maxlenght="70" style="text-transform: uppercase"/></td>
            {/if}
            <td>
               <select name="paData[{$i[0]}][3]">
               {foreach from = $saEstado item = j}
                  <option value="{$j[0]}" {if $i[3] eq $j[0]} selected {/if} size="5"> {$j[1]}</option>
               {/foreach}
               </select>
            </td>
            <td><input type="text" name="paData[{$i[0]}][4]" value="{$i[4]}" size="70"/>
                <input type="hidden" name="paData[{$i[0]}][5]" value="{$i[5]}"/></td>
            {if $i[5] eq 1}
               <td></td>
            {else}
               <td align="center"><input type="radio" name="pcTipLic" value="{$i[0]}"/></td>
            {/if}
            </tr>
         {/foreach}
      </table>
   <table>
      <tr>
      <td><input type="submit" name="Boton1" value="Nuevo" class="boton"/></td>
      <td><input type="submit" name="Boton1" value="Eliminar" class="boton"/></td>
      </tr>
   </table>
   <table>
      <tr>
      <td><input type="submit" name="Boton2" value="Grabar" /></td>
      <td><input type="submit" name="Boton2" value="Salir" /></td>
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