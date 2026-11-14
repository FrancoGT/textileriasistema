<!DOCTYPE html>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<html>
<head>
   <link href="CSS/Sixavi.css" media="screen" rel="stylesheet" type="text/css"/>
   <title>Gestión de Seguridad</title>
   {if $scBehavior != "0"}
   <script>
      function FuncionInit() {
         var objDiv = document.getElementById("div1");
         objDiv.scrollTop = objDiv.scrollHeight;
      }

      window.onload = function() {
         var pos = window.name || 0;
         window.scrollTo(0, pos);
		 FuncionInit();
      }
     
      window.onunload = function() {
         window.name = self.pageYOffset || (document.documentElement.scrollTop + document.body.scrollTop);
      } 
   </script>
   {/if}
</head>
<body>
<form action="Seg1340.php#no-back" method="post">
   <div class="wrapper">
   <div id="MH">
           <ul>
                <li style="margin-left: 50px;"><h1>Mantenimiento de Factores Críticos de Riesgo (FCR)</h1></li>
				<li style="float:right;margin-right: 100px;"><a onclick="self.location = 'index.php'"> Cerrar Sesión</a></li>
           </ul>
   </div>
   <div id="topnavbar"></div>
   <div id="navigation">
   <div id="navcontainer">
   {if $scBehavior eq "0"}
      <table>
         <tr>
         <td>Código del FCR:</td>
         <td>
            <select name = "pcTipo">
            {foreach from = $saTipo item = i}
               <option value="{$i[0]}">{$i[1]}</option>
            {/foreach}
            </select>
         </td>
         </tr>
      </table>
      <table>
         <td><input type="submit" name="Aplicar1" value="Aplicar"/></td>
         <td><input type="submit" name="Nuevo" value="Nuevo"/></td>
         <td><input type="submit" name="Editar" value="Editar"/></td>
         <td><input type="button" value="Salir" onclick="self.location = 'Mnu0000.php?Id=1000'"/></td>
      </table>
   {elseif $scBehavior eq "1"}
      <table>
         <tr>
         <td>Tipo de Factor Crítico de Riesgo:</td>
         <td><font color=blue>{$scDescri}</font></td>
         </tr>
      </table>
      <div id="div1" style="overflow:scroll; height:400px; width:800px; overflow:auto; border:2px solid gray;">
      <table border=1 style="font-size:11px">
         <tr>
         <th width='50px' align='center'>Id</th>
         <th width='80px' align='center'>Código</th>
         <th width='300px' align='center'>Descripcion</th>
         <th width='200px' align='center'>Estado</th>
         <th></th>
         <th width='50px' align='center'><img src="CSS/anular.bmp" /></th>
         </tr>
         {foreach from = $saData item = i}
            <tr>
            <td align='center'>{$i[0]}<input type = "hidden" name = "paData[{$i[0]}][0]" value="{$i[0]}"/></td>
            {if $i[4] eq '1'}
               <td align='center'><input type = "text" name = "paData[{$i[0]}][1]" value="{$i[1]}" size="12"/></td>
            {else}
               <td align='center'>{$i[1]}<input type = "hidden" name = "paData[{$i[0]}][1]" value="{$i[1]}"/></td>
            {/if}
            <td align='center'><input type = "text" name = "paData[{$i[0]}][2]" value="{$i[2]}" style='width: 400px'/></td>
            <td align='center'>
               <select  width="200" style="width: 190px; font-size:11px" name = "paData[{$i[0]}][3]">
                  {foreach from = $saEstado item = j}
                     <option value = "{$j[0]}" {if $i[4] eq $j[0]} selected {/if}> {$j[1]} </option>
                  {/foreach}
               </select>
            </td>
            <td><input type = "hidden" name = "paData[{$i[0]}][4]" value="{$i[4]}"/></td>
            {if $i[4] eq '1'}
               <td align='center'><input type = "checkbox" name = "paCheck[]" value = "{$i[0]}"/></td>
            {else}
               <td></td>
            {/if}
            </tr>
         {/foreach}
      </table>
      </div>
      <table>
         <tr>
         <td><input type="submit" name="Nuevo1" value="Nuevo" class="boton"/></td>
         <td><input type="submit" name="Eliminar" value="Eliminar" class="boton"/></td>
         </tr>
      </table>
      <table>
         <tr>
         <td><input type="submit" name="Grabar1" value="Grabar"/></td>
         <td><input type="submit" name="Cancelar1" value="Cancelar"/></td>
         </tr>
      </table>
   {elseif $scBehavior eq "2"}
      <table>
         <tr>
         <td>Código de FCR:</td>
         {if empty($scCodFcr)}
            <td><input type="text" maxlength="12" name="pcCodFcr" value="{$scCodFcr}"/></td>
         {else}
            <td>{$scCodFcr}<input type="hidden" maxlength="12" name="pcCodFcr" value="{$scCodFcr}"/></td>
         {/if}
         </tr><tr>
         <td>Descripción:</td>
         <td><input type="text" maxlength="90" name="pcDescri" size="60" value="{$scDescri}"/></td>
         </tr>
      </table>
      <table>
         <tr>
         <td><input type="submit" name="Grabar" value="Grabar"/></td>
         <td><input type="submit" name="Cancelar" value="Cancelar"/></td>
         </tr>
      </table>
   {elseif $scBehavior eq "3"}
      <table>
         <tr>
         <td>Tipo de Norma:</td>
         <td><input type="text" maxlength="6" name="pcDesTip" value="{$pcDesTip}" disabled/></td>
         <td><input hidden="true" type="text" maxlength="6" name="pcTipo" value="{$pcTipo}" disabled/></td>
         </tr><tr>
         <td>Codigo de Norma:</td>
         <td><input type="text" maxlength="10" name="pcCodFcr" value=""/></td>
         </tr><tr>
         <td>Descripcion:</td>
         <td><input type="text" maxlength="90" size="60" name="pcDescri" value=""/></td>
         </tr><tr>
         <td>Estados:</td>
         <td>
            <select name="pcEstado">
               {foreach from = $saEstado item = i}
                  <option value="{$i[0]}">{$i[1]}</option>
               {/foreach}
            </select>
         </td>
         </tr>
      </table>
      <table>
         <tr>
         <td></td>
         <td><input type="submit" name="Grabar1" value="Grabar1"/></td>
         <td><input type="button" value="Salir" onclick="self.location = 'Mnu0000.php?Id=1000'"/></td>
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
