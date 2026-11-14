<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<html>
<head>
   <link href="CSS/Sixavi.css" media="screen" rel="stylesheet" type="text/css" />
   <title>Seguridad Operativa</title>
</head>
<body>
<form method="post" action="Seg2250.php#no-back">
   <div class="wrapper">
   <div id="MH">
      <ul>
      <li style="margin-left: 50px;"><h1>Seguimiento de Incidentes</h1></li>
      <li style="float:right;margin-right: 100px;"><a onclick="self.location = 'index.php'"> Cerrar Sesión</a></li>
      </ul>
   </div>
   <div id="topnavbar"></div>
   <div id="navigation">
   <div id="navcontainer">
   {if $scBehavior eq '0'}
      <fieldset style="width:500px" align="center">
      <table border=0>
         <tr>
         <td>Número Incidente</td>
         <td><input type="text" name="pcNroInc" value="" size='6'/></td>
         </tr>
      </table>  
      </fieldset>
      <table>
         <tr>
         <td><input type="submit" name="Boton1" value="Aplicar"/></td>
         <td><input type="submit" name="Boton1" value="Buscar"/></td>
         <td><input type="submit" name="Boton1" value="Salir"/></td>
         </tr>
      </table>
   {elseif $scBehavior eq '1'}
      <fieldset style="width:950px" align="center">
      <table border=0 style="font-size:11px">
         <tr>
         <td>Número de Incidente</td>
         <td><font face="calibri" size=3 color=blue>{$scNroInc}</font><input type="hidden" name="pcNroInc" value="{$scNroInc}" size='6'/></td>
         </tr><tr>
         <td>Datos</td>
         <td><textarea class="memo" name="pmDatos" style="background:#FFFFFF" disabled>{$smDatos}</textarea></td>
      </table>
      </fieldset>
      <fieldset style="width:950px" align="center">
      <legend align='left'><font face="calibri" size=3 color=blue>Plan de Acción</font></legend>
      <table border=2 width=930 style="font-size:11px">
         <tr>
         <th width=30 align='center'>Id</th>
         <th width=80 align='center'>Tipo</th>
         <th width=275 align='center'>Descripción</th>
         <th width=200 align='center'>Responsable</th>
         <th width=100 align='center'>F.Planificada</th>
         <th width=100 align='center'>F.Ejecución</th>
         <th width=120 align='center'>Condición</th>
         <th width=25 align="center"><img src="CSS/Edit.png" style="width: 100%; height: 10%;"/></th>
         </tr>
         {foreach from = $saData item = i}
            <tr>
            <td align='center'>{$i[0]}<input type="hidden" name="paData[{$i[0]}][0]" value="{$i[0]}"/></td>
            <td align='center'>
               <select  width="200" style="width: 190px; font-size:11px" name="paData[{$i[0]}][1]" disabled>
               {foreach from = $saAccTom item = j}
                  <option value="{$j[0]}" {if $i[1] eq $j[0]} selected {/if}> {$j[1]} </option>
               {/foreach}
               </select>
            </td>
            <td>{$i[2]}</td>
            <td align='center'>
               <select  width="200" style="width: 190px; font-size:11px" name="paData[{$i[0]}][3]" disabled>
               {foreach from = $saCodPer item = j}
                  <option value="{$j[0]}" {if $i[3] eq $j[0]} selected {/if}> {$j[1]} </option>
               {/foreach}
               </select>
            </td>
            <td align='center'>{$i[4]}</td>
            <td align='center'>{$i[5]}</td>
            <td align='center'>
               <select  width="200" style="width:110px; font-size:11px" name="paData[{$i[0]}][6]" disabled>
                 {foreach from = $saCondic item = j}
                    <option value="{$j[0]}" {if $i[6] eq $j[0]} selected {/if}> {$j[1]} </option>
                 {/foreach}
               </select>
            </td>
            <td align='center'><input type="radio" name="paCheck[]" value="{$i[0]}"/></td>
            </tr>
         {/foreach}
         <table>
            <tr>
            <td><input type="submit" name="Boton2" value="Editar" class="boton"/></td>
            </tr>
         </table>
      </table>
      </fieldset>
      <table>
         <tr>
         <td><input type="submit" name="Boton2" value="Cancelar"/></td>
		 <td><input type="submit" name="Boton2" value="Siguiente"/></td>
         </tr>
      </table>
   {elseif $scBehavior eq '2'}
      <fieldset style="width:950px" align="center">
      <table border=0 style="font-size:11px">
         <tr>
         <td>Número de Incidente</td>
         <td><font color=blue>{$scNroInc}</font><input type="hidden" name="pcNroInc" value="{$scNroInc}"/></td>
         <tr></tr>
         <td>Personal</td>
         <td><font color=blue>{$scNombre}</font></td>
         <tr></tr>
         <td>Fecha</td>
         <td><font color=blue>{$scFecha1}</font></td>
         <tr></tr>
         <td>Descripción</td>
         <td><font color=blue>{$scDescri}</font></td>
         <tr></tr>
         <td>Crónica</td>
         <td><textarea class="memo" name="pmCronic" style="background:#FFFFFF" disabled>{$smCronic}</textarea></td>
         <tr></tr>
         <td>Comentarios</td>
         <td><textarea class="memo" name="pmComent" placeholder='Nuevos comentarios ...' {if $scCondic eq 'E'} style="background:#FFFFFF" disabled {/if}></textarea></td>
         <tr></tr>
         <td>Condición</td>
         <td>
            <select style="font-size:11px" name="pcCondic" {if $scCondic eq 'E'} disabled {/if}>
            {foreach from = $saCondic item = i}
               <option value="{$i[0]}" {if $scCondic eq $i[0]} selected {/if}> {$i[1]} </option>
            {/foreach}
            </select>
         </td>
      </table>
      </fieldset>
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