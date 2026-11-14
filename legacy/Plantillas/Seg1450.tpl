<!DOCTYPE html>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<html>
<head>
   <link href="CSS/Sixavi.css" media="screen" rel="stylesheet" type="text/css"/>
   <title>Seguridad Operativa</title>
</head>
<body>
<form action="Seg1450.php#no-back" method="post">
   <div class="wrapper">
   <div id="MH">
      <ul>
      <li style="margin-left: 50px;"><h1>Registro de Licencias de Conducir Vehículos Terrestres</h1></li>
      <li style="float:right;margin-right: 100px;"><a onclick="self.location = 'index.php'"> Cerrar Sesión</a></li>
      </ul>
   </div>
   <div id="topnavbar"></div>
   <div id="navigation">
   <div id="navcontainer">
   {if $scBehavior eq "0"}
      <table>
         <tr>
         <td>Personal:</td>
         <td>
            <select name = "pcCodPer">
            {foreach from = $saCodPer item = i}
               <option value="{$i[0]}">{$i[1]}</option>
            {/foreach}
            </select>
         </td>
         </tr>
      </table>
      <table>
         <td><input type="submit" name="Aplicar" value="Aplicar"/></td>
         <td><input type="button" value="Salir" onclick="self.location = 'Mnu0000.php?Id=2000'"/></td>
      </table>
   {elseif $scBehavior eq "1"}
      <table>
         <tr>
         <td>Personal:</td>
         <td><p class="datos"><p class="datos">{$scCodPer} - {$scNombre} - {$scNroDni} - {$scDesSec}</p></p><input type="hidden" name="pcCodPer" value="{$scCodPer}"/></td>
         </tr>
      </table>
      <table border = 1 cellspacing="0" cellpadding="0" style="font-size:11px">
         <tr>
         <th style="width:10%" align='center'>Id</th>
         <th style="width:10%" align='center'>Tipo Licencia</th>
         <th style="width:10%" align='center'>Fecha Vigencia</th>
         <th style="width:10%" align='center'>Fecha Caducidad</th>
         <th style="width:10%" align='center'>Número</th>
         <th style="width:10%" align='center'>Restricciones</th>
         <th style="width:10%" align='center'>Estado</th>
         <th style="width:10%" align='center'><img src="CSS/anular.bmp" /></th>
         </tr>
         {foreach from = $saData item = i}
            <tr>
            <td align='center'>{$i[0]}<input type = "hidden" name = "paData[{$i[0]}][0]" value="{$i[0]}"/></td>
            <td align='center'>
               <select  width="200" style="width: 190px; font-size:11px" name = "paData[{$i[0]}][1]">
               {foreach from = $saTipLic item = j}
                  <option value = "{$j[0]}" {if $i[1] eq $j[0]} selected {/if}> {$j[1]} </option>
               {/foreach}
               </select>
            </td>
            <td align='center'><input type = "text" name = "paData[{$i[0]}][2]" value="{$i[2]}"/></td>
            <td align='center'><input type = "text" name = "paData[{$i[0]}][3]" value="{$i[3]}"/></td>
            <td align='center'><input type = "text" name = "paData[{$i[0]}][4]" value="{$i[4]}"/></td>
            <td align='center'>
               <select  width="200" style="width: 190px; font-size:11px" name = "paData[{$i[0]}][5]">
               {foreach from = $saRestri item = j}
                  <option value = "{$j[0]}" {if $i[5] eq $j[0]} selected {/if}> {$j[1]} </option>
               {/foreach}
               </select>
            </td>
            <td align='center'>
               <select  width="200" style="width: 190px; font-size:11px" name = "paData[{$i[0]}][6]">
               {foreach from = $saEstado item = j}
                  <option value = "{$j[0]}" {if $i[6] eq $j[0]} selected {/if}> {$j[1]} </option>
               {/foreach}
               </select>
            </td>
            </td><td align='center'><input type = "checkbox" name = "paCheck[]" value = "{$i[0]}"/></td>
            </tr>
         {/foreach}
      </table>
      <table>
         <tr>
         <td><input type="submit" name="Nuevo" value="Nuevo" class="boton"/></td>
         <td><input type="submit" name="Eliminar" value="Eliminar" class="boton"/></td>
         </tr>
      </table>
      <table>
         <tr>
         <td><input type="submit" name="Grabar" value="Grabar"/></td>
         <td><input type="submit" name="Cancelar" value="Cancelar"/></td>
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
