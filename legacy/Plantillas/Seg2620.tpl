<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<html>
<head>
   <link href="CSS/Sixavi.css" media="screen" rel="stylesheet" type="text/css" />
   <script src="CSS/Libreria.js" language="JavaScript"></script>
   <style>
      #navcontainer a { font-size:small; display:block; color:#333; background-color:#E8E8E8; border:#333 1px solid;  width:400px; padding:5px 5px 5px 5px; text-decoration:none; }
      #navcontainer a:hover { background-color: #E8E8E8; color: blue;}
   </style>
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
   <title>Trazabilidad de Comprobantes</title>
</head>
<body onload="f_FuncionInit();">
<form method="post" action="Seg2620.php#no-back" enctype="multipart/form-data">
   <div class="wrapper">
   <div id="MH">
      <ul>
      <li style="margin-left: 50px;"><h1>Pruebas de Eficiencia</h1></li>
      <li style="float:right;margin-right: 100px;"><a onclick="self.location = 'index.php'"> Cerrar Sesión</a></li>
      </ul>
   </div>
   <div id="topnavbar"></div>
   <div id="navigation">
   <div id="navcontainer">
   {if $scBehavior eq '0'}  
      <table style="font-size:11px">
         <tr>
         <td>Personal:</td>
         <td>Empresa:</td>
         <td>Proyecto:</td>
         </tr><tr>
         <td><select name="pcCodPer" autofocus width="300" style="width: 300px">
             {foreach from = $saCodPer item = i}
                <option value="{$i[0]}" {if $scCodPer eq $i[0]} selected {/if} autofocus> {$i[1]}</option>
             {/foreach}
             </select>
         </td>
         <td><select name="pcCodEmp">
             {foreach from = $saCodEmp item = i}
                <option value="{$i[0]}" {if $scCodEmp eq $i[0]} selected {/if}> {$i[1]}</option>
             {/foreach}
             </select>
         </td>
         <td><select name="pcCodPry">
             {foreach from = $saCodPry item = i}
                <option value="{$i[0]}" {if $scCodPry eq $i[0]} selected {/if}> {$i[1]}</option>
             {/foreach}
             </select>
         </td>
         </tr><tr>
         <td>Tipo Evaluación:</td>
         <!-- <td>Area:</td> -->
         <td>Sub-División:</td>
         </tr><tr>
         <td><select name="pcTipEva">
            {foreach from = $saTipEva item = i}
               <option value="{$i[0]}" {if $scTipEva eq $i[0]} selected {/if}> {$i[1]}</option>
            {/foreach}
            </select>
         </td>
         <!-- <td><select name="pcArea">
            {foreach from = $saArea item = i}
               <option value="{$i[0]}" {if $scArea eq $i[0]} selected {/if}> {$i[1]}</option>
            {/foreach}
            </select>
         </td> -->
         <td><select name="pcSubDiv">
            {foreach from = $saSubDiv item = i}
               <option value="{$i[0]}" {if $scSubDiv eq $i[0]} selected {/if}> {$i[1]}</option>
            {/foreach}
            </select>
         </td>
         </tr><tr>
         <td>Desde:</td>
         <td>Hasta:</td>
         <td>Unidad 1:</td>
         </tr><tr>
         <td><input type='text' name='pdFecIni' value='{$sdFecIni}' maxlength="10" placeholder="aaaa-mm-dd" onKeyPress="return fDate(event)"/></td>
         <td><input type='text' name='pdFecFin' value='{$sdFecFin}' maxlength="10" placeholder="aaaa-mm-dd" onKeyPress="return fDate(event)"/></td>
         <td><select name="paCodUni[0]" width="300" style="width: 300px">
             {foreach from = $saCodUni item = i}
                <option value="{$i[0]}" {if $scCodUn1 eq $i[0]} selected {/if}> {$i[1]}</option>
             {/foreach}
             </select>
         </td>
         </tr><tr>
         <td>Unidad 2:</td>
         <td>Unidad 3:</td>
         <td>Tipo Vagón:</td>
         </tr><tr>
         <td><select name="paCodUni[1] width="300" style="width: 300px"">
            {foreach from = $saCodUni item = i}
               <option value="{$i[0]}" {if $scCodUn2 eq $i[0]} selected {/if}> {$i[1]}</option>
            {/foreach}
            </select>
         </td>
         <td><select name="paCodUni[2] width="300" style="width: 300px"">
            {foreach from = $saCodUni item = i}
               <option value="{$i[0]}" {if $scCodUn3 eq $i[0]} selected {/if}> {$i[1]}</option>
            {/foreach}
            </select>
         </td>
         <td><select name="pcTipVag">
            {foreach from = $saTipVag item = i}
               <option value="{$i[0]}" {if $scTipVag eq $i[0]} selected {/if}> {$i[1]}</option>
            {/foreach}
            </select>
         </td>
         </tr><tr>
         <td>Estación Inicial: </td>
         <td>PK Inicial: </td>
         <td>Supervisor 1: </td>
         </tr><tr>
         <td>
            <select name="pcEstaci" id ="pcEstaci" onchange="myFunction0()">
            {foreach from = $saEstaci item = i}
               <option value="{$i[0]}" {if $scEstaci eq $i[0]} selected {/if} > {$i[1]}</option>
            {/foreach}
            </select>
         </td>
         <td><input type="text" name="pcKilome" id ="pcKilome" value="{$scKilome}" size='5'/></td>
         <td>
            <select name = "pcSuperv" style="width: 300px; font-size:11px">
            {foreach from = $saSuperv item = i}
               <option value = "{$i[0]}" {if $scSuperv eq $i[0]} selected {/if} > {$i[1]}</option>
            {/foreach}
            </select>
         </td>
         </tr><tr>
         <td>Estación Final: </td>
         <td>PK Final: </td>
         <td>Supervisor 2: </td>
         </tr><tr>
         <td>
            <select name="pcEstacf" id ="pcEstacf"onchange="myFunction1()">
            {foreach from = $saEstaci item = i}
              <option value="{$i[0]}" {if $scEstacf eq $i[0]} selected {/if} > {$i[1]}</option>
           {/foreach}
           </select>
         </td>
         <td><input type="text" name="pcKilomf" id ="pcKilomf" value="{$scKilomf}" size='5'/></td>
         <td>
            <select name = "pcSuper2" style="width: 300px; font-size:11px">
            {foreach from = $saSuperv item = i}
               <option value = "{$i[0]}" {if $scSuper2 eq $i[0]} selected {/if} > {$i[1]}</option>
            {/foreach}
            </select>
         </td>
         </tr><tr>   
         <td>Normas</td>
         </tr><tr>
         <td><select name="pcCodNor" width="300" style="width: 300px">
             {foreach from = $saCodNor item = i}
                <option value="{$i[0]}" {if $scCodNor eq $i[0]} selected {/if}> {$i[1]}</option>
             {/foreach}
             </select>
         </td>
      </table>
      <table>
         <td><input type="submit" name="Boton1" value="Generar"/></td>
         <td><input type="submit" name="Boton1" value="Salir"/></td>
         </tr>
      </table>
   {elseif $scBehavior eq "1"}
      <fieldset style="width:1000px" align="center">
      <legend><font face="calibri" size=4 color=blue>Archivos Anexos</font></legend>
      <table border=0 cellspacing="0" cellpadding="0" style="font-size:11px">
         {foreach from = $saData item = i}
            <tr>
            <td align='center'><a href="{$i[0]}" target="_blank">{$i[1]}</a></td>
            </tr>
         {/foreach}            
      </table>
      </fieldset>
      <table>
         <tr>
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
