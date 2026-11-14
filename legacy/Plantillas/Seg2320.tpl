<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<html>
<head>
   <link href="CSS/Sixavi.css" media="screen" rel="stylesheet" type="text/css" />
   <title>Datos Adicionales de Incidentes</title>
</head>
<body>
<form method = "post" action = "Seg2320.php#no-back">
   <div class="wrapper">
   <div id="MH">
      <ul>
      <li style="margin-left: 50px;"><h1>Seguimiento de Plan de Acción ACR</h1></li>
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
         <td>Número de Incidente: [{$scNroInc}]<input type = "hidden" name = "pcNroInc" value="{$scNroInc}" size='6'/></td>
		 <tr></tr>
		 <td><textarea class="memo" name = "pmDatos" style="background:#FFFFFF" disabled>{$smDatos}</textarea></td>
      </table>
      </fieldset>
      <fieldset style="width:1000px" align="center">
      <legend align ='left'><font size = 3 color = blue>Seguimiento</font></legend>
      <table border = 1 cellspacing="0" cellpadding="0" style="font-size:11px">
         <tr>
         <th width='50px' align='center'>Id</th>
         <th width='300px' align='center'>Tipo</th>
         <th width='300px' align='center'>Descripción</th>
         <th width='300px' align='center'>Responsable</th>
         <th width='300px' align='center'>F. Planificada</th>
         <th width='300px' align='center'>F. de Cierre</th>
         <th width='300px' align='center'>Condición</th>
         <th width='100px' align='center'>Editar</th>
         </tr>
         {foreach from = $saData item = i}
            <tr>
            <td align='center'>{$i[0]}<input type = "hidden" name = "paData[{$i[0]}][0]" value="{$i[0]}"/></td>
            <td align='center'>
               <select  width="200" style="width: 190px; font-size:11px" name = "paData[{$i[0]}][1]" disabled>
               {foreach from = $saAccTom item = j}
                  <option value = "{$j[0]}" {if $i[1] eq $j[0]} selected {/if}> {$j[1]} </option>
               {/foreach}
               </select>
            </td>
            <td align='center'>{$i[2]}</td>
            <td align='center'>
               <select  width="200" style="width: 190px; font-size:11px" name = "paData[{$i[0]}][3]" disabled>
               {foreach from = $saCodPer item = j}
                  <option value = "{$j[0]}" {if $i[3] eq $j[0]} selected {/if}> {$j[1]} </option>
               {/foreach}
               </select>
            </td>
            <td align='center'>{$i[4]}</td>
            <td align='center'>{$i[5]}</td>
            <td align='center'>
               <select  width="200" style="width:110px; font-size:11px" name = "paData[{$i[0]}][6]" disabled>
               {foreach from = $saCondic item = j}
                  <option value = "{$j[0]}" {if $i[6] eq $j[0]} selected {/if}> {$j[1]} </option>
               {/foreach}
               </select>
            </td>
            <td align='center'><input type = "radio" name = "paCheck[]" value = "{$i[0]}"/></td>
            </tr>
         {/foreach}
      </table>
      <table>
         <tr>
         <td><input type="submit" name="Editar" value="Editar" class="boton"/></td>
         </tr>
      </table>
      <table border = 0 cellspacing="0" cellpadding="0" style="font-size:11px">
         <tr>
         <td>Eficaz: <input type = 'checkbox' name = 'pcEficaz' value='S' {if $scEficaz eq 'S'} checked {/if}/></td>
		 </tr><tr>
		 <td>Comentarios</td>
		 <tr></tr>
         <td><textarea class="memo" name = "pmComent">{$smComent}</textarea></td>
      </table>
      <table>
         <tr>
         <td><input type="submit" name="Grabar" value="Grabar"/></td>
         <td><input type="submit" name="Cancelar" value="Cancelar"/></td>
         </tr>
      </table>
   {elseif $scBehavior eq '2'}
      <fieldset style="width:1000px" align="center">
      <legend><font face = "calibri" size = 4 color = blue>Seguimiento</font></legend>
      <table border = 0 cellspacing="0" cellpadding="0" style="font-size:11px">
         <tr>
         <td>Número de Incidente</td>
         <td>{$scNroInc}<input type = "hidden" name = "pcNroInc" value="{$scNroInc}" size='6'/></td>
         <tr></tr>
         <td>Personal</td>
         <td>{$scNombre}</td>
         <tr></tr>
         <td>Fecha</td>
         <td>{$scFecha1}</td>
         <tr></tr>
         <td>Descripción</td>
         <td>{$scDescri}</td>
         <tr></tr>
         <td>Crónica</td>
		 <td><textarea class="memo" name = "pmCronic" style="background:#FFFFFF" disabled>{$smCronic}</textarea></td>
         {if $scCondic eq 'P'}
            <tr></tr>
            <td>Comentarios</td>
            <td><textarea class="memo" name = "pmComent" placeholder='Nuevos comentarios ...'></textarea></td>
         {/if}
         <tr></tr>
         <td>Condición</td>
         <td>
            <select style="font-size:11px" name = "pcCondic" {if $scCondic != 'P'} disabled {/if}>
            {foreach from = $saCondic item = i}
               <option value = "{$i[0]}" {if $scCondic eq $i[0]} selected {/if}> {$i[1]} </option>
            {/foreach}
            </select>
         </td>
      </table>
      </fieldset>
      <table>
         <tr>
         {if $scCondic eq 'P'}
             <td><input type="submit" name="Grabar1" value="Grabar"/></td>
         {/if}
         <td><input type="submit" name="Cancelar1" value="Cancelar"/></td>
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