<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<html>
<head>
   <link href="CSS/Sixavi.css" media="screen" rel="stylesheet" type="text/css" />
   <title>Seguridad Operativa</title>
   <script type="text/javascript" src="CSS/jquery-1.10.2.min.js"></script>
   <script type="text/javascript" src="CSS/Libreria.js"></script>
   <script>
      function f_FuncionInit() {
         myFunction0();
         myFunction1();
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
      function myFunction1() {
         var lcEstacf = document.getElementById('pcEstacf').value;
         if (lcEstacf == "99") {
            document.getElementById('pcKilomf').disabled = false;
            var s = document.getElementById('pcKilomf');
            s.options.length = 0;
         } else {
            document.getElementById('pcKilomf').disabled = true;
            document.getElementById('pcKilomf').value = lcEstacf;
	     }
      }
   </script>
</head>
<body onload="f_FuncionInit()">
<form method="post" action="Seg2140.php#no-back">
   <div class="wrapper">
   <div id="MH">
      <ul>
      <li style="margin-left: 50px;"><h1>Registro de Evaluación de Conducción de Vehículos Terrestres</h1></li>
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
      <fieldset style="width:900px" align="center">
      <legend align="left"><font face="calibri" size=3 color=blue>Datos de la Evaluación</font></legend>
      <table border=0 style="font-size:11px">
         <tr>
         <td>Número de Evaluación</td>
         <td><font color=blue>{$scNroEva}</font><input type="hidden" name="pcNroEva" value="{$scNroEva}"/></td>
         </tr><tr>
		 <td>Empresa</td>
         <td>
         <select name="pcCodEmp">
            {foreach from = $saCodEmp item = i}
               <option value="{$i[0]}" {if $scCodEmp eq $i[0]} selected {/if} > {$i[1]}</option>
            {/foreach}
         </select>
         </td>
		 <td>Proyecto</td>
         <td>
            <select name="pcCodPry">
            {foreach from = $saCodPry item = i}
               <option value="{$i[0]}" {if $scCodPry eq $i[0]} selected {/if} > {$i[1]}</option>
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
		 </tr><tr>
		 <td>Fecha de Evaluación</td>
         <td><input type="text" name="pcFecEva" value="{$scFecEva}" size='10' placeholder="aaaa-mm-dd"/></td>
         <td>Hora de Evaluación</td>
         <td><input type="text" name="pcHorEva" value="{$scHorEva}" size='5' placeholder="hh:mm"/></td>
         </tr><tr>
		 <td>Operador</td>
         <td>
            <select name="pcCodOpe">
            {foreach from = $saCodOpe item = i}
               <option value="{$i[0]}" {if $scCodOpe eq $i[0]} selected {/if} > {$i[1]}</option>
            {/foreach}
            </select>
         </td>
         <td>Horas Servicio</td>
         <td><input type="text" name="pcHorSer" value="{$scHorSer}" size='5' placeholder="hh:mm" /></td>
         </tr><tr>
		 <td>Estación Inicio</td>
         <td>
            <select name="pcEstaci" id ="pcEstaci"onchange="myFunction0()">
            {foreach from = $saEstaci item = i}
               <option value="{$i[0]}" {if $scEstaci === $i[0]} selected {/if} > {$i[1]}</option>
            {/foreach}
            </select>
         </td>
         <td>PK Inicial</td>
         <td><input type="text" name="pcKilome" id ="pcKilome" value="{$scKilome}" size='5'/></td>
         </tr><tr>
         <td>Estación Final</td>
         <td>
            <select name="pcEstacf" id ="pcEstacf"onchange="myFunction1()">
            {foreach from = $saEstaci item = i}
              <option value="{$i[0]}" {if $scEstacf === $i[0]} selected {/if} > {$i[1]}</option>
           {/foreach}
           </select>
         </td>
         <td>PK Final</td>
         <td><input type="text" name="pcKilomf" id ="pcKilomf" value="{$scKilomf}" size='5'/></td>
         </tr><tr>
         <td>Tracto</td>
         <td><input type="text" name="pcTren" value="{$scTren}" size='50' width='30'/></td>
         <td>Composición</td>
         <td><input type="text" name="pcCompos" value="{$scCompos}" size='10' placeholder="99.99.9999"/></td>
         </tr><tr>
         <td>Unidad 1</td>
         <td>
            <select name="pcCodUn1">
            {foreach from = $saCodUni item = i}
               <option value="{$i[0]}" {if $scCodUn1 eq $i[0]} selected {/if} > {$i[1]}</option>
            {/foreach}
            </select>
         </td>
         <td>Unidad 2</td>
         <td>
            <select name="pcCodUn2">
            {foreach from = $saCodUni item = i}
               <option value="{$i[0]}" {if $scCodUn2 eq $i[0]} selected {/if}> {$i[1]}</option>
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
         <td>Tipo de Tracto</td>
         <td>
            <select name="pcTipVag">
            {foreach from = $saTipVag item = i}
               <option value="{$i[0]}" {if $scTipVag eq $i[0]} selected {/if} > {$i[1]}</option>
            {/foreach}
            </select>
         </td>
		 </tr><tr>
		 <td>Supervisor</td>
         <td>
            <select name="pcSuperv">
            {foreach from = $saSuperv item = i}
               <option value="{$i[0]}" {if $scSuperv eq $i[0]} selected {/if} > {$i[1]}</option>
            {/foreach}
             </select>
         </td>
         <td>Resultado</td>
         <td>
            <select name="pcResult">
            {foreach from = $saResult item = i}
               <option value="{$i[0]}" {if $scResult eq $i[0]} selected {/if} > {$i[1]}</option>
            {/foreach}
            </select>
         </td>
		 </tr><tr>
         </tr>
      </table>
      </fieldset>
      <fieldset style="width:900px" align="center">
      <legend align="left"><font face="calibri" size=3 color=blue>Detalle de Evaluación</font></legend>
      <table border=1 style="font-size:11px">
         <tr>
         <th width='50px' align="center">Id</th>
         <th width='700px'>Item</th>
         <th width='50px' align="center">S/N</th>
         </tr>
         {foreach from = $saData item = i}
            <tr>
            <td align='center'>{$i[1]}<input type="hidden" name="paData[{$i[0]}][0]" value="{$i[0]}"/>
                                      <input type="hidden" name="paData[{$i[0]}][1]" value="{$i[1]}"/></td>
            <td>{$i[2]}<input type="hidden" name="paData[{$i[0]}][2]" value="{$i[2]}"/>
                       <input type="hidden" name="paData[{$i[0]}][3]" value="{$i[3]}"/></td>
            <td align='center'><input type="checkbox" name="paCheck[]" value="{$i[0]}" {if $i[3] eq 'S'} checked {/if}/></td>
            </tr>
         {/foreach}
         </td></tr>
      </table>
      </fieldset>
      <fieldset style="width:900px" align="center">
      <legend align="left"><font face="calibri" size=3 color=blue>Observaciones</font></legend>
      <textarea class="memo" name="pmObserv">{$smObserv}</textarea>
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