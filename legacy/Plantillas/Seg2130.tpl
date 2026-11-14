<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<html>
<head>
   <link href="CSS/Sixavi.css" media="screen" rel="stylesheet" type="text/css" />
   <title>Seguridad Operativa</title>
   <script type="text/javascript" src="CSS/jquery-1.10.2.min.js"></script>
   <script type="text/javascript" src="CSS/Libreria.js"></script>
   <script>
      function f_FuncionInit() {
         myFunction();
      }

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
   </script>   
</head>
<body onload="f_FuncionInit()">
<form method="post" action="Seg2130.php#no-back">
   <div class="wrapper">
   <div id="MH">
      <ul>
      <li style="margin-left: 50px;"><h1>Registro de Control de Velocidad por Radar</h1></li>
      <li style="float:right;margin-right: 100px;"><a onclick="self.location = 'index.php'"> Cerrar Sesión</a></li>
      </ul>
   </div>
   <div id="topnavbar"></div>
   <div id="navigation">
   <div id="navcontainer">
   {if $scBehavior eq '0'}
      <fieldset style="width:500px" align="center">
      <table border=0 style="font-size:11px">
         <tr>
         <td>Número de Evaluación</td>
         <td><font color=blue>{$scEvaTip}-</font><input type="text" name="pcNroEva" value="" size='5' maxlength='5' class="fmNumero"/></td>
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
      <fieldset style="width:1000px" align="center">
      <legend align="left"><font face="calibri" size=3 color=blue>Datos de la Evaluación</FONT></legend>
      <table border=0 style="font-size:11px">
         <tr>
         <td>Número de Evaluación</td>
         <td><font color=blue>{$scNroEva}</font><input type="hidden" name="pcNroEva" value="{$scNroEva}" /></td>
         <td></td>
         <td>Empresa</td>
         <td>
            <select name="pcCodEmp">
            {foreach from = $saCodEmp item = i}
               <option value="{$i[0]}" {if $scCodEmp eq $i[0]} selected {/if} > {$i[1]}</option>
            {/foreach}
            </select>
         </td>
         </tr><tr>
         <td>Proyecto</td>
         <td>
            <select name="pcCodPry">
            {foreach from = $saCodPry item = i}
               <option value="{$i[0]}" {if $scCodPry eq $i[0]} selected {/if} > {$i[1]}</option>
            {/foreach}
            </select>
         </td>
         <td></td>
		 <td>Subdivisión</td>
         <td>
            <select name="pcSubDiv">
            {foreach from = $saSubDiv item = i}
               <option value="{$i[0]}" {if $scSubDiv eq $i[0]} selected {/if} > {$i[1]}</option>
            {/foreach}
            </select>
         </td>
         </tr><tr>
		 <td>Fecha de Evaluación</td>
         <td><input type="text" name="pcFecEva" value="{$scFecEva}" size='12' placeholder="aaaa-mm-dd" onblur="Abc()"/></td>
         <td></td>
         <td>Hora de Evaluación</td>
         <td><input type="text" name="pcHorEva" value="{$scHorEva}" size='5' placeholder="hh:mm"/></td>
         </tr><tr>
		 <td>Tren/Ruta</td>
         <td><input type="text" name="pcTren" value="{$scTren}" size="40" width='25'/></td>
		 <td></td>
		 <td>Tipo de Tren</td>
         <td>
            <select name="pcTipVag">
            {foreach from = $saTipVag item = i}
               <option value="{$i[0]}" {if $scTipVag eq $i[0]} selected {/if} > {$i[1]}</option>
            {/foreach}
            </select>
         </td>
		 </tr><tr>
		 <td>Unidad 1</td>
         <td>
            <select name="pcCodUn1">
            {foreach from = $saCodUni item = i}
               <option value="{$i[0]}" {if $scCodUn1 eq $i[0]} selected {/if} > {$i[1]}</option>
            {/foreach}
            </select>
         </td>
		 <td></td>
		 <td>Unidad 2</td>
         <td>
            <select name="pcCodUn2">
            {foreach from = $saCodUni item = i}
               <option value="{$i[0]}" {if $scCodUn2 eq $i[0]} selected {/if} > {$i[1]}</option>
            {/foreach}
            </select>
         </td>
		 </tr><tr>
		 <td>Unidad 3</td>
         <td>
            <select name="pcCodUn3">
            {foreach from = $saCodUni item = i}
               <option value="{$i[0]}" {if $scCodUn3 eq $i[0]} selected {/if} > {$i[1]}</option>
            {/foreach}
            </select>
         </td>
         <td></td>
		 <td>Composición</td>
         <td><input type="text" name="pcCompos" value="{$scCompos}" size='10' placeholder="99.99.9999"/></font></td>
         </tr><tr>
		 <td>Kilometraje (PK)</td>
         <td><input type="text" id= "pcKilome" name="pcKilome" value="{$scKilome}" size='5' placeholder='9999.999'/></td>
         <td></td>
		 <td>Estacion</td>
         <td>
            <select name="pcEstaci" id ="pcEstaci" onchange="myFunction()">
            {foreach from = $saEstaci item = i}
               <option value="{$i[0]}" {if $scEstaci === $i[0]} selected {/if} > {$i[1]}</option>
            {/foreach}
            </select>
         </td>
		 </tr><tr>
		 <td>Condición de Vía</td>
         <td>
            <select name="pcCondic">
            {foreach from = $saCondic item = i}
               <option value="{$i[0]}" {if $scCondic eq $i[0]} selected {/if} > {$i[1]}</option>
            {/foreach}
            </select>
         </td>
		 <td></td>
		 <td>Condición Climática</td>
         <td>
            <select name="pcConCli">
            {foreach from = $saConCli item = i}
               <option value="{$i[0]}" {if $scCondic eq $i[0]} selected {/if} > {$i[1]}</option>
            {/foreach}
            </select>
         </td>
         <tr></tr>
		 <td>Vel.Máx.Autorizada</td>
         <td><input type="text" name="pcVelAut" value="{$scVelAut}" size="4"/></td>
		 <td></td>
		 <td>Restricciones</td>
         <td><input type="checkbox" name="pcRestri" value="S" {if $scRestri eq 'S'} checked {/if}/></td>
		 <tr></tr>
		 <td>Vel.Máx.Registrada</td>
         <td><input type="text" name="pcVelReg" value="{$scVelReg}" size="4"/></td>
         <td></td>
		 <td>Resultado</td>
         <td>
            <select name="pcResult">
            {foreach from = $saResult item = i}
               <option value="{$i[0]}" {if $scResult eq $i[0]} selected {/if} > {$i[1]}</option>
            {/foreach}
            </select>
         </td>
		 </tr><tr>
		 <td>Supervisor 1</td>
         <td>
            <select name="pcSuperv" style="width:200px">
            {foreach from = $saSuperv item = i}
               <option value="{$i[0]}" {if $scSuperv eq $i[0]} selected {/if} > {$i[1]}</option>
            {/foreach}
            </select>
         </td>
         <td></td>
         <td>Supervisor 2</td>
         <td>
            <select name="pcSuper1" style="width:200px">
            {foreach from = $saSuperv item = i}
               <option value="{$i[0]}" {if $scSuper1 eq $i[0]} selected {/if} > {$i[1]}</option>
            {/foreach}
            </select>
         </td>
         </tr>
      </table>  
      </fieldset>
      <fieldset style="width:1000px" align="center">
      <legend align="left"><font face="calibri" size=3 color=blue>Observaciones</font></legend>
      <textarea class="memo" name="pmObserv">{$smObserv}</textarea>
      </fieldset>
      <fieldset style="width:975px" align="center">
      <legend align="left"><font face="calibri" size=3 color=blue>Personal Evaluado</FONT></legend>
      <table border=1 style="font-size:11px">
         <tr>
         <th width='50px' align="left">Id</th>
         <th width='300px'>Personal</th>
         <th width='50px'>Puesto</th>
         </tr>
         {foreach from = $saDataP item = i}
         <tr>
         <td align='center'>{$i[0]}<input type="hidden" name="paDataP[{$i[0]}][0]" value="{$i[0]}"/></td>
         <td align='center'>
            <select  width="200" style="width: 300px" name="paDataP[{$i[0]}][1]" style="font-size:11px">
               {foreach from = $saCodPer item = j}
               <option value="{$j[0]}" {if $i[1] eq $j[0]} selected {/if}> {$j[1]}</option>
               {/foreach}
            </select>
         </td>
         <td align='center'>
            <select  width="200" style="width: 300px" name="paDataP[{$i[0]}][2]">
               {foreach from = $saPuesto item = j}
               <option value="{$j[0]}" {if $i[2] eq $j[0]} selected {/if}> {$j[1]}</option>
               {/foreach}
               </select>
         </td>
         </tr>
         {/foreach}
      </table>  
      </fieldset>
      <table>
         <tr>
         <td><input type="submit" name="Boton2" value="Grabar"/></td>
         <td><input type="submit" name="Boton2" value="Cancelar"/></td>
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

