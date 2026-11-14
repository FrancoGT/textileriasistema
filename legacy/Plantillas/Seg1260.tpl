<!DOCTYPE html>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/> 
<html>
<head>
   <link href="CSS/Sixavi.css" media="screen" rel="stylesheet" type="text/css" />
   <title>Seguridad Operativa</title>
   <script src="CSS/Libreria.js" language="JavaScript"></script>
</head>
<body>
<form action="Seg1260.php#no-back" method="post">
   <div class="wrapper">
   <div id="MH">
           <ul>
                <li style="margin-left: 50px;"><h1>Mantenimiento de Personal</h1></li>
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
         <td><input type="submit" name="Boton1" value="Aplicar" /></td>
         <td><input type="submit" name="Boton1" value="Nuevo" /></td>
         <td><input type="submit" name="Boton1" value="Salir" /></td>
      </table>
   {elseif $scBehavior eq "1"}
      <table style="font-size:11px">
         <tr>
         <td>Código:</td>
         {if empty($scCodPer)}
            <td></td>
         {else}
            <td><font color=blue>{$scCodPer}</font><input type="hidden" maxlength="6" name="pcCodPer" value="{$scCodPer}"/></td>
         {/if}
         </tr><tr>
         <td>Nombre:</td>
         <td><input type="text" maxlength="90" name="pcNombre" value="{$scNombre}" size="60" onKeyPress="toUpper(this)" onChange="toUpper(this)"/></td>
         </tr><tr>
         <td>Dirección:</td>
         <td><input type="text" maxlength="90" name="pcDirecc" value="{$scDirecc}" size="60" onKeyPress="toUpper(this)" onChange="toUpper(this)"/></td>
         </tr><tr>
         <td>E-Mail:</td>
         <td><input type="text" maxlength="90" name="pcEmail" value="{$scEmail}" size="60"/></td>
         </tr><tr>    
         <td>Nro.DNI:</td>
         <td><input type="text" maxlength="8" name="pcNroDni" value="{$scNroDni}" size="8" onKeyPress="return fNumbers(event)"/></td>
         </tr><tr>    
         <td>Sexo:</td>
         <td>
            <select name="pcSexo" >
            {foreach from = $saSexo item = i}
               <option value="{$i[0]}" {if $i[0] eq $scSexo} selected {/if}>{$i[1]} </option>
            {/foreach}
            </select>
         </td>
         </tr><tr>
         <td>Estado Civil:</td>
         <td>
            <select name="pcEstCiv" >
            {foreach from = $saEstCiv item = i}
               <option value="{$i[0]}" {if $i[0] eq $scEstCiv} selected {/if}>{$i[1]}</option>
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
         </tr><tr>
         <td>Empresa:</td>
         <td><font color=blue>{$scCodEmp}</font></td>
         </tr><tr>
         <td>Categoría:</td>
         <td><font color=blue>{$scCargo}</font></td>
         </tr><tr>
         <td>Cargo:</td>
         <td><font color=blue>{$scCarDis}</font></td>
         </tr><tr>
         <td>Sector:</td>
         <td><font color=blue>{$scSector}</font></td>
         </tr><tr>
         <td>Proyecto:</td>
         <td><font color=blue>{$scDesPry}</font></td>
         </tr>
      </table>
      <table>
         <tr>
         <td></td>
         <td><input type="submit" name="Boton2" value="Grabar"/></td>
         <td><input type="submit" name="Boton2" value="Cancelar"/></td>
         </tr>
      </table>
   {elseif $scBehavior eq "2"}
      <table>
         <tr>
         <td>Nombre:</td>
         <td><input type="text" maxlength="90" name="pcNombre" size="60" value="{$scNombre}"/></td>
         </tr><tr>
         <td>Direccion:</td>
         <td><input type="text" maxlength="90" name="pcDirecc" size="60" value="{$scDirecc}"/></td>
         </tr><tr>
         <td>E-Mail:</td>
         <td><input type="text" maxlength="90" name="pcEmail" value="{$scEmail}" size="60"/></td>
         </tr><tr>    
         <td>Nro.DNI:</td>
         <td><input type="text" maxlength="8" name="pcNroDni" value="{$scNroDni}" size="8"/></td>
         </tr><tr> 
         <td>Sexo:</td>
         <td>
            <select name="pcSexo">
            {foreach from = $saSexo item = i}
               <option value="{$i[0]}" {if $i[0] eq $scSexo} selected {/if}>{$i[1]}</option>
            {/foreach}
            </select>
         </td>
         </tr><tr> 
         <td>Estado Civil:</td>
         <td>
            <select name="pcEstCiv">
            {foreach from = $saEstCiv item = i}
               <option value="{$i[0]}" {if $i[0] eq $scEstCiv} selected {/if}>{$i[1]}</option>
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
      </table>
      <table>
         <tr>
         <td></td>
         <td><input type="submit" name="Boton2" value="Grabar"/></td>
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
