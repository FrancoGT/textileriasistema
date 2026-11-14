<!DOCTYPE html>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/> 
<html>
<head>
   <link href="CSS/Sixavi.css" media="screen" rel="stylesheet" type="text/css" />
   <title>TEXTILERIA</title>
   <script src="CSS/Libreria.js" language="JavaScript"></script>
</head>
<body>
<form action="ERP1090.php#no-back" method="post">
   <div class="wrapper">
   <div id="MH">
           <ul>
                <li style="margin-left: 50px;"><h1>Mantenimiento de Almacenes</h1></li>
				<li style="float:right;margin-right: 100px;"><a onclick="self.location = 'index.php'"> Cerrar Sesión</a></li>
           </ul>
   </div>
   <div id="topnavbar"></div>
   <div id="navigation">
   <div id="navcontainer">
   {if $scBehavior eq "0"}
      <table>
         <tr>
         <td>Almacenes:</td>
         <td>
            <select name="pcCodAlm">
            {foreach from = $saCodAlm item = i}
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
         {if empty($scCodAlm)}
            <td></td>
         {else}
            <td><font color=blue>{$scCodAlm}</font><input type="hidden" maxlength="6" name="pcCodAlm" value="{$scCodAlm}"/></td>
         {/if}
         </tr><tr>
         <td>Nombre:</td>
         <td><input type="text" maxlength="90" name="pcNomAlm" value="{$scNomAlm}" size="60"style="text-transform:uppercase;"/></td>
         </tr><tr>
         <td>Dirección:</td>
         <td><input type="text" maxlength="90" name="pcDirec" value="{$scDirec}" size="60"style="text-transform:uppercase;"/></td>
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
         <td>Tipo de Almacen:</td>
         <td>
            <select name="pcTipo">
            {foreach from = $saTipo item = i}
               <option value="{$i[0]}" {if $i[0] eq $scTipo} selected {/if}>{$i[1]}</option>
            {/foreach}
            </select>
         </td>
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
      <table style="font-size:11px">
         <tr>
         <td>Nombre:</td>
         <td><input type="text" maxlength="90" name="pcNomAlm" value="{$scNomAlm}" size="60" style="text-transform:uppercase;"/></td>
         </tr><tr>
         <td>Dirección:</td>
         <td><input type="text" maxlength="90" name="pcDirec" value="{$scDirec}" size="60" style="text-transform:uppercase;"/></td>
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
		 <tr>
         <td>Tipo de Almacen:</td>
         <td>
            <select name="pcTipo">
            {foreach from = $saTipo item = i}
               <option value="{$i[0]}" {if $i[0] eq $scTipo} selected {/if}>{$i[1]}</option>
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