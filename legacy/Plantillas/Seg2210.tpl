<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<html>
<head>
   <link href="CSS/Sixavi.css" media="screen" rel="stylesheet" type="text/css" />
   <script src="CSS/funciones.js" language="JavaScript"></script>
   <script src="CSS/Libreria.js" language="JavaScript"></script>
   <title>Seguridad Operativa</title>
   <script>
      function f_FuncionInit() {
         myFunction0();
      }

      function myFunction0() {
         var lcEstaci = document.getElementById('pcEstaci').value;
		 if (lcEstaci == "99") {
            document.getElementById('pcKilome').disabled = false;
            var s = document.getElementById('pcKilome');
            s.options.length = 0;
         } else {
            document.getElementById('pcKilome').disabled = true;
            document.getElementById('pcKilome').value = lcEstaci;
         }
      }
   </script>
</head>
<body onload="f_FuncionInit()">
<form action='Seg2210.php' method='post'>
   <div class="wrapper">
   <div id="MH">
      <ul>
      <li style="margin-left: 50px;"><h1>Registro de Reporte de Incidente</h1></li>
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
         <td><input type="text" name="pcNroInc" value="" maxlength="6" size='10' align='center' autofocus/></td>
         </tr>
      </table>
      </fieldset>
      <table>
         <tr>
         <td><input type="submit" name="Boton1" value="Aplicar"/></td>
         <td><input type="submit" name="Boton1" value="Nuevo"/></td>
         <td><input type="submit" name="Boton1" value="Buscar"/></td>
         <td><input type="submit" name="Boton1" value="Salir"/></td>
         </tr>
      </table>
   {elseif $scBehavior eq '1'}
      <fieldset style="width:900px" align="center">
      <legend align="left"><font face="calibri" size=3 color=blue>Registro de Incidentes</font></legend>
	  <table border=0 style="font-size:11px">
         <tr>
         <td>Número de Incidente</td>
         <td><font color=blue>{$scNroInc}</font><input type="hidden" name="pcNroInc" value="{$scNroInc}"/></td>
         <td></td>
         <td>Empresa</td>
         <td>
            <select name="pcCodEmp">
            {foreach from = $saCodEmp item = i}
               <option value="{$i[0]}" {if $scCodEmp eq $i[0]} selected {/if} > {$i[1]}</option>
            {/foreach}
            </select>
         </td>
         <tr></tr>
         <td>Proyecto</td>
         <td>
            <select name="pcCodPry" id="pcCodPry">
            {foreach from = $saCodPry item = i}
               <option value="{$i[0]}" {if $scCodPry eq $i[0]} selected {/if} > {$i[1]}</option>
            {/foreach}
            </select>
			<span id="espera"></span>
         </td>
         <td></td>
         <td>Fecha</td>
         <td><input type="text" name="pcFecInc" value="{$scFecInc}" maxlength='10' placeholder="aaaa-mm-dd" onKeyPress="return fDate(event)"/></td>
         <tr></tr>
         <td>Hora</td>
         <td><input type="text" name="pcHorInc" value="{$scHorInc}" maxlength='5' placeholder="hh:mm" onKeyPress="return fHour(event)"/></td>
         <td></td>
         <td>Patio/Principal</td>
         <td>
            <select name="pcLugar">
            {foreach from = $saLugar item = i}
               <option value="{$i[0]}" {if $scLugar eq $i[0]} selected {/if} > {$i[1]}</option>
            {/foreach}
            </select>
         </td>
         </tr><tr>
         <td>Clasificación</td>
         <td>
            <select name="pcClaInc">
            {foreach from = $saClaInc item = i}
               <option value="{$i[0]}" {if $scClaInc eq $i[0]} selected {/if} > {$i[1]}</option>
            {/foreach}
            </select>
         </td>
         <td></td>
         <td>Tipo</td>
         <td>
            <select name="pcTipInc">
            {foreach from = $saTipInc item = i}
               <option value="{$i[0]}" {if $scTipInc eq $i[0]} selected {/if} > {$i[1]}</option>
            {/foreach}
            </select>
         </td>
         <td></td>
		 </tr><tr>
         <td>Reportado por:</td>
         <td>
            <select name="pcCodPe1">
            {foreach from = $saPerCod item = i}
               <option value="{$i[0]}" {if $scCodPe1 eq $i[0]} selected {/if} > {$i[1]}</option>
            {/foreach}
            </select>
         </td>
         <td></td>
         <td>Área</td>
         <td>
            <select name="pcArea1">
            {foreach from = $saAreas item = i}
               <option value="{$i[0]}" {if $scArea1 eq $i[0]} selected {/if} > {$i[1]}</option>
            {/foreach}
            </select>
         </td>
		 </tr><tr>
         <td>Comunicado a:</td>
         <td>
            <select name="pcCodPe2">
            {foreach from = $saCodPer item = i}
               <option value="{$i[0]}" {if $scCodPe2 eq $i[0]} selected {/if} > {$i[1]}</option>
            {/foreach}
            </select>
         </td>
         <td></td>
         <td>Área</td>
         <td>
            <select name="pcArea2">
            {foreach from = $saAreas item = i}
               <option value="{$i[0]}" {if $scArea2 eq $i[0]} selected {/if} > {$i[1]}</option>
            {/foreach}
            </select>
         </td>
		 </tr><tr>
         <td>Subdivisión</td>
         <td>
            <select name="pcSubDiv">
            {foreach from = $saSubDiv item = i}
               <option value="{$i[0]}" {if $scSubDiv eq $i[0]} selected {/if} > {$i[1]}</option>
            {/foreach}
            </select>
         </td>
         <td></td>
         <td>PK</td>
         <td><input type="text" name="pcKilome" value="{$scKilome}" id="pcKilome" size='10' maxlength='8' onKeyPress="return fFloat(event)"/></td>
		 </tr><tr>
         <td>Estación</td>
         <td>
            <select name="pcEstaci" id="pcEstaci" onchange="myFunction0()">
            {foreach from = $saEstaci item = i}
               <option value="{$i[0]}" {if $scEstaci eq $i[0]} selected {/if} > {$i[1]}</option>
            {/foreach}
            </select>
         </td>
		 <td></td>
         <td>Procesos</td>
         <td>
            <select name="pcCodPrc" id="pcCodPrc" style="width: 300px">
            {foreach from = $saCodPrc item = i}
               <option value="{$i[0]}" {if $scCodPrc eq $i[0]} selected {/if} > {$i[1]}</option>
            {/foreach}
            </select>
         </td>
		 </tr><tr>
         <td>Nro.Rep. Físico</td>
         <td><input type="text" name="pcRepFis" value="{$scRepFis}" size='8' maxlength='8' /></td>
		 </tr>
      </table>  
      </fieldset>
      <fieldset style="width:900px" align="center">
      <legend align="left"><font face="calibri" size=3 color=blue>Descripción del Incidente</font></legend>
      <textarea class="memo" name="pmDescri">{$smDescri}</textarea>   
      </fieldset>
      <fieldset style="width:900px" align="center">
      <legend align="left"><font face="calibri" size=3 color=blue>Sugerencia</font></legend>
      <textarea class="memo" name="pmSugere">{$smSugere}</textarea>
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