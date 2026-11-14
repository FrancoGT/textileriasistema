<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<html>
<head>
   <link href="CSS/Sixavi.css" media="screen" rel="stylesheet" type="text/css" />
   <title>Seguridad Operativa</title>
   <script src="CSS/Libreria.js" language="JavaScript"></script>
</head>
<body>
<form method = "post" action = "Seg2310.php#no-back">
   <div class="wrapper">
   <div id="MH">
      <ul>
      <li style="margin-left: 50px;"><h1>Ingreso de Plan de Acción ACR</h1></li>
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
      <legend align= "left"><font face = "calibri" size = 3 color = blue>Plan de Acción</font></legend>
     <table border = 2 width=930 style="font-size:11px">
         <tr>
         <th width=30 align='center'>Id</th>
         <th width=160 align='center'>Tipo</th>
         <th width=300 >Descripcion</th>
         <th width=180 align='center'>Responsable</th>
         <th width=100 align='center'>Fecha Planificada</th>
         <th width=120 align='center'>Condición</th>
         <th width=40 align='center'><img src="CSS/anular.bmp"/></th>
         </tr>
         {foreach from = $saData item = i}
            <tr>
            <td align='center'>{$i[0]}<input type = "hidden" name = "paData[{$i[0]}][0]" value="{$i[0]}"/></td>
            <td align='center'>
               <select width="200" style="width: 190px; font-size:11px" name = "paData[{$i[0]}][1]">
               {foreach from = $saAccTom item = j}
                  <option value = "{$j[0]}" {if $i[1] eq $j[0]} selected {/if}> {$j[1]} </option>
               {/foreach}
               </select>
            </td>
            <td><input type = "text" maxlength = "245" size = '70' name = "paData[{$i[0]}][2]"  value="{$i[2]}" /></td>
            <td align='center'>
               <select width="250" style="width: 190px; font-size:11px" name = "paData[{$i[0]}][3]">
               {foreach from = $saCodPer item = j}
                  <option value = "{$j[0]}" {if $i[3] eq $j[0]} selected {/if}> {$j[1]} </option>
               {/foreach}
               </select>
            </td>
            <td><input type = "text" name = "paData[{$i[0]}][4]" value="{$i[4]}" size='12' placeholder="aaaa-mm-dd" onKeyPress="return fDate(event)"/></td>
            <td align='center'>
               <select  width="200" style="width:110px; font-size:11px" name = "paData[{$i[0]}][5]">
               {foreach from = $saCondic item = j}
                  <option value = "{$j[0]}" {if $i[5] eq $j[0]} selected {/if}> {$j[1]} </option>
               {/foreach}
               </select>
            </td>
            <td align='center'><input type = "checkbox" name = "paCheck[]" value = "{$i[0]}"/></td>
            </tr>
         {/foreach}
         <table>
            <tr>
            <td><input type="submit" name="Nuevo" value="Nuevo" class="boton"/></td>
            <td><input type="submit" name="Eliminar" value="Eliminar" class="boton"/></td>
            </tr>
         </table>
      </table>
      </fieldset>
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