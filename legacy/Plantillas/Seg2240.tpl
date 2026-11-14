<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<html>
<head>
   <link href="CSS/Sixavi.css" media="screen" rel="stylesheet" type="text/css" />
   <title>Seguridad Operativa</title>
   <script src="CSS/Libreria.js" language="JavaScript"></script>
   {if $scBehavior eq '4' or $scBehavior eq '2' or $scBehavior eq '3' or $scBehavior eq '5'}
   <script language="JavaScript">
   window.onload = function() {
         var pos = window.name || 0;
         window.scrollTo(0, pos);
		 FuncionInit();
      }
     
      window.onunload = function() {
         window.name = self.pageYOffset || (document.documentElement.scrollTop + document.body.scrollTop);
      }
	  function FuncionInit() {
	     var objDiv = document.getElementById("div1");
         objDiv.scrollTop = objDiv.scrollHeight;
		 objDiv = document.getElementById("div2");
         objDiv.scrollTop = objDiv.scrollHeight;
      }
   </script>
   {/if}
</head>
<body>
<form method = "post" action = "Seg2240.php#no-back">
   <div class="wrapper">
   <div id="MH">
      <ul>
      <li style="margin-left: 50px;"><h1>Análisis de Causa Raíz</h1></li>
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
         <td><input type="submit" name="Boton9" value="Aplicar"/></td>
         <td><input type="submit" name="Boton9" value="Buscar"/></td>
         <td><input type="submit" name="Boton9" value="Salir"/></td>
         </tr>
      </table>
   {elseif $scBehavior eq '1'}
      <fieldset style="width:1000px" align="center">
      <table border = 0 cellspacing="0" cellpadding="0" style="font-size:11px">
         <tr>
         <td>Número de Incidente</td>
         <td>{$scNroInc}</td>
         </tr><tr>
         <td>Descripción</td>
         <td><textarea class="memo" name = "pmDatos" style="background:#FFFFFF" disabled>{$smDatos}</textarea></td>
         </tr>
      </table>
      </fieldset>
      <table>
         <tr>
         <td><input type="submit" name="Boton1" value="Factor Crítico" class="boton" style="width: 200px!important"/></td>
         <td><input type="submit" name="Boton1" value="Causas Inmediatas" class="boton" style="width: 200px!important"/></td>
         <td><input type="submit" name="Boton1" value="Causas Básicas" class="boton" style="width: 200px!important"/></td>
         <td><input type="submit" name="Boton1" value="Control Gestión" class="boton" style="width: 200px!important"/></td>
         <td><input type="submit" name="Boton1" value="Conclusiones" class="boton" style="width: 200px!important"/></td>
         </tr>
      </table> 
      </fieldset>
      <table>
         <tr>
         <td><input type="submit" name="Boton1" value="Cancelar"/></td>
		 <td><input type="submit" name="Boton1" value="Siguiente"/></td>
         </tr>
      </table>
   {elseif $scBehavior eq '2'}
      <fieldset style="width:1000px" align="center">
      <table border = 0 cellspacing="0" cellpadding="0" style="font-size:11px">
         <tr>
         <td>Número de Incidente</td>
         <td>{$scNroInc}</td>
         </tr><tr>
         <td>Descripción</td>
         <td><textarea class="memo" name = "pmDatos" style="background:#FFFFFF" disabled>{$smDatos}</textarea></td>
         </tr>
      </table>
      </fieldset>
      <!-- Actos Sub-Estándares -->
      <fieldset style="width:1000px" align="center">
      <legend><font face="calibri" size=4 color=blue>Prácticas/Actos Sub-Estándares</font></legend>
      <div id="div1" style="overflow:scroll; height:130px; width:900px; overflow:auto; border: 3px;" onload="FuncionInit()">     
      <table border = 1 style="font-size:10px">
         <tr>
         <th width='20'>Id</th>
         <th width='300'>Factor</th>
         <th width='300'>Comentarios</th>
         <th width='5px'><img src="CSS/anular.bmp" style="width: 100%; height: 100%;"/></th>
         </tr>
         {foreach from = $saData1 item = i}            
            <tr>
            <td align = 'center'>{$i[0]}<input type = "hidden" name = "paData1[{$i[0]}][0]" value="{$i[0]}"/></td>
            <td>
               <select name = "paData1[{$i[0]}][1]">
               {foreach from = $saFacCr1 item = j}
                  <option value = "{$j[0]}" {if $i[1] eq $j[0]} selected {/if}> {$j[1]}</option>
               {/foreach}
               </select>
            </td>
            <td align = 'center'><input type = "text" maxlength = "90" name = "paData1[{$i[0]}][2]" size='70' value="{$i[2]}"/></td>
            <td><input type = "radio" name = "paCheck1[]" value = "{$i[0]}"/></td>
            </tr>
         {/foreach}
      </table>  
      </div>
      <table>
         <tr>
         <td><input type="submit" name="Boton2" value="Nuevo" class="boton" /></td>
         <td><input type="submit" name="Boton2" value="Eliminar" class="boton" /></td>
         </tr>
      </table> 
      </fieldset>
      <!-- Prácticas Sub-estándar -->
      <fieldset style="width:1000px" align="center">
      <legend><font face="calibri" size=4 color=blue>Condiciones Sub-Estándares</font></legend>
      <div id='div2' style="overflow:scroll; height:130px; width:900px; overflow:auto; border: 1px;">     
      <table border = 2 width=550 style="font-size:11px">
         <tr>
         <th width=20>Id</th>
         <th width=200>Factor</th>
         <th width=300>Comentarios</th>
         <th width=30><img src="CSS/anular.bmp" style="width: 100%; height: 100%;"/></th>
         </tr>
         {foreach from = $saData2 item = i}            
            <tr>
            <td align = 'center'>{$i[0]}<input type = "hidden" name = "paData2[{$i[0]}][0]" value="{$i[0]}"/></td>
            <td>
               <select name = "paData2[{$i[0]}][1]" width =300>
               {foreach from = $saFacCr2 item = j}
                  <option value = "{$j[0]}" {if $i[1] eq $j[0]} selected {/if}> {$j[1]}</option>
               {/foreach}
               </select>
            </td>
            <td align = 'center'><input type = "text" maxlength = "90" name = "paData2[{$i[0]}][2]" size='50' value="{$i[2]}"/></td>
            <td><input type = "radio" name = "paCheck2[]" value = "{$i[0]}"/></td>
            </tr>
         {/foreach}
      </table>  
      </div>
      <table>
         <tr>
         <td><input type="submit" name="Nuevo2" value="Nuevo" class="boton" /></td>
         <td><input type="submit" name="Eliminar2" value="Eliminar" class="boton" /></td>
         </tr>
      </table> 
      </fieldset>
      <table>
         <tr>
         <td>Comentarios</td>
         <td><textarea class="memo" name = "pmComSbe">{$smComSbe}</textarea></td>
         </tr>
      </table>
      <table>
         <tr>
         <td><input type="submit" name="Grabar1" value="Grabar"/></td>
         <td><input type="submit" name="Cancelar1" value="Cancelar"/></td>
         </tr>
      </table>
   {elseif $scBehavior eq '3'}
      <fieldset style="width:1000px" align="center">
      <table border = 0 cellspacing="0" cellpadding="0" style="font-size:11px">
         <tr>
         <td>Número de Incidente</td>
         <td>{$scNroInc}</td>
         </tr><tr>
         <td>Descripción</td>
         <td><textarea class="memo" name = "pmDatos" style="background:#FFFFFF" disabled>{$smDatos}</textarea></td>
         </tr>
      </table>
      </fieldset>
      <!-- Factores Personales -->
      <fieldset style="width:1000px" align="center">
      <legend><font face="calibri" size=4 color=blue>Factores Personales</font></legend>
      <div id='div1' style="overflow:scroll; height:140px; width:900px; overflow:auto; border: 1px;">     
      <table border = 2 width=650 style="font-size:10px">
         <tr>
         <th width=20>Id</th>
         <th width=300>Factor</th>
         <th width=300>Comentarios</th>
         <th width=30><img src="CSS/anular.bmp" style="width: 100%; height: 100%;"/></th>
         </tr>
         {foreach from = $saData3 item = i}            
            <tr>
            <td align = 'center'>{$i[0]}<input type = "hidden" name = "paData3[{$i[0]}][0]" value="{$i[0]}"/></td>
            <td>
               <select name = "paData3[{$i[0]}][1]" width=300>
               {foreach from = $saFacCr3 item = j}
                  <option value = "{$j[0]}" {if $i[1] eq $j[0]} selected {/if}> {$j[1]}</option>
               {/foreach}
               </select>
            </td>
            <td align = 'center'><input type = "text" maxlength = "90" name = "paData3[{$i[0]}][2]" size='50' value="{$i[2]}" /></td>
            <td><input type = "radio" name = "paCheck3[]" value = "{$i[0]}"/></td>
            </tr>
         {/foreach}
      </table>  
      </div>
      <table>
         <tr>
         <td><input type="submit" name="Boton3" value="Nuevo" class="boton" /></td>
         <td><input type="submit" name="Boton3" value="Eliminar" class="boton" /></td>
         </tr>
      </table> 
      </fieldset>
      <!-- Factores de Trabajo -->
      <fieldset style="width:1000px" align="center">
      <legend><font face="calibri" size=4 color=blue>Factores de Trabajo</font></legend>
      <div id='div2' style="overflow:scroll; height:140px; width:900px; overflow:auto; border: 1px;">     
      <table border = 2  style="font-size:10px">
         <tr>
         <th width=20>Id</th>
         <th width=200>Factor</th>
         <th width=300>Comentarios</th>
         <th width=30><img src="CSS/anular.bmp" style="width: 100%; height: 100%;"/></th>
         </tr>
         {foreach from = $saData4 item = i}            
            <tr>
            <td align = 'center'>{$i[0]}<input type = "hidden" name = "paData4[{$i[0]}][0]" value="{$i[0]}"/></td>
            <td>
               <select name = "paData4[{$i[0]}][1]" style="width:450">
               {foreach from = $saFacCr4 item = j}
                  <option value = "{$j[0]}" {if $i[1] eq $j[0]} selected {/if}> {$j[1]}</option>
               {/foreach}
               </select>
            </td>
            <td align = 'center'><input type = "text" maxlength = "90" name = "paData4[{$i[0]}][2]" size='60' value="{$i[2]}" /></td>
            <td><input type = "radio" name = "paCheck4[]" value = "{$i[0]}"/></td>
            </tr>
         {/foreach}
      </table>  
      </div>
      <table>
         <tr>
         <td><input type="submit" name="Boton4" value="Nuevo" class="boton" /></td>
         <td><input type="submit" name="Boton4" value="Eliminar" class="boton" /></td>
         </tr>
      </table> 
      </fieldset>
      <table>
         <tr>
         <td>Comentarios</td>
         <td><textarea class="memo" name = "pmComFac">{$smComFac}</textarea></td>
         </tr>
      </table>
      <table>
         <tr>
         <td><input type="submit" name="Grabar2" value="Grabar"/></td>
         <td><input type="submit" name="Cancelar2" value="Cancelar"/></td>
         </tr>
      </table>
   {elseif $scBehavior eq '4'}
      <fieldset style="width:1000px" align="center">
      <table border = 0 cellspacing="0" cellpadding="0" style="font-size:11px">
         <tr>
         <td>Número de Incidente</td>
         <td>{$scNroInc}</td>
         </tr><tr>
         <td>Descripción</td>
         <td><textarea class="memo" name = "pmDatos" style="background:#FFFFFF" disabled>{$smDatos}</textarea></td>
         </tr><tr>
         <td>Factores Críticos</td>
         <td><textarea class="memo" name = "pmFacCri">{$smFacCri}</textarea></td>
         </tr><tr>
      </table>
      </fieldset>
      <table>
         <tr>
         <td><input type="submit" name="Grabar3" value="Grabar"/></td>
         <td><input type="submit" name="Cancelar3" value="Cancelar"/></td>
         </tr>
      </table>
   {elseif $scBehavior eq '5'}
      <fieldset style="width:1000px" align="center">
      <table border = 0 cellspacing="0" cellpadding="0" style="font-size:11px">
         <tr>
         <td>Número de Incidente</td>
         <td>{$scNroInc}</td>
         </tr><tr>
         <td>Descripción</td>
         <td><textarea class="memo" name = "pmDatos" style="background:#FFFFFF" disabled>{$smDatos}</textarea></td>
         </tr>
      </table>
      </fieldset>
      <!-- Control de Gestión -->
      <fieldset style="width:1000px" align="center">
      <legend><font face="calibri" size=4 color=blue>Control de Gestión</font></legend>
      <div id='div1' style="overflow:scroll; height:140px; width:900px; overflow:auto; border: 1px;" >     
      <table border = 1 style="font-size:10px">
         <tr>
         <th width='20'>Id</th>
         <th width='300'>Factor</th>
         <th width='300'>Comentarios</th>
         <th width='5px'><img src="CSS/anular.bmp" style="width: 100%; height: 100%;"/></th>
         </tr>
         {foreach from = $saData5 item = i}            
            <tr>
            <td align = 'center'>{$i[0]}<input type = "hidden" name = "paData5[{$i[0]}][0]" value="{$i[0]}"/></td>
            <td>
               <select name = "paData5[{$i[0]}][1]" style="width:420">
               {foreach from = $saFacCr5 item = j}
                  <option value = "{$j[0]}" {if $i[1] eq $j[0]} selected {/if}> {$j[1]}</option>
               {/foreach}
               </select>
            </td>
            <td align = 'center'><input type = "text" maxlength = "90" name = "paData5[{$i[0]}][2]" size='70' value="{$i[2]}" /></td>
            <td><input type = "radio" name = "paCheck5[]" value = "{$i[0]}"/></td>
            </tr>
         {/foreach}
      </table>  
      </div>
      <table>
         <tr>
         <td><input type="submit" name="Nuevo5" value="Nuevo" class="boton" /></td>
         <td><input type="submit" name="Eliminar5" value="Eliminar" class="boton" /></td>
         </tr>
      </table> 
      </fieldset>
	  <table border = 0 cellspacing="0" cellpadding="0" style="font-size:11px">
         <tr>
         <td>Comentarios </td>
         <td><textarea class="memo" name = "pmComCge">{$smComCge}</textarea></td>
         </tr><tr>
      </table>
      <table>
         <tr>
         <td><input type="submit" name="Grabar4" value="Grabar"/></td>
         <td><input type="submit" name="Cancelar4" value="Cancelar"/></td>
         </tr>
      </table>
  {elseif $scBehavior eq '6'}
      <fieldset style="width:1000px" align="center">
      <table border = 0 cellspacing="0" cellpadding="0" style="font-size:11px">
         <tr>
         <td>Número de Incidente</td>
         <td>{$scNroInc}</td>
         </tr><tr>
         <td>Descripción</td>
         <td><textarea class="memo" name = "pmDatos" style="background:#FFFFFF" disabled>{$smDatos}</textarea></td>
         </tr><tr>
         <td>Conclusiones</td>
         <td><textarea class="memo" name = "pmConclu">{$smConclu}</textarea></td>
         </tr>
      </table>
      </fieldset>
      <table>
         <tr>
         <td><input type="submit" name="Boton6" value="Grabar"/></td>
         <td><input type="submit" name="Boton6" value="Cancelar"/></td>
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