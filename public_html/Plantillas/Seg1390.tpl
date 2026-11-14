<!DOCTYPE html>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/> 
<html>
<head>
   <link href="CSS/Sixavi.css" media="screen" rel="stylesheet" type="text/css" />
   <title>Seguridad Operativa</title>
   <script src="CSS/Libreria.js" language="JavaScript"></script>
</head>
<body>
<form action="Seg1390.php#no-back" method="post">
   <div class="wrapper">
   <div id="MH">
           <ul>
                <li style="margin-left: 50px;"><h1>Mantenimiento de Usuarios</h1></li>
				<li style="float:right;margin-right: 100px;"><a onclick="self.location = 'index.php'"> Cerrar Sesión</a></li>
           </ul>
   </div>
   <div id="topnavbar"></div>
   <div id="navigation">
   <div id="navcontainer">
   {if $scBehavior eq "0"}
      <div style="overflow:scroll; height:500px; width:800px; overflow:auto; border: 1px">
      <table border=2 style="font-size:11px">
         <th align="center">Código</th>
         <th width="500" align="center">Nombre</th>
         <th width="100" align="center">Perfil</th>
         <th align="center"><img src="CSS/Check.jpg" width="25" height="25"></th>
         {foreach from = $saCodUsu item = i}               
            <tr>
            <td>{$i['0']}</td><td>{$i['1']}</td><td>{$i['2']}</td>
            <td><input type="radio" name="pcCodUsu" value="{$i['0']}"/></td>
            </tr>
         {/foreach}
      </table>
      </div>
      <table>
         <tr>
         <td><input type="submit" name="Boton1" value="Editar" /></td>
         <td><input type="submit" name="Boton1" value="Nuevo" /></td>
         <td><input type="submit" name="Boton1" value="Salir" /></td>
         </tr>
      </table>
   {elseif $scBehavior eq "1"}
      <fieldset>
      <table style="font-size:11px">
         <tr>
         <td>Código:</td>
         <td><font color=blue>{$scCodUsu}</font><input type="hidden" name="pcCodUsu" value="{$scCodUsu}"/></td>
         </tr><tr>
         <td>Nombre:</td>
         <td><font color=blue>{$scNombre}</font><input type="hidden" name="pcNombre" value="{$scNombre}"/></td>
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
         <td>Perfil:</td>
         <td>
            <select name="pcPerfil">
            {foreach from = $saPerfil item = i}
               <option value="{$i[0]}" {if $i[0] eq $scPerfil} selected {/if}>{$i[1]}</option>
            {/foreach}
            </select>
         </td>
         </tr><tr>
         <td>F.Expiración Clave:</td>
         <td><input type="text" maxlength="10" name="pdFecFin" value="{$sdFecFin}" size="12" onKeyPress="return fDate(event)"/></td>
         </tr>
      </table>
      </fieldset>
      <table>
         <tr>
         <td></td>
         <td><input type="submit" name="Boton2" value="Actualizar" /></td>
         <td><input type="submit" name="Boton2" value="Cancelar" /></td>
         </tr>
      </table>
   {elseif $scBehavior eq "2"}
      <table border=1 style="font-size:11px">
         <tr>
         <td>Personal:</td>
         <td>
            <select name="pcCodPer">
            {foreach from = $saCodPer item = i}
               <option value="{$i[0]}">{$i[1]}</option>
            {/foreach}
            </select>
         </td>
         </tr><tr> 
         <td>Perfil:</td>
         <td>
            <select name="pcPerfil">
            {foreach from = $saPerfil item = i}
               <option value="{$i[0]}" {if $i[0] eq $scPerfil} selected {/if}>{$i[1]}</option>
            {/foreach}
            </select>
         </td>
      </table>
      <table>
         <tr>
         <td></td>
         <td><input type="submit" name="Boton3" value="Grabar" /></td>
         <td><input type="submit" name="Boton3" value="Cancelar" /></td>
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
