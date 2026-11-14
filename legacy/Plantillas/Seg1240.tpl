<!DOCTYPE html>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<html>
<head>
   <link href="CSS/Sixavi.css" media="screen" rel="stylesheet" type="text/css"/>
   <title>Gestión de Seguridad</title>
   <script>
      function FuncionInit() {
         var objDiv = document.getElementById("div1");
         objDiv.scrollTop = objDiv.scrollHeight;
      }
   </script>
</head>
<body onload="FuncionInit()">
<form action="Seg1240.php#no-back" method="post">
   <div class="wrapper">
   <div id="MH">
           <ul>
                <li style="margin-left: 50px;"><h1>Mantenimiento de Normas</h1></li>
				<li style="float:right;margin-right: 100px;"><a onclick="self.location = 'index.php'"> Cerrar Sesión</a></li>
           </ul>
   </div>
   <div id="topnavbar"></div>
   <div id="navigation">
   <div id="navcontainer">
   {if $scBehavior eq "0"}
      <table>
         <tr>
         <td>Código de la Norma:</td>
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
         <td><input type="submit" name="Boton1" value="Aplicar"/></td>
         <td><input type="submit" name="Boton1" value="Nuevo"/></td>
         <td><input type="submit" name="Boton1" value="Editar"/></td>
         <td><input type="submit" name="Boton1" value="Salir"/></td>
      </table>
   {elseif $scBehavior eq "1"}
      <table>
         <tr>
         <td>Tipo de Norma:</td>
         <td><font color="blue">{$scTipo} - {$scDescri}</font><input type="hidden" name="pcDescri" value="{$scDescri}"/></td>
         </tr>
      </table>
      <div id="div1" style="overflow:scroll; height:400px; width:1000px; overflow:auto; border:2px solid gray;">
      <table border=1 cellspacing="0" cellpadding="0" style="font-size:11px">
         <tr>
         <th width='50px' align='center'>Id</th>
         <th width='80px' align='center'>Código</th>
         <th width='300px' align='center'>Descripción</th>
         <th width='200px' align='center'>Estado</th>
         <th width='50px' align='center'>Base</th>
         <th width='50px' align='center'><img src="CSS/anular.bmp" /></th>
         </tr>
         {foreach from = $saData item = i}
            <tr>
            <td align='center'>{$i[0]}<input type = "hidden" name = "paData[{$i[0]}][0]" value="{$i[0]}"/></td>
            {if $i[5] eq '1'}
               <td align='center'><input type = "text" name = "paData[{$i[0]}][1]" value="{$i[1]}" size="12"/></td>
            {else}
               <td align='center'>{$i[1]}<input type = "hidden" name = "paData[{$i[0]}][1]" value="{$i[1]}"/></td>
            {/if}
            <td align='center'><input type = "text" name = "paData[{$i[0]}][2]" value="{$i[2]}" style='width: 400px'/></td>
            <td align='center'>
               <select  width="200" style="width: 190px; font-size:11px" name = "paData[{$i[0]}][3]">
               {foreach from = $saEstado item = j}
                  <option value = "{$j[0]}" {if $i[4] eq $j[0]} selected {/if}>{$j[1]} </option>
               {/foreach}
               </select>
               <input type = "hidden" name = "paData[{$i[0]}][5]" value="{$i[5]}"/>
            </td>
            <td align="center"><input type="checkbox" name="paData[{$i[0]}][4]" {if $i[4] eq 'S'} checked {/if} value="S"/></td>
            {if $i[5] eq '1'}
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
         <td><input type="submit" name="Boton2" value="Nuevo" class="boton"/></td>
         <td><input type="submit" name="Boton2" value="Eliminar" class="boton"/></td>
         </tr>
      </table>
      <table>
         <tr>
         <td><input type="submit" name="Boton2" value="Grabar"/></td>
         <td><input type="submit" name="Boton2" value="Cancelar"/></td>
         </tr>
      </table>
   {elseif $scBehavior eq "2"}
      <table>
         <tr>
         <td>Código de Norma:</td>
         {if empty($scCodNor)}
            <td><input type="text" maxlength="12" name="pcCodNor" value="{$scCodNor}"/></td>
         {else}
            <td>{$scCodNor}<input type="hidden" maxlength="12" name="pcCodNor" value="{$scCodNor}"/></td>
         {/if}
         </tr><tr>
         <td>Descripción:</td>
         <td><input type="text" maxlength="90" name="pcDescri" size="60" value="{$scDescri}"/></td>
         </tr>
      </table>
      <table>
         <tr>
         <td><input type="submit" name="Boton3" value="Grabar"/></td>
         <td><input type="submit" name="Boton3" value="Cancelar"/></td>
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
