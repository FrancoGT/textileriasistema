<!DOCTYPE html>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/> 
<html>
<head>
   <link href="CSS/Sixavi.css" media="screen" rel="stylesheet" type="text/css" />
   <title>Gestión de Seguridad</title>
</head>
<body>
<form action = "Seg8001.php" method = "post">
   <div class="wrapper">
   <div id="header">
   <table>
      <tr>
      <td><img src="CSS/Softia.png" width="46" height="38" /></td>
      <td><p class="titulo">Mantenimiento de Unidades</p></td>
      </tr>
   </table>
   </div>
   <div id="topnavbar"></div>
   <div id="navigation">
   <div id="navcontainer">
   {if $scBehavior eq "0"}
      <table>
         <tr>
         <td>Código de Unidad</td>
         <td><input type = "text" maxlength = "6" name = "pcCodUni" value = "{$scCodUni}"/></td>
         </tr>
      </table>
      <table>
         <td><input type = "submit" name = "Aplicar" value = "Aplicar" /></td>
         <td><input type = "submit" name = "Nuevo" value = "Nuevo" /></td>
         <td><input type = "submit" name = "Buscar" value = "Buscar" /></td>
         <td><input type = "button" value = "Salir" onclick="self.location = 'Mnu0000.php?Id=1000'" /></td>
      </table>
   {elseif $scBehavior eq "1"}
      <table>
         <tr>
         <td>Código:</td>
         <td>{$scCodUni}<input type = "hidden" maxlength = "6" name = "pcCodUni" value = "{$scCodUni}"/></td>         
         </tr><tr>
         <td>Descripción:</td>
         <td><input type = "text" maxlength = "90" name = "pcDescri" value = "{$scDescri}"  size="60" onblur="makeUppercase(this)" onKeyPress=" makeUppercase(this)" /></td>
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
         <td></td>
         <td><input type = "submit" name = "Grabar" value = "Grabar" /></td>
         <td><input type = "submit" name = "Cancelar" value = "Cancelar" /></td>
         </tr>
      </table>
   {elseif $scBehavior eq "2"}
      <table>
         <tr>
         <td>Código</td>
         <td><input type = "text" maxlength = "4" name = "pcCodUsu" size="8" value = "{$scCodUsu}" /></td>
         </tr><tr>        
         <td>Personal:</td>
         <td>
            <select name="pcCodPer">
            {foreach from = $saCodPer item = i}
               <option value = "{$i[0]}" {if $i[0] eq $scCodPer} selected {/if}>{$i[1]}</option>
            {/foreach}
            </select>
         </td>
         </tr><tr> 
      </table>
      <table>
         <tr>
         <td></td>
         <td><input type = "submit" name = "Grabar" value = "Grabar" /></td>
         <td><input type = "submit" name = "Cancelar" value = "Cancelar" /></td>
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
