<!DOCTYPE html>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/> 
<html>
<head>
   <link href="CSS/Sixavi.css" media="screen" rel="stylesheet" type="text/css" />
   <script src="CSS/Libreria.js" language="JavaScript"></script>
   <title>Seguridad Operativa</title>
</head>
<body>
<form action="Seg1210.php" method="post">
   <div class="wrapper">
   
   <div id="MH">
           <ul>
                <li style="margin-left: 50px;"><h1>Mantenimiento de Proyectos</h1></li>
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
            <td align="center"><input type="radio" name="paCheck[]" value="{$i[0]}"/></td>
            </tr>
         {/foreach}
      </table>  
      <table>
         <td><input type="submit" name="Aplicar" value="Aplicar" /></td>
         <td><input type="submit" name="Nuevo" value="Nuevo" /></td>
         <td><input type="button" value="Salir" onclick="self.location='Mnu0000.php?Id=1000'" /></td>
      </table>
   {elseif $scBehavior eq "1"}
      <table>
         <tr>
         <td>Código:</td>
         <td><p class="datos">{$scCodPry}</p><input type="hidden" name="pcCodPry" value="{$scCodPry}"/></td>
         </tr><tr>
         <td>Descripción:</td>
         <td><input type="text" maxlength="70" size ="70" name="pcDescri" value="{$scDescri}" onKeyPress="toUpper(this)" onChange="toUpper(this)" autofocus/></td>
         </tr><tr>
         <td>Fecha Inicio:</td>
         <td><input type="text" maxlength="10" name="pdFecIni" value="{$sdFecIni}" placeholder="aaaa-mm-dd" onKeyPress="return fDate(event)"/></td>
         </tr><tr>
         <td>Estado:</td>
         <td>
            <select name="pcEstado" style="background-color:#FFFFA0">
            {foreach from = $saEstado item = i}
               <option value="{$i[0]}" {if $i[0] eq $scEstado} selected {/if}>{$i[1]}</option>
            {/foreach}
            </select>
         </td>
         </tr><tr>
         <td>Encargado:</td>
         <td>
            <select name="pcCodPer" style="background-color:#FFFFA0">
            {foreach from = $saCodPer item = i}
               <option value="{$i[0]}" {if $i[0] eq $scCodPer} selected {/if}>{$i[1]}</option>
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
         <td><input type="submit" name="Grabar" value="Grabar" /></td>
         <td><input type="submit" name="Cancelar" value="Cancelar" /></td>
         </tr>
      </table>
   {elseif $scBehavior eq "2"}
      <table>
         <tr>
         <td>Código:</td>
         <td><input type="text" maxlength="5" name="pcCodPry" value="" onKeyPress="toUpper(this)" onChange="toUpper(this)" autofocus/></td>
         </tr><tr>
         <td>Descripción:</td>
         <td><input type="text" maxlength="70" name="pcDescri" size="70"  value="{$scDescri}" onKeyPress="toUpper(this)" onChange="toUpper(this)"/></td>
         </tr><tr>
         <td>Fecha Inicio:</td>
         <td><input type="text" maxlength="10" name="pdFecIni" size="12" value="{$sdFecIni}" placeholder="aaaa-mm-dd" onKeyPress="return fDate(event)"/></td>
         </tr><tr>
         <td>Estado:</td>
         <td>
            <select name="pcEstado" style="background-color:#FFFFA0">
            {foreach from = $saEstado item = i}
               <option value="{$i[0]}" {if $i[0] eq $scEstado} selected {/if}>{$i[1]}</option>
            {/foreach}
            </select>
         </td>
         </tr><tr>
         <td>Encargado:</td>
         <td>
            <select name="pcCodPer" style="background-color:#FFFFA0">
            {foreach from = $saCodPer item = i}
               <option value="{$i[0]}" {if $i[0] eq $scCodPer} selected {/if}>{$i[1]}</option>
            {/foreach}
            </select>
         </td>
         </tr><tr>
         <td>Datos varios:</td>
         <td><textarea name="pmDatos" rows="10" cols="50">{$smDatos}</textarea></td>
      </table>
      <table>
         <tr>
         <td></td>
         <td><input type="submit" name="Grabar" value="Grabar" /></td>
         <td><input type="submit" name="Cancelar" value="Cancelar" /></td>
         </tr>
      </table>
   {/if}
   </div>
   </div>
   <div id="content">
   </div>
   <div id="footer">
   </div>
   </div>   
</form>
</body>
</html>
