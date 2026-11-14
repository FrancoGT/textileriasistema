<!DOCTYPE html>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/> 
<html>
<head>
   <link href="CSS/Sixavi.css" media="screen" rel="stylesheet" type="text/css" />
   <title>Gestión de Seguridad</title>
</head>
<body>
<form action = "Seg6120.php" method = "post">
   <div class="wrapper">
   <div id="header">
   <table>
      <tr>
      <td><img src="CSS/Softia.png" width="46" height="38" /></td>
      <td><p class="titulo">Mantenimiento de Perfiles de Usuarios</p></td>
      </tr>
   </table>
   </div>
   <div id="topnavbar"></div>
   <div id="navigation">
   <div id="navcontainer">   
   {if $scBehavior eq "0"}   
      <table>
         <tr>        
         <td>Perfil:</td>
         <td>
            <select name="pcPerfil">
            {foreach from = $saPerfil item = i}
               <option value = "{$i[0]}" {if $i[0] eq $scPerfil} selected {/if}>{$i[1]}</option>
            {/foreach}
            </select>
         </td>
         </tr>
      </table>
      <table>
         <tr>
         <td></td>         
         <td><input type = "submit" name = "Boton1" value = "Aplicar" /></td>
         <td><input type = "submit" name = "Boton1" value = "Salir" /></td>
         </tr>
      </table>
   {elseif $scBehavior eq "1"}   
      <table>
         <tr>        
            <td>Perfil:</td>
            <td>{$pcDescri}<input type="hidden" name="pcDescrir" value="{$pcDescri}"/></td>
         </tr>
      </table>
      <div style="overflow:scroll; height:500px; width:800px; overflow:auto; border: 1px">
      <table border = 2 style = "font-size:11px">
         <th width="400" align="center">Código</th>
         <th width="100" align="center">Opción</th>
         <th width="100" align="center">Estado</th>
         <th align="center"><img src = "CSS/Check.jpg" width="25" height="25"></th>         
         {foreach from = $saPerfil item = i}               
            <tr>
            <td>{$i['0']}</td><td>{$i['1']}</td><td>{$i['2']}</td>
            <td><input type = "radio" name = "pcCodOpc" value = "{$i['0']}"/></td>            
            </tr>
         {/foreach}
      </table>
      </div>
      <table>
         <tr>
         <td><input type="submit" name="Boton2" value="Nuevo" /></td>         
         <td><input type="submit" name="Boton2" value="Eliminar" /></td>         
         <td><input type="submit" name="Boton2" value="Cancelar" /></td>         
         </tr>
      </table>        
   {elseif $scBehavior eq "2"}
    <table>
         <tr>        
         <td>Opciones:</td>
         <td>
            <select name="pcCodOpc">
            {foreach from = $saCodOpc item = i}
               <option value = "{$i[0]}" {if $i[0] eq $scCodOpc} selected {/if}>{$i[1]}</option>
            {/foreach}
            </select>
         </td>
         </tr><tr>                             
         <td>Estado:</td>
         <td>
            <select name="pcEstado">
            {foreach from = $saEstado item = i}
               <option value = "{$i[0]}" {if $i[0] eq $scEstado} selected {/if}>{$i[1]}</option>
            {/foreach}
            </select>
         </td>
         </tr>
      </table>
      <table>
         <tr>         
         <td><input type = "submit" name = "Boton3" value = "Grabar" /></td>
         <td><input type = "submit" name = "Boton3" value = "Cancelar" /></td>
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
