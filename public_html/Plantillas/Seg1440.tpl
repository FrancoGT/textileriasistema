<!DOCTYPE html>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<html>
<head>
   <link href="CSS/Sixavi.css" media="screen" rel="stylesheet" type="text/css"/>
   <title>Seguridad Operativa</title>
</head>
<body>
<form action="Seg1440.php#no-back" method="post">
   <div class="wrapper">
   <div id="MH">
      <ul>
      <li style="margin-left: 50px;"><h1>Registro de Licencias de Conducir Vehículos Ferroviarios</h1></li>
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
         <td><input type="submit" name="Boton1" value="Aplicar"/></td>
         <td><input type="submit" name="Boton1" value="Salir"/></td>
      </table>
   {elseif $scBehavior eq "1"}
      <table>
         <tr>
         <td>Personal:</td>
         <td><p class="datos"><p class="datos">{$scCodPer} - {$scNombre} - {$scNroDni} - {$scDesSec}</p><input type="hidden" name="pcCodPer" value="{$scCodPer}"/></td>
         </tr>
      </table>
	  <table border=1 style="font-size:10px" width=1000px>
         <tr>
         <th width=20px align='center'>Id</th>
         <th width=120px align='center'>Tipo Licencia</th>
         <th width=80px align='center'>Fecha<br>Expedición</th>
         <th width=80px align='center'>Fecha<br>Expiración</th>
         <th width=80px align='center'>Número<br>Licencia</th>
         <th width=100px align='center'>Sector Autorizado</th>
         <th width=100px align='center'>Vehículos<br>Autorizados</th>
         <th width=100px align='center'>Restricciones</th>
         <th width=100px align='center'>Sector de Trabajo</th>
         <th width=100px align='center'>Estado</th>
         <th width=100px align='center'>Empresa</th>
         <th width=20px align='center'><img src="CSS/anular.bmp" /></th>
         </tr>
         {foreach from = $saData item = i}
            <tr>
            <td align='center'>{$i[0]}<input type="hidden" name="paData[{$i[0]}][0]" value="{$i[0]}"/></td>
            <td align='center'>
               <select  style="width: 120px; font-size:10px" name="paData[{$i[0]}][1]">
               {foreach from = $saTipLic item = j}
                  <option value = "{$j[0]}" {if $i[1] eq $j[0]} selected {/if}> {$j[1]} </option>
               {/foreach}
               </select>
            </td>
            <td width=80px align='center'><input type="text" name="paData[{$i[0]}][2]" value="{$i[2]}" size=10/></td>
            <td width=80px align='center'><input type="text" name="paData[{$i[0]}][3]" value="{$i[3]}" size=10/></td>
            <td width=80px align='center'><input type="text" name="paData[{$i[0]}][4]" value="{$i[4]}" size=12/></td>
            <td align='center'>
               <select tyle="width: 100px; font-size:10px" name="paData[{$i[0]}][5]">
               {foreach from = $saSecAut item = j}
                  <option value = "{$j[0]}" {if $i[5] eq $j[0]} selected {/if}> {$j[1]} </option>
               {/foreach}
               </select>
            </td>
            <td width=100px align='center'><input type="text" name="paData[{$i[0]}][6]" value="{$i[6]}"/></td>
            <td align='center'>
               <select style="width: 100px; font-size:10px" name="paData[{$i[0]}][7]">
               {foreach from = $saRestri item = j}
                  <option value = "{$j[0]}" {if $i[7] eq $j[0]} selected {/if}> {$j[1]} </option>
               {/foreach}
               </select>
            </td>
            <td align='center'>
               <select style="width: 100px; font-size:10px" name="paData[{$i[0]}][8]">
               {foreach from = $saSector item = j}
                  <option value = "{$j[0]}" {if $i[8] eq $j[0]} selected {/if}> {$j[1]} </option>
               {/foreach}
               </select>
            </td>
            <td align='center'>
               <select style="width: 100px; font-size:10px" name="paData[{$i[0]}][9]">
               {foreach from = $saEstado item = j}
                  <option value = "{$j[0]}" {if $i[9] eq $j[0]} selected {/if}> {$j[1]} </option>
               {/foreach}
               </select>
            </td>
            <td align='center'>
               <select style="width: 100px; font-size:11px" name="paData[{$i[0]}][10]">
               {foreach from = $saCodEmp item = j}
                  <option value = "{$j[0]}" {if $i[10] eq $j[0]} selected {/if}> {$j[1]} </option>
               {/foreach}
               </select>
            </td>
            </td><td align='center'><input type="checkbox" name="paCheck[]" value = "{$i[0]}"/></td>
            </tr>
         {/foreach}
      </table>
      <table>
         <tr>
         <td><input type="submit" name="Boton2" value="Nuevo" class="boton"/></td>
         <td><input type="submit" name="Boton2" value="Eliminar" class="boton"/></td>
         </tr>
      </table>
      <table>
         <tr>
         <td><input type="submit" name="Boton3" value="Grabar"/></td>
         <td><input type="submit" name="Boton3" value="Cancelar"/></td>
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
