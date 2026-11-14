<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<html>
<head>
   <link href="CSS/Sixavi.css" media="screen" rel="stylesheet" type="text/css" />
   <title>Datos Adicionales de Incidentes</title>
</head>
<body>
<form method = "post" action = "Seg4190.php">
   <div class="wrapper">
   <div id="header">
   <table>
      <tr>
      <td><img src="CSS/Softia.png" width="46" height="38" /></td>
      <td><p class="titulo">Personal y Unidades Involucrados en Incidente</p></td>
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
         <td>Número Incidente</td>
         <td><input type = "text" name = "pcNroInc" value="" size='6'/></td>
         </tr>
      </table>  
      </fieldset>
      <table>
         <tr>
         <td><input type="submit" name="Aplicar" value="Aplicar"/></td>
         <td><input type="submit" name="Buscar" value="Buscar"/></td>
         <td><input type="button" value="Salir" onclick="self.location = 'Mnu0000.php?Id=4000'"/></td>
         </tr>
      </table>
   {elseif $scBehavior eq '1'}
      <fieldset style="width:1000px" align="center">
      <legend><font face = "calibri" size = 4 color = blue>Incidentes</font></legend>
      <table border = 0 cellspacing="0" cellpadding="0" style="font-size:11px">
         <tr>
         <td>Número de Incidente</td>
         <td>[{$scNroInc}]<input type = "hidden" name = "pcNroInc" value="{$scNroInc}" size='6'/></td>
         <td>Datos</td>
         <td>
         <textarea class="memo" name = "pmDatos" disabled>{$smDatos}</textarea>
         </td>
      </table>
      </fieldset>
      <fieldset style="width:1000px" align="center">
      <legend><font face = "calibri" size = 4 color = blue>Personal Involucrado</font></legend>
      <table border = 1 cellspacing="0" cellpadding="0" style="font-size:11px">
         <tr>
         <th width='50px' align="center">Id</th>
         <th width='800px' align="center">Personal</th>
         <th width='300px' align="center">Empresa</th>
         <th width='300px' align="center">Puesto</th>
         <th width='300px' align="center">Nro.DNI</th>
         <th width='300px' align="center">Nombre</th>
         <th width='300px' align="center">Actividad</th>
         <th width='300px' align="center">Ubicación</th>
         </tr>
         {foreach from = $saCodPin item = i}
            <tr>
            <td align='center'>{$i[0]}<input type = "hidden" name = "paData[{$i[0]}][0]" value="{$i[0]}"/><input type = "hidden" name = "paData[{$i[0]}][1]" value="{$i[1]}"/></td>
            <td>{$i[2]}<input type = "hidden" name = "paData[{$i[0]}][2]" value="{$i[2]}"/><input type = "hidden" name = "paData[{$i[0]}][3]" value="{$i[3]}"/></td>
            <td align='center'>{$i[4]}<input type = "hidden" name = "paData[{$i[0]}][4]" value="{$i[4]}"/><input type = "hidden" name = "paData[{$i[0]}][5]" value="{$i[5]}"/></td>
            <td align='center'>{$i[6]}<input type = "hidden" name = "paData[{$i[0]}][6]" value="{$i[6]}"/></td>
            <td align='center'>{$i[7]}<input type = "hidden" name = "paData[{$i[0]}][7]" value="{$i[7]}"/></td>
            <td align='center'>{$i[8]}<input type = "hidden" name = "paData[{$i[0]}][8]" value="{$i[8]}"/></td>
            <td align='center'>{$i[9]}</td>
            <td align='center'>{$i[10]}</td>
         {/foreach}
      </table>
      </fieldset>
      <fieldset style="width:1000px" align="center">
      <legend><font face = "calibri" size = 4 color = blue>Unidades Involucradas</font></legend>
      <table border = 1 cellspacing="0" cellpadding="0" style="font-size:11px">
         <tr>
         <th width='50px'>Id</th>
         <th width='50px'>Unidad</th>
         <th width='300px'>Descripción</th>
         <th width='300px'>Daño</th>
         </tr>
         {foreach from = $saCodUin item = i}
            <tr>
            <td align='center'>{$i[0]}<input type = "hidden" name = "paData1[{$i[0]}][0]" value="{$i[0]}"/></td>
            <td align='center' disabled>
               <select  width="200" style="width: 190px; font-size:11px" name = "paData1[{$i[0]}][1]">
               {foreach from = $saCodUni item = j}
                  <option value = "{$j[0]}" {if $i[1] eq $j[0]} selected {/if}> {$j[1]} </option>
               {/foreach}
               </select>
            </td>
            <td>{$i[2]}</td>
            <td>{$i[3]}</td>
         {/foreach}
      </table>
      </fieldset>
      <table>
         <tr>
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