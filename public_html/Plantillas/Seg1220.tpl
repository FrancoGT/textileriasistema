<!DOCTYPE html>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<html>
<head>
   <link href="CSS/Sixavi.css" media="screen" rel="stylesheet" type="text/css"/>
   <script src="CSS/Libreria.js" language="JavaScript"></script>
   <title>Gestión de Seguridad</title>
</head>
<body>
<form action="Seg1220.php#no-back" method="post">
   <div class="wrapper">
   <div id="MH">
           <ul>
                <li style="margin-left: 50px;"><h1>Mantenimiento de Empresas</h1></li>
				<li style="float:right;margin-right: 100px;"><a onclick="self.location = 'index.php'"> Cerrar Sesión</a></li>
           </ul>
   </div>
   <div id="topnavbar"></div>
   <div id="navigation">
   <div id="navcontainer">
   {if $scBehavior eq "0"}
      <table border=1 style="font-size:11px">
         <tr>
         <th width='50' align="center">Código</th>
         <th width='490' align="center">Descripción</th>
         <th width='25' align="center"><img src="CSS/Edit.png" style="width: 100%; height: 10%;"/></th>
         </tr>
         {foreach from = $saData item = i}               
            <tr>
            <td align="center">{$i[0]}</td>
            <td>{$i[1]}</td>
            <td align="center"><input type = "radio" name = "paCheck[]" value = "{$i[0]}"/></td>
            </tr>
         {/foreach}
      </table>  
      <table>
         <td><input type="submit" name="Aplicar" value="Aplicar"/></td>
         <td><input type="submit" name="Nuevo" value="Nuevo"/></td>
         <td><input type = "button" value = "Salir" onclick="self.location = 'Mnu0000.php?Id=1000'" /></td>
      </table>
   {elseif $scBehavior eq "1"}
      <table>
         <tr>
         <td>Código:</td>
         <td>{$scCodEmp}<input type="hidden" name="pcCodEmp" value="{$scCodEmp}"/></td>
         </tr><tr>
         <td>Descripción:</td>
         <td><input type="text" maxlength="90" name="pcDescri" size="70" value="{$scDescri}" onKeyPress="toUpper(this)" onChange="toUpper(this)"/></td>
         </tr><tr>
         <td>Estado:</td>
         <td>
            <select name="pcEstado">
            {foreach from = $saEstado item = i}
               <option value="{$i[0]}" {if $i[0] eq $scEstado} selected {/if}>{$i[1]}</option>
            {/foreach}
            </select>
         </td>
         </tr><tr>
         <td>Datos:</td>
         <td><textarea name="pmDatos" rows="10" cols="50">{$smDatos}</textarea></td>
      </table>
      <table>
         <tr>
         <td></td>
         <td><input type="submit" name="Grabar" value="Grabar"/></td>
         <td><input type="submit" name="Cancelar" value="Cancelar"/></td>
         </tr>
      </table>
   {elseif $scBehavior eq "2"}
      <table>
         <tr>
         <td>Código:</td>
         <td><input type="text" maxlength="6" name="pcCodEmp" value="" onKeyPress="toUpper(this)" onChange="toUpper(this)"/></td>
         </tr><tr>
         <td>Descripción:</td>
         <td><input type="text" maxlength="70" name="pcDescri" size="70" value="" onKeyPress="toUpper(this)" onChange="toUpper(this)"/></td>
         </tr><tr>
         <td>Estado:</td>
         <td>
            <select name="pcEstado">
            {foreach from = $saEstado item = i}
               <option value="{$i[0]}" {if $i[0] eq $scEstado} selected {/if}>{$i[1]}</option>
            {/foreach}
            </select>
         </td>
         </tr><tr>
         <td>Datos varios:</td>
         <td><textarea name="pmDatos" rows="10" cols="50"></textarea></td>
      </table>
      <table>
         <tr>
         <td></td>
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