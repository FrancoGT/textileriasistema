<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<html>
<head>
   <link href="CSS/Sixavi.css" media="screen" rel="stylesheet" type="text/css" />
   <script language=JavaScript>   
      function f_habilitar(e) {
         if (e) {
            document.getElementById("pmDetHer").disabled = false;
            document.getElementById("pmDetHer").style.backgroundColor='#FFFFA0';
         } else {
            document.getElementById("pmDetHer").value = '';
            document.getElementById("pmDetHer").disabled = true;
            document.getElementById("pmDetHer").style.backgroundColor='#ffffff';
         }
      }

   {if $scBehavior != '0'}
      window.onload = function() {
         var pos = window.name || 0;
         window.scrollTo(0, pos);
      }
     
      window.onunload = function() {
         window.name = self.pageYOffset || (document.documentElement.scrollTop + document.body.scrollTop);
      } 
   {/if}
      function myFunction() {
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

      function f_FuncionInit() {
         myFunction();
      }

   </script>
   <title>Seguridad Operativa</title>
</head>
<body onload="f_FuncionInit()">
<form method = "post" action = "Seg1510.php#no-back">
   <div class="wrapper">
   <div id="MH">
      <ul>
      <li style="margin-left: 50px;"><h1>Registro de Incidentes Ferroviarios</h1></li>
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
         <td>Número de Incidente Ferroviario</td>
         <td><input type = "text" name = "pcNumero" value="" size='6' autofocus /></td>
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
      <table border = 0 cellspacing="0" cellpadding="0" style="font-size:11px">
         <tr>
         <td>Número:</td>
         <td>{$scNumero}<input type = "hidden" name = "pcNumero" value="{$scNumero}"/></td>
         <td></td>
         <td>Fecha:</td>
         <td><input type = "text" name = "pdFecha" value="{$sdFecha}" size="10" autofocus placeholder="aaaa-mm-dd"/></td>
         <td></td>
         <td>Hora:</td>
         <td><input type = "text" name = "pcHora" value="{$scHora}" size="5" maxlength="5" placeholder="hh:mm"/></td>
         <tr></tr>
         <td>Tipo:</td>
         <td>
            <select name = "pcIncFer">
            {foreach from = $saIncFer item = i}
               <option value = "{$i[0]}" {if $scIncFer eq $i[0]} selected {/if} > {$i[1]}</option>
            {/foreach}
            </select>
         </td>
         <td></td>
         <td>Tren:</td>
         <td><input type = "text" name = "pcTren" value="{$scTren}" size="50" maxlength='245'/></td>
         <td></td>
         <td>Empresa:</td>
         <td>
            <select name = "pcCodEmp">
            {foreach from = $saCodEmp item = i}
               <option value = "{$i[0]}" {if $scCodEmp eq $i[0]} selected {/if} > {$i[1]}</option>
            {/foreach}
            </select>
         </td>
         <tr></tr>
         <td>Proyecto:</td>
         <td>
            <select name = "pcCodPry">
            {foreach from = $saCodPry item = i}
               <option value = "{$i[0]}" {if $scCodPry eq $i[0]} selected {/if} > {$i[1]}</option>
            {/foreach}
            </select>
         </td>
         <td></td>
         <td>Sub-división:</td>
         <td>
            <select name = "pcSubDiv">
            {foreach from = $saSubDiv item = i}
               <option value = "{$i[0]}" {if $scSubDiv eq $i[0]} selected {/if} > {$i[1]}</option>
            {/foreach}
            </select>
         </td>
         <tr></tr>
		 <td>Tipo de Vía:</td>
         <td>
            <select name = "pcLugar">
            {foreach from = $saLugAcc item = i}
               <option value = "{$i[0]}" {if $scLugar eq $i[0]} selected {/if} > {$i[1]}</option>
            {/foreach}
            </select>
         </td>
         <td></td>
		 <td>Estación:</td>
         <td>
            <select name = "pcEstaci" id="pcEstaci" onchange="myFunction()">
            {foreach from = $saEstaci item = i}
               <option value = "{$i[0]}" {if $scEstaci === $i[0]} selected {/if} > {$i[1]}</option>
            {/foreach}
            </select>
         </td>
		 <td></td>
		 <td>Ubicación (PK):</td>
         <td><input type = "text" name = "pcKilome" id="pcKilome" value="{$scKilome}" size="15"/></td>
         <tr></tr>
		 <td>Maquinista (Chofer):</td>
         <td>
            <select name = "pcCodMaq" style="width: 200px; font-size:11px">
            {foreach from = $saCodPer item = i}
               <option value = "{$i[0]}" {if $scCodMaq eq $i[0]} selected {/if} > {$i[1]}</option>
            {/foreach}
            </select>
         </td>
         <td></td>
         <td>Jefe de Tren:</td>
         <td>
            <select name = "pcCodJef" style="width: 200px; font-size:11px">
            {foreach from = $saCodPer item = i}
               <option value = "{$i[0]}" {if $scCodJef eq $i[0]} selected {/if} > {$i[1]}</option>
            {/foreach}
            </select>
         </td>
         <td></td>
         <td>Brequero:</td>
         <td>
            <select name = "pcCodBrq" style="width: 200px; font-size:11px">
            {foreach from = $saCodPer item = i}
               <option value = "{$i[0]}" {if $scCodBrq eq $i[0]} selected {/if} > {$i[1]}</option>
            {/foreach}
            </select>
         </td>
		 <tr></tr>
		 <td>Causas:</td>
         <td>
            <select name = "pcCodFcr" style="width: 300px; font-size:11px">
            {foreach from = $saCodFcr item = i}
               <option value = "{$i[0]}" {if $scCodFcr eq $i[0]} selected {/if} > {$i[1]}</option>
            {/foreach}
            </select>
         </td>
         <td></td>
		 <td>Area:</td>
         <td>
            <select name = "pcArea">
            {foreach from = $saAreas item = i}
               <option value = "{$i[0]}" {if $scArea eq $i[0]} selected {/if} > {$i[1]}</option>
            {/foreach}
            </select>
         </td>
         <tr></tr>
		 <td>Heridos:</td>
         <td><input type = "checkbox" name = "pcHerido"  id = "pcHerido" value="S" {if $scHerido eq 'S'} checked {/if}  onclick = 'f_habilitar(this.checked);'/></td>
         <td></td>
         <td>Costo:</td>
         <td><input type = "text" name = "pnCosto" value="{$snCosto}" size='12'/></td>
         <td></td>
		 <td>Demora:</td>
         <td><input type = "text" name = "pcDemora" value="{$scDemora}" size='6' maxlength="6" placeholder="00d00h"/></td>
         </tr>
      </table>
      <table style="font-size:11px">
         <tr>
         <td>Descripción del Incidente</td>
         <td>Heridos</td>
         </tr><tr>
         <td><textarea name="pmDescri" rows="10" cols="50">{$smDescri}</textarea></td>
         <td><textarea name="pmDetHer" id="pmDetHer" rows="10" cols="50" {if $scHerido eq 'N'} disabled style="background-color: white"{/if}>{$smDetHer}</textarea></td>
         </tr><tr>
         <td>Medidas Adoptadas</td>
         <td>Descripción de Causas</td>
         </tr><tr>
         <td><textarea name="pmMedAdo" rows="10" cols="50">{$smMedAdo}</textarea></td>
         <td><textarea name="pmCausas" rows="10" cols="50">{$smCausas}</textarea></td>
         </tr>
      </table>
      <table border = 1 style="font-size:11px">
         <tr>
         <th width='50px' align="center">Id</th>
         <th width='300px' align="center">Unidad</th>
         <th width='50px'>Eliminar</th>
         </tr>
         {foreach from = $saData item = i}
            <tr>
            <td align='center'>{$i[0]}<input type = "hidden" name = "paData[{$i[0]}][0]" value="{$i[0]}"/><input type = "hidden" name = "paData[{$i[0]}][1]" value="{$i[1]}"/></td>
            {if $i[1] neq '99999'} 
               <td>{$i[2]}<input type = "hidden" name = "paData[{$i[0]}][2]" value="{$i[2]}" size="50"/></td>
            {else} 
               <td><input type = "text" maxlength = "100" name = "paData[{$i[0]}][2]" value="{$i[2]}" size="50" {if $i[1] neq '99999'} disabled {/if}/></td>
            {/if}
            <td align='center'><input type = "checkbox" name = "paCheck[]" value = "{$i[0]}"/></td>
         {/foreach}
      </table>
      <table>
         <td align='center'>
            <select style="width: 190px; font-size:11px" name = "pcCodUni";>
            {foreach from = $saCodUni item = j}
               <option value = "{$j[0]}" > {$j[1]} </option>
            {/foreach}
            </select>
         </td>
      </table>
      <table>
         <td><input type="submit" name="Boton2" value="Nuevo" class="boton"/></td>
         <td><input type="submit" name="Boton2" value="Eliminar" class="boton"/></td>
      </table>
      </fieldset>
      <table>
         <td><input type="submit" name="Boton3" value="Grabar"/></td>
         <td><input type="submit" name="Boton3" value="Cancelar"/></td>
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
