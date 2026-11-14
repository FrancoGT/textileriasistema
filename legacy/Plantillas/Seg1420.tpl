<!DOCTYPE html>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<html>
<head>
   <link href="CSS/Sixavi.css" media="screen" rel="stylesheet" type="text/css"/>
   <title>Seguridad Operativa</title>
</head>
<body>
<form action="Seg1420.php#no-back" method="post">
   <div class="wrapper">
   <div id="MH">
      <ul>
      <li style="margin-left: 50px;"><h1>Registro de Licencias</h1></li>
      <li style="float:right;margin-right: 100px;"><a onclick="self.location = 'index.php'"> Cerrar Sesión</a></li>
      </ul>
   </div>
   <div id="topnavbar"></div>
   <div id="navigation">
   <div id="navcontainer">
   {if $scBehavior eq "0"}
      <table>
         <tr>
         <td>Personal:</td>
         <td>
            <select name="pcCodPer">
            {foreach from = $saCodPer item = i}
               <option value="{$i[0]}">{$i[1]}</option>
            {/foreach}
            </select>
         </td>
         </tr>
      </table>
      <table>
         <td><input type="submit" name="Boton1" value="Aplicar"/></td>
         <td><input type="submit" name="Boton1" value="Salir"/></td>
      </table>
   {elseif $scBehavior eq "1"}
      <table>
         <tr>
         <td>Personal:</td>
         <td><p class="datos"><p class="datos">{$scCodPer} - {$scNombre} - {$scNroDni} - {$scDesSec}</p><input type="hidden" name="pcCodPer" value="{$scCodPer}"/></td>
         </tr>
      </table>
      <!--<div style="overflow:scroll; height:400px; width:800; overflow-y:scroll; overflow:auto; border:1px">-->
      <div style="overflow:scroll; height:400px; overflow-y:scroll; overflow:auto; border:1px">
      <table width="100%" border=1 style="font-size:10px">
         <tr>
         <th align='center'>Id</th>
         <th align='center'>Tipo Licencia</th>
         <th align='center'>Fecha Expedición</th>
         <th align='center'>Fecha Expiración</th>
         <th align='center'>Número Licencia</th>
         <th align='center'>Nota</th>
         <th align='center'>Estado</th>
         <th align='center'>Cargo</th>
         <th align='center'>Empresa</th>
         <th width='50px' align="center"><img src="CSS/anular.bmp" /></th>
         </tr>
         {foreach from = $saData item = i}
            <tr>
            <td align='center'>{$i[0]}<input type="hidden" name="paData[{$i[0]}][0]" value="{$i[0]}"/></td>
            <td>
               <select width="150px" style="width:145px" name="paData[{$i[0]}][1]">
               {foreach from = $saTipLic item = j}
                  <option value="{$j[0]}" {if $i[1] eq $j[0]} selected {/if}> {$j[1]} </option>
               {/foreach}
               </select>
            </td>
            <td align='center'><input type="text" name="paData[{$i[0]}][2]" value="{$i[2]}" size='12' maxlength='10'/></td>
            <td align='center'><input type="text" name="paData[{$i[0]}][3]" value="{$i[3]}" size='12' maxlength='10'/></td>
            <td align='center'><input type="text" name="paData[{$i[0]}][4]" value="{$i[4]}" size='8' maxlength='20'/></td>
            <td align='center'><input type="text" name="paData[{$i[0]}][5]" value="{$i[5]}" size='3' maxlength='3'/></td>
            <td align='center'>
               <select width="150px" style="width:145px" name="paData[{$i[0]}][6]">
               {foreach from = $saEstado item = j}
                  <option value="{$j[0]}" {if $i[6] eq $j[0]} selected {/if}> {$j[1]} </option>
               {/foreach}
               </select>
            </td>
            <td align='center'>
               <select width="150px" style="width:145px" name="paData[{$i[0]}][7]">
               {foreach from = $saCargo item = j}
                  <option value="{$j[0]}" {if $i[7] eq $j[0]} selected {/if}> {$j[1]} </option>
               {/foreach}
               </select>
            </td>
            <td align='center'>
               <select width="150px" style="width:145px" name="paData[{$i[0]}][8]">
               {foreach from = $saCodEmp item = j}
                  <option value="{$j[0]}" {if $i[8] eq $j[0]} selected {/if}> {$j[1]} </option>
               {/foreach}
               </select>
            </td>
            </td><td align='center'><input type="checkbox" name="paCheck[]" value="{$i[0]}"/></td>
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
