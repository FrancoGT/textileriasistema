<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<html>
<head>
   <title>Seguridad Operativa</title>
   <link href="CSS/Sixavi.css" media="screen" rel="stylesheet" type="text/css"/>
   <script src="CSS/Libreria.js" language="JavaScript"></script>
</head>
<body>
<form method="post" action="Seg1290.php#no-back">
   <div class="wrapper">
   <div id="MH">
      <ul>
      <li style="margin-left: 50px;"><h1>Registro de Sanción por Empleado</h1></li>
      <li style="float:right;margin-right: 100px;"><a onclick="self.location = 'index.php'"> Cerrar Sesión</a></li>
      </ul>
   </div>
   <div id="topnavbar"></div>
   <div id="navigation">
   <div id="navcontainer">
   {if $scBehavior eq '0'}
      <fieldset>
      <table>
         <tr>              
         <td>Empleado:</td>
         <td>
            <select name="pcCodPer" autofocus>
            {foreach from = $saCodPer item = i}
               <option value="{$i[0]}" > {$i[1]}</option>
            {/foreach}
            </select>
         </td>
         </tr>
      </table>
      </fieldset>
      <table>
         <tr>
         <td><input type="submit" name="Boton1" value="Aplicar"/></td>
         <td><input type="submit" name="Boton1" value="Salir"/></td>
         </tr>
      </table>
   {elseif $scBehavior eq '1'}
      <fieldset style="width:600px" align="center">
      <table style="font-size:11px">
         <tr>
         <td>Empleado:</td>
         <td><p class="datos">{$scCodPer} - {$scNombre} - {$scNroDni}</p></td>
         </tr><tr>
      </table>
      </fieldset>
      <fieldset style="width:500px" align="center">
      <legend align="left"><font face="calibri" size=3 color=blue>Sanciones del Empleado</font></legend>
      <div style="overflow:scroll; height:400px; width:600px; overflow:auto; border: 1px;">     
      <table border=1 style="font-size:11px">
         <tr>
         <th width='20' align="center">Id</th>
         <th width='300' align="center">Sanción</th>
         <th width='100' align="center">Fecha</th>
         <th width='5px' align="center"><img src="CSS/Edit.png" style="width:100%; height:10%;"/></th>
         </tr>
         {foreach from = $saData item = i}               
            <tr>
            <td align="center">{$i[0]}<input type="hidden" name="paData[{$i[0]}][0]" value="{$i[0]}"/>
			                          <input type="hidden" name="paData[{$i[0]}][1]" value="{$i[1]}"/></td>     
            {if !empty ($i[2])}
               <td>{$i[2]}<input type="hidden" name="paData[{$i[0]}][2]" size="50" value="{$i[2]}"/></td>
            {/if}
            {if !empty ($i[3])}
               <td align="center">{$i[3]}<input type="hidden" name="paData[{$i[0]}][3]" value="{$i[3]}"/></td>
            {/if}
            <td align="center"><input type="radio" name="paCheck[]" value="{$i[0]}"/></td>
            </tr>
         {/foreach}
      </table>  
      </div>
      </fieldset>
      <table>
         <tr><td></td>
         <td><input type="submit" name="Boton2" value="Editar"/></td>
         <td><input type="submit" name="Boton2" value="Nuevo"/></td>
         <td><input type="submit" name="Boton2" value="Cancelar"/></td>
         </tr>
      </table> 
   {elseif $scBehavior eq '2'}
      <fieldset style="width:620px" style="height:100px" align="center">
      <table style="font-size:11px">
         <td>Código del Empleado:</td>
         <td><p class="datos">{$scCodPer} - {$scNombre} - {$scNroDni}</p><input type="hidden" name="pcCodPer" value="{$pcCodPer}"/></td>
      </table>
      <table style="font-size:11px">
         <tr>
         <td>Sanción:</td>
         <td>
            <select name="pcCodSan" autofocus>
            {foreach from = $saCodSan item = i}
               <option value="{$i[0]}" {if $scCodSan eq $i[0]} selected {/if} > {$i[1]}</option>
            {/foreach}
            </select>
         </td>
         <td>Fecha:</td>
         <td><input maxlength="10" type="text"  name="pdFecha" value='{$sdFecha}' placeholder='aaaa-mm-dd' onKeyPress="return fDate(event)"/></td>
         <td>Estado:</td>
         <td>
            <select name="pcEstado">
            {foreach from = $saEstado item = i}
               <option value="{$i[0]}" {if $i[0] eq $scEstado} selected {/if}>{$i[1]}</option>
            {/foreach}
            </select>
         </td>
         </tr>
      </table>
      </fieldset>
      <fieldset style="width:620px" align="center">
      <legend><font face="calibri" size=3 color=blue>Normas Incumplidas</FONT></legend>
      <div style="overflow:scroll; height:250px; width:600px; overflow:auto; border: 2px; color: #0900C4;">     
      <table border=1 style="font-size:11px">
         <tr>
         <th width='50px' align="center">Id</th>
         <th width='300px' align="center">Norma</th>  
         <th width='50px' align="center"><img src="CSS/anular.bmp"/></th>
         </tr>
         {foreach from = $saData item = i}
            <tr>
            <td align='center'>{$i[0]}<input type="hidden" name="paData[{$i[0]}][0]" value="{$i[0]}"/></td>
            <td>{$i[2]}<input type="hidden" name="paData[{$i[0]}][1]" value="{$i[1]}"/><input type="hidden" name="paData[{$i[0]}][2]" value="{$i[2]}"/></td>
            <td align='center'><input type="checkbox" name="paCheck[]" value="{$i[0]}"/></td>
            </tr>
         {/foreach}
      </table>  
      </div>
      <table>
         <td align='center'>
            <select  width="200" style="width: 300px" name="pcCodNor">
            {foreach from = $saCodNor item = j}
               <option value="{$j[0]}"> {$j[1]}</option>
            {/foreach}
            </select>
         </td>
      </table>  
      <table>
         <tr>
         <td><input type="submit" name="Boton3" value="Nuevo" class="boton"/></td>
         <td><input type="submit" name="Boton3" value="Eliminar" class="boton"/></td>
         </tr>
      </table> 
      <fieldset style="width:620px" align="center">
      <legend><font face="calibri" size=3 color=blue>Observaciones</font></legend>
      <table border=0 cellspacing="0" cellpadding="0" style="font-size:11px">
         <tr><td><textarea class="memo" name="pmObserv">{$smObserv}</textarea></td></tr>
      </table>
      </fieldset>
      </fieldset>
      <table>
         <tr>
         <td><input type="submit" name="Boton4" value="Grabar"/></td>
         <td><input type="submit" name="Boton4" value="Cancelar"/></td>
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