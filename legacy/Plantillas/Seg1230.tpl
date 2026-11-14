<!DOCTYPE html>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/> 
<html>
<head>
   <link href="CSS/Sixavi.css" media="screen" rel="stylesheet" type="text/css" />
   <title>Seguridad Operativa</title>
   {if $scBehavior != "0"}
      <script>
         function FuncionInit() {
            var objDiv = document.getElementById("div1");
            objDiv.scrollTop = objDiv.scrollHeight;
         }
   
         window.onload = function() {
            var pos = window.name || 0;
            window.scrollTo(0, pos);
            FuncionInit();
         }
     
         window.onunload = function() {
            window.name = self.pageYOffset || (document.documentElement.scrollTop + document.body.scrollTop);
         }  
      </script>
   {/if}
</head>
<body>
<form action="Seg1230.php#no-back" method="post">
   <div class="wrapper">
   <div id="MH">
      <ul>
      <li style="margin-left: 50px;"><h1>Mantenimiento de Cursos</h1></li>
      <li style="float:right;margin-right: 100px;"><a onclick="self.location = 'index.php'"> Cerrar Sesión</a></li>
      </ul>
   </div>
   <div id="topnavbar"></div>
   <div id="navigation">
   <div id="navcontainer">
   {if $scBehavior eq "0"}
      <table>
         <tr>
         <td>Código del Curso:</td>
         <td><input type="text" maxlength="6" name="pcCodCur" value="{$scCodCur}"/></td>
         </tr>
      </table>
      <table>
         <td><input type="submit" name="Boton1" value="Aplicar" /></td>
         <td><input type="submit" name="Boton1" value="Nuevo" /></td>
         <td><input type="submit" name="Boton1" value="Buscar" /></td>
         <td><input type="submit" name="Boton1" value="Salir" /></td>
      </table>
   {elseif $scBehavior eq "1"}
      <fieldset style="width:1160px" align="center">
      <fieldset style="width:800px" align="center">
      <legend align="left"><font face="calibri" size=3 color=blue>Datos del Curso</font></legend>
      <table style="font-size:11px">
         <tr>
         <td>Código:</td>
         <td>Descripción:</td>
         <td>Estado:</td>
         <td>Tipo:</td>
         </tr><tr>
         <td><input type="text" maxlength="6" name="pcCodCur" value="{$scCodCur}" disabled/></td>
         <td><input type="text" maxlength="90" size='50' name="pcDescri" value="{$scDescri}" onblur="makeUppercase(this)" onKeyPress=" makeUppercase(this)"/></td>
         <td>
            <select name="pcEstado">
            {foreach from=$saEstado item=i}
               <option value="{$i[0]}" {if $i[0] eq $scEstado} selected {/if}>{$i[1]}</option>
            {/foreach}
            </select>
         </td>
         <td>
            <select name="pcTipo">
            {foreach from=$saTipo item=i}
               <option value="{$i[0]}" {if $i[0] eq $scTipo} selected {/if}>{$i[1]}</option>
            {/foreach}
            </select>
         </td>
         </tr><tr>
         <td>Fecha:</td>
         <td>Expositor:</td>
         <td>Duración:</td>
         </tr><tr>
         <td><input type="text" maxlength="10" name="pdFecha" size="12" value="{$sdFecha}" placeholder="aaaa-mm-dd"/></td>
         <td><input type="text" maxlength="90" name="pcNomDoc" size="60" value="{$scNomDoc}"/></td>
         <td><input type="text" maxlength="10" name="pcTiempo" size="10" value="{$scTiempo}" placeholder="hh:mm"/></td>
         </tr>
      </table>
      </fieldset>
      <fieldset style="width:800px" align="center">
      <legend align="left"><font face="calibri" size=3 color=blue>Normas</font></legend>
      <div id="div1" style="overflow:scroll; height:120px; width:800px; overflow:auto; border:2px solid gray;">
      <table border=1 style="font-size:11px">
         <tr>
         <th style="width: 50px;" align='center'>Id</th>
         <th style="width: 300px;">Norma</th>
         <th style="width: 300px;">Datos</th>
         <th style="width: 50px;" align='center'><img src="CSS/anular.bmp" /></th>
         </tr>
         {foreach from=$saData item=i}
            <tr>
            <td align='center'>{$i[0]}<input type="hidden" name="paData[{$i[0]}][0]" value="{$i[0]}"/></td>
            <td>{$i[2]}<input type="hidden" name="paData[{$i[0]}][1]" value="{$i[1]}"/><input type="hidden" name="paData[{$i[0]}][2]" value="{$i[2]}"/></td>
            <td><input type="text" maxlength="200" size='80' name="paData[{$i[0]}][3]" value="{$i[3]}" {if substr($i[1], 0, 1) != 'Z'} disabled {/if}/></td>
            <td align='center'><input type="checkbox" name="paCheck[]" value="{$i[0]}"/></td>
            </tr>
         {/foreach}
         </td></tr>
      </table>
      </div>
      <table>
         <tr>
         <td align='center'>
            <select  width="200" style="width: 300px" name="pcCodNor">
            {foreach from=$saCodNor item=j}
               <option value="{$j[0]}"> {$j[1]} </option>
            {/foreach}
            </select>
         </td>
         </tr>
      </table>
      <table>
         <tr>
         <td><input type="submit" name="Boton2" value="Nuevo" class="boton"/></td>
         <td><input type="submit" name="Boton2" value="Eliminar" class="boton"/></td>
         </tr>
      </table>
      </fieldset>
      <fieldset style="width:800px" align="center">
      <legend align="left"><font face="calibri" size=3 color=blue>Datos Varios</font></legend>
      <table>
         <tr>
         <td><textarea name="pmDatos" rows="10" cols="80">{$smDatos}</textarea></td>
         </tr>
      </table>
      </fieldset>
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
