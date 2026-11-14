<meta http-equiv='Content-Type' content='text/html; charset=UTF-8'>
<html>
<head>
   <link href='CSS/Sixavi.css' media='screen' rel='stylesheet' type='text/css' />
   <script src="CSS/Libreria.js" language="JavaScript"></script>
   <title>Seguridad Operativa</title>
</head>
<body>
<form method='post' action='Seg2220.php#no-back'>
   <div class='wrapper'>
   <div id="MH">
      <ul>
      <li style="margin-left: 50px;"><h1>Datos Adicionales de Incidentes</h1></li>
      <li style="float:right;margin-right: 100px;"><a onclick="self.location = 'index.php'"> Cerrar Sesión</a></li>
      </ul>
   </div>
   <div id='topnavbar'></div>
   <div id='navigation'>
   <div id='navcontainer'>
   {if $scBehavior eq '0'}
      <fieldset style='width:500px' align='center'>
      <table border=0>
         <tr>
         <td>Número Incidente</td>
         <td><input type='text' name='pcNroInc' value='' maxlength='6' size='10' align='center' autofocus/></td>
         </tr>
      </table>  
      </fieldset>
      <table>
         <tr>
         <td><input type='submit' name='Boton1' value='Aplicar'/></td>
         <td><input type='submit' name='Boton1' value='Buscar'/></td>
         <td><input type='submit' name='Boton1' value='Salir'/></td>
         </tr>
      </table>
   {elseif $scBehavior eq '1'}
      <fieldset style='width:950px' align='center'>
      <table border=0 style='font-size:11px'>
         <tr>
         <td><font color=blue>{$scNroInc}</font><input type='hidden' name='pcNroInc' value='{$scNroInc}' size='6'/></td>
         <td><textarea class='memo' name='pmDatos' style="background:#FFFFFF" disabled>{$smDatos}</textarea></td>
      </table>
      </fieldset>
      <fieldset style='width:950px' align='center'>
      <legend align="left"><font face='calibri' size=3 color=blue>Evaluación del Reporte</font></legend>
      <table style='font-size:11px'>
         <tr>
         <td>Consecuencia</td>
         <td>
            <select name='pcConsec' style= font-size:11px>
            {foreach from=$saConsec item=i}
               <option value='{$i[0]}' {if $scConsec eq $i[0]} selected {/if}> {$i[1]}</option>
            {/foreach}
            </select>
         </td>
         <td></td>
         <td>Potencialidad</td>
         <td>
            <select name='pcPotenc' style= font-size:11px>
            {foreach from = $saPotenc item = i}
               <option value='{$i[0]}' {if $scPotenc eq $i[0]} selected {/if} > {$i[1]}</option>
            {/foreach}
            </select>
         </td>
         <td></td>
         <td>Responsable SSO</td>
         <td>
            <select name='pcCodPer' style= font-size:11px>
            {foreach from = $saPerCod item = i}
               <option value='{$i[0]}' {if $scCodPer eq $i[0]} selected {/if} > {$i[1]}</option>
            {/foreach}
            </select>
         </td>
         <td></td>
         <td>¿Requiere Investigación?</td>
         <td><input type='checkbox' name='pcReqInv' value='S' {if $scReqInv eq 'S'} checked {/if}/></td>
         </tr>
      </table>
      <table style='font-size:11px'>
         <tr>
         <td>Análisis Preliminar</td>
         <td>
         <textarea class='memo' name='pmAnaPre'>{$smAnaPre}</textarea>
         </td>
      </table>
      </fieldset>
      <fieldset style='width:950px' align='center'>
      <legend align="left"><font face='calibri' size=3 color=blue>Plan de Acción</font></legend>
      <table border=1 style='font-size:11px'>
         <tr>
         <th width='50px' align='center'>Id</th>
         <th width='300px' align='center'>Tipo</th>
         <th width='300px'>Descripción</th>
         <th width='300px' align='center'>Responsable</th>
         <th width='300px' align='center'>Fecha</th>
         <th width='300px' align='center'>Condición</th>
         <th width='50px' align='center'><img src='CSS/anular.bmp'/></th>
         </tr>
         {foreach from = $saData item = i}
            <tr>
            <td align='center'>{$i[0]}<input type='hidden' name='paData[{$i[0]}][0]' value='{$i[0]}'/></td>
            <td align='center'>
               <select  width='200' style='width: 190px; font-size:11px' name='paData[{$i[0]}][1]'>
               {foreach from = $saAccTom item = j}
                  <option value='{$j[0]}' {if $i[1] eq $j[0]} selected {/if}> {$j[1]} </option>
               {/foreach}
               </select>
            </td>
            <td><input type='text' maxlength='250' name='paData[{$i[0]}][2]'  value='{$i[2]}' size='50' /></td>
            <td align='center'>
               <select  width='200' style='width: 190px; font-size:11px' name='paData[{$i[0]}][3]'>
               {foreach from = $saCodPer item = j}
                  <option value='{$j[0]}' {if $i[3] eq $j[0]} selected {/if}> {$j[1]} </option>
               {/foreach}
               </select>
            </td>
            <td><input type='text' name='paData[{$i[0]}][4]' value='{$i[4]}' size='12' placeholder='aaaa-mm-dd' onKeyPress="return fDate(event)"/></td>
            <td align='center'>
               <select  width='200' style='width:110px; font-size:11px' name='paData[{$i[0]}][5]'>
               {foreach from = $saCondic item = j}
                  <option value='{$j[0]}' {if $i[5] eq $j[0]} selected {/if}> {$j[1]} </option>
               {/foreach}
               </select>
            </td>
            <td align='center'><input type='checkbox' name='paCheck[]' value='{$i[0]}'/></td>
            </tr>
         {/foreach}
         <table>
            <tr>
            <td><input type='submit' name='Boton2' value='Nuevo' class='boton'/></td>
            <td><input type='submit' name='Boton2' value='Eliminar' class='boton'/></td>
            </tr>
         </table>
      </table>
      </fieldset>
      <table>
         <tr>
         <td><input type='submit' name='Boton3' value='Grabar'/></td>
         <td><input type='submit' name='Boton3' value='Cancelar'/></td>
         </tr>
      </table>
   {/if}
   </div>
   </div>
   <div id='content'></div>
   <div id='footer'></div>
   </div>
</form>
</body> 
</html>