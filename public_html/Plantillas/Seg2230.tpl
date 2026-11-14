<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<html>
<head>
   <link href="CSS/Sixavi.css" media="screen" rel="stylesheet" type="text/css" />
   <script src="CSS/Libreria.js" language="JavaScript"></script>
   <title>Seguridad Operativa</title>
   {if $scBehavior eq '1'}
   <script language=JavaScript>
   
      window.onload = function() {
         var pos = window.name || 0;
         window.scrollTo(0, pos);
      }
     
      window.onunload = function() {
         window.name = self.pageYOffset || (document.documentElement.scrollTop + document.body.scrollTop);
      } 
   </script>
   {/if}
</head>
<body>
<form method="post" action="Seg2230.php#no-back">
   <div class="wrapper">
   <div id="MH">
      <ul>
      <li style="margin-left: 50px;"><h1>Personal y Unidades Involucrados en Incidente</h1></li>
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
      <legend align="left"><font face="calibri" size=3 color=blue>Personal Involucrado</font></legend>
	  <div id="content-personal" style="overflow-y:scroll; height:300px; width:1300px; ">
      <table border=1 style="font-size:10px">
         <tr>
         <th width='50px' align="center">Id</th>
         <th width='800px' align="center">Personal</th>
         <th width='300px' align="center">Empresa</th>
         <th width='300px' align="center">Puesto</th>
         <th width='300px' align="center">Nro.DNI</th>
         <th width='300px' align="center">Nombre</th>
         <th width='100px' align="center">Entrenado</th>
		 <th width='300px' align="center">Actividad</th>
         <th width='300px' align="center">Ubicación</th>
         <th width='300px' align="center">Supervisor Operativo</th>
         <th width='50px' align="center"><img src="CSS/anular.bmp" /></th>
         </tr>
         {foreach from = $saCodPin item = i}
            <tr>
            {if substr($i[2], 0, 1) != '*'}
               <td align='center'>{$i[0]}<input type="hidden" name="paData[{$i[0]}][0]" value="{$i[0]}"/><input type="hidden" name="paData[{$i[0]}][1]" value="{$i[1]}"/></td>
               <td>{$i[2]}<input type="hidden" name="paData[{$i[0]}][2]" value="{$i[2]}"/><input type="hidden" name="paData[{$i[0]}][3]" value="{$i[3]}"/></td>
               <td align='center'>{$i[4]}<input type="hidden" name="paData[{$i[0]}][4]" value="{$i[4]}"/><input type="hidden" name="paData[{$i[0]}][5]" value="{$i[5]}"/></td>
               <td align='center'>{$i[6]}<input type="hidden" name="paData[{$i[0]}][6]" value="{$i[6]}"/></td>
               <td align='center'>{$i[7]}<input type="hidden" name="paData[{$i[0]}][7]" value="{$i[7]}"/></td>
               <td align='center'>{$i[8]}<input type="hidden" name="paData[{$i[0]}][8]" value="{$i[8]}"/></td>
               <td align='center'><input type="checkbox" name="paData[{$i[0]}][12]" value='S' {if ($i[12] eq 'S')} checked {/if}/></td>
			   <td align='center'><input type="Text" name="paData[{$i[0]}][9]" value="{$i[9]}" maxlength='200' /></td>
               <td align='center'><input type="Text" name="paData[{$i[0]}][10]" value="{$i[10]}" maxlength='200' /></td>
               <td>
                  <select  width="200" style="width: 190px; font-size:11px" name="paData[{$i[0]}][11]">
                  {foreach from = $saPerCod item = j}
                     <option value="{$j[0]}" {if $i[11] eq $j[0]} selected {/if}>{$j[1]} </option>
                  {/foreach}
                  </select>
               </td>
            {else}
               <td align='center'>{$i[0]}<input type="hidden" name="paData[{$i[0]}][0]" value="{$i[0]}"/><input type="hidden" name="paData[{$i[0]}][1]" value="{$i[1]}"/></td>
               <td>{$i[2]}<input type="hidden" name="paData[{$i[0]}][2]" value="{$i[2]}"/><input type="hidden" name="paData[{$i[0]}][3]" value="{$i[3]}"/></td>
               <td align='center'>{$i[4]}<input type="hidden" name="paData[{$i[0]}][4]" value="{$i[4]}"/><input type="hidden" name="paData[{$i[0]}][5]" value="{$i[5]}"/></td>
               <td align='center'>{$i[6]}<input type="hidden" name="paData[{$i[0]}][6]" value="{$i[6]}"/></td>
               <td align='center'><input type="Text" maxlength="8" name="paData[{$i[0]}][7]" value="{$i[7]}"/></td>
               <td align='center'><input type="Text" name="paData[{$i[0]}][8]" value="{$i[8]}" /></td>
               <td align='center'><input type="checkbox" name="paData[{$i[0]}][12]" value='S' {if ($i[12] eq 'S')} checked {/if}/></td>
			   <td align='center'><input type="Text" name="paData[{$i[0]}][9]" value="{$i[9]}" /></td>
               <td align='center'><input type="Text" name="paData[{$i[0]}][10]" value="{$i[10]}" /></td>
               <td align='center'><input type="hidden" name="paData[{$i[0]}][11]" value="999999"/></td>
            {/if}
            
            <td align='center'><input type="checkbox" name="paCheck[]" value="{$i[0]}"/></td>
         {/foreach}
      </table>
	  </div>
	  <table>
            <td>
               <select  width="200" style="width: 190px; font-size:11px" name="pcCodPer" align="rigth">
               {foreach from = $saCodPer item = j}
                  <option value="{$j[0]}" {if $scCodPer eq $j[0]} selected {/if}>{$j[1]} </option>
               {/foreach}
               </select>
            </td>
         </table>
	  <table>
            <tr>
            <td><input type="submit" name="Boton2" value="Nuevo" class="boton"/></td>
            <td><input type="submit" name="Boton2" value="Eliminar" class="boton"/></td>
            </tr>
         </table>
      </fieldset>
      <fieldset style="width:950px" align="center">
      <legend align="left"><font face="calibri" size=3 color=blue>Unidades Involucradas</font></legend>
	  <table border=2 style="font-size:11px">
	  <tr>
         <th width=30 align="center">Id</th>
         <th width=300 align="center">Unidad</th>
         <th width=300 align="center">Daño</th>
         <th width=300 align="center">Función</th>
         <th width=25 align="center"><img src="CSS/anular.bmp" /></th>
         </tr>
	  </table>
	  <div id="content-unidades" style="overflow-y:scroll; height:300px; width:1300px; ">
      <table class="tabla-unidades" border=2 style="font-size:11px">
         
         {foreach from = $saCodUin item = i}
            <tr>
            <td width=30 align='center'>{$i[0]}<input type="hidden" name="paData1[{$i[0]}][0]" value="{$i[0]}"/>
                                      <input type="hidden" name="paData1[{$i[0]}][1]" value="{$i[1]}"/></td>
            {if substr($i[1],0,5) eq '99999'}
               <td width=300><input type="text" maxlength="200" size=45 name="paData1[{$i[0]}][2]" id="paData1[{$i[0]}][2]" value="{$i[2]}" /></td>
            {else}
               <td width=300>{$i[2]}<input type="hidden" name="paData1[{$i[0]}][2]" id="paData1[{$i[0]}][2]" value="{$i[2]}"/></td>
            {/if}
            <td width=300><input type="text" maxlength="100" size=55 name="paData1[{$i[0]}][3]" value="{$i[3]}"/></td>
            <td width=300><input type="text" maxlength="100" size=55 name="paData1[{$i[0]}][4]" value="{$i[4]}"/></td>
            <td width=25 align='center'><input type="checkbox" name="paCheck1[]" value="{$i[0]}"/></td>
         {/foreach}
      </table>
	  </div>
      <table>
         <td>
            <select  width="200" style="width: 190px; font-size:11px" name="pcCodUni" id="pcCodUni">
            {foreach from = $saCodUni item = j}
               <option value="{$j[0]}">{$j[1]} </option>
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
{if $scBehavior eq '1'}
<script language=JavaScript>
document.getElementById("content-personal").scrollTop = 4000;
document.getElementById("content-unidades").scrollTop = 4000;
</script>
{/if}
</body>
</html>