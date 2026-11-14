<!DOCTYPE html>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<html>
<style type="text/css"> 
</style> 
<head>
   <link href="CSS/Sixavi.css" media="screen" rel="stylesheet" type="text/css"/>
   <title>Gestión de Seguridad</title>
</head>
<body>
<form action="Seg1410.php#no-back" method="post">
   <div class="wrapper">
   <div id="MH">
      <ul>
      <li style="margin-left: 50px;"><h1>Histórico de Personal</h1></li>
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
         <td><p class="datos"><p class="datos">{$scCodPer} - {$scNombre}</p><input type="hidden" name="pcCodPer" value="{$scCodPer}"/></td>
         </tr>
      </table>
      <table width="100%" border=1 style="font-size:11px">
         <tr>
         <th align="center">Id</th>
         <th align="center">Empresa</th>
         <th align="center">Fecha<br>Inicial</th>
         <th align="center">Puesto</th>
         <th align="center">Categoría</th>
		 <th align="center">Sector</th>
         <th align="center">Area</th>
         <th align="center">Proyecto</th>
         <th align="center"><img src="CSS/anular.bmp"/></th>
         </tr>
         {foreach from = $saData item = i}
            <tr>
            <td align='center'>{$i[0]}<input type="hidden" name="paData[{$i[0]}][0]" value="{$i[0]}"/></td>
            <td align='center'>
               <select width="150px" style="width:145px" name="paData[{$i[0]}][1]">
               {foreach from = $saCodEmp item = j}
                  <option value="{$j[0]}" {if $i[1] eq $j[0]} selected {/if}> {$j[1]} </option>
               {/foreach}
               </select>
            </td>
            <td align='center'><input type="text" size="11" maxlength="10" name="paData[{$i[0]}][2]" value="{$i[2]}"/></td>
            <td align='center'>
               <select width="150px" style="width:145px" name="paData[{$i[0]}][3]">
               {foreach from = $saCarDis item = j}
                  <option value="{$j[0]}" {if $i[3] eq $j[0]} selected {/if}> {$j[1]} </option>
               {/foreach}
               </select>
            </td>
            <td align='center'>
               <select width="120px" style="width:115px" name="paData[{$i[0]}][4]">
               {foreach from = $saCargos item = j}
                  <option value="{$j[0]}" {if $i[4] eq $j[0]} selected {/if}> {$j[1]} </option>
               {/foreach}
               </select>
            </td>
            <td align='center'>
               <select width="120px" style="width:115px" name="paData[{$i[0]}][5]">
               {foreach from = $saSector item = j}
                  <option value="{$j[0]}" {if $i[5] eq $j[0]} selected {/if}> {$j[1]} </option>
               {/foreach}
               </select>
            </td>
            <td align='center'>
               <select width="120px" style="width:115px" name="paData[{$i[0]}][6]">
               {foreach from = $saAreas item = j}
                  <option value="{$j[0]}" {if $i[6] eq $j[0]} selected {/if}> {$j[1]} </option>
               {/foreach}
               </select>
            </td>
            <td align='center'>
               <select width="120px" style="width:115px" name="paData[{$i[0]}][7]">
               {foreach from = $saCodPry item = j}
                  <option value="{$j[0]}" {if $i[7] eq $j[0]} selected {/if}> {$j[1]} </option>
               {/foreach}
               </select>
            </td>
            <td align='center'><input type = "checkbox" name = "paCheck[]" value = "{$i[0]}"/></td>
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
