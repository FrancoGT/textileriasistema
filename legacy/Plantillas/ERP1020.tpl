<!DOCTYPE html>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<html>
<head>
   <link href="CSS/Sixavi.css" media="screen" rel="stylesheet" type="text/css"/>
   <title>TEXTILERIA</title>
</head>
<body>
<form action="ERP1020.php#no-back" method="post">
   {if isset($maintenanceToken)}<input type="hidden" name="_token" value="{$maintenanceToken}" />{/if}
   <div class="wrapper">
   <div id="MH">
      <ul>
      <li style="margin-left: 50px;"><h1>Mantenimiento de Perfiles de Usuarios</h1></li>
      <li style="float:right;margin-right: 100px;"><a onclick="self.location = 'index.php'"> Cerrar Sesión</a></li>
      </ul>
   </div>
   <div id="topnavbar"></div>
   <div id="navigation">
   <div id="navcontainer">
   {if isset($maintenanceMessage) && $maintenanceMessage ne ''}<p role="status">{$maintenanceMessage}</p>{/if}
   {if $scBehavior eq "0"}   
      <fieldset>
      <table style="font-size:11px">
         <tr>
         <td>Perfil:</td>
         <td>
            <select name="pcPerfil">
            {foreach from = $saPerfil item = i}
               <option value="{$i[0]}" {if $i[0] eq $scPerfil} selected {/if}>{$i[1]}</option>
            {/foreach}
            </select>
         </td>
         </tr>
      </table>
      </fieldset>
      <table>
         <tr>
         <td></td>
         <td><input type="submit" name="Boton1" value="Aplicar"/></td>
         <td><input type="submit" name="Boton1" value="Salir"/></td>
         </tr>
      </table>
   {elseif $scBehavior eq "1"}   
      <table style="font-size:11px">
         <tr>
         <td>Perfil:</td>
         <td><font color=blue>{$pcDescri}</font><input type="hidden" name="pcDescrir" value="{$pcDescri}"/></td>
         </tr>
      </table>
      <div style="overflow:scroll; height:400px; width:800px; overflow:auto; border:1px solid">
      <table border=2 style="font-size:11px">
         <th bgcolor="#F29818" width="100" align="center">Código</th>
         <th bgcolor="#F29818" width="400" align="center">Opción</th>
         <th bgcolor="#F29818" width="100" align="center">Estado</th>
         <th bgcolor="#F29818" align="center"><img src="CSS/Check.jpg" width="25" height="25"></th>
         {foreach from = $saPerfil item = i}               
            <tr>
            <td align="center">{$i['0']}</td><td>{$i['1']}</td><td align="center">{$i['2']}</td>
            <td align="center"><input type="radio" name="pcCodOpc" value="{$i['0']}"/></td>
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
         <tr><td><input type="submit" name="Boton2" value="Cancelar"/></td></tr>
      </table>
   {elseif $scBehavior eq "2"}
      <fieldset>
      <table style="font-size:11px">
         <tr>
         <td>Opciones:</td>
         <td>
            <select name="pcCodOpc">
            {foreach from = $saCodOpc item = i}
               <option value="{$i[0]}">{$i[1]}</option>
            {/foreach}
            </select>
         </td>
         </tr><tr>
         <td>Estado:</td>
         <td>
            <select name="pcEstado">
            {foreach from = $saEstado item = i}
               <option value="{$i[0]}" {if $i[0] eq $scEstado} selected {/if}>{$i[1]}</option>
            {/foreach}
            </select>
         </td>
         </tr>
      </table>
      </fieldset>
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