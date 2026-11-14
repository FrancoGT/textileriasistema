<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<html>
<head>
   <link href="CSS/Sixavi.css" media="screen" rel="stylesheet" type="text/css" />
   <script src="CSS/Libreria.js" language="JavaScript"></script>
   <title>Seguridad Operativa</title>
</head>
<body>
<form method="post" action="Seg2350.php#no-back">
   <div class="wrapper">
   <div id="MH">
      <ul>
      <li style="margin-left: 50px;"><h1>Equipo de Investigación</h1></li>
      <li style="float:right;margin-right: 100px;"><a onclick="self.location = 'index.php'"> Cerrar Sesión</a></li>
      </ul>
   </div>
   <div id="topnavbar"></div>
   <div id="navigation">
   <div id="navcontainer">
   {if $scBehavior eq '0'}
      <fieldset style="width:500px" align="center">
      <table border=0>
         <tr>
         <td>Número Incidente</td>
         <td><input type="text" name="pcNroInc" value="" size='6'/></td>
         </tr>
      </table>  
      </fieldset>
      <table>
         <tr>
         <td><input type="submit" name="Boton1" value="Aplicar"/></td>
         <td><input type="submit" name="Boton1" value="Buscar"/></td>
         <td><input type="submit" name="Boton1" value="Salir"/></td>
         </tr>
      </table>
   {elseif $scBehavior eq '1'}
      <fieldset style="width:1000px" align="center">
      <table border=0 cellspacing="0" cellpadding="0" style="font-size:11px">
         <tr>
         <td>Número de Incidente: [{$scNroInc}]<input type="hidden" name="pcNroInc" value="{$scNroInc}" size='6'/></td>
         </tr><tr>
		 <td>
         <textarea class="memo" name="pmDatos" style="background:#FFFFFF" disabled>{$smDatos}</textarea>
         </td>
      </table>
      </fieldset>
      <fieldset style="width:1000px" align="center">
      <legend align='left'><font face="calibri" size=3 color=blue>Personal Involucrado</font></legend>
      <table border=2 width =600 style="font-size:11px">
         <tr>
         <th width=30 align="center">Id</th>
         <th width=400 align="center">Nombre</th>
         <th width=30 align="center">Líder</th>
         <th width=100 align="center">Estado</th>
         <th width=40 align="center"><img src="CSS/anular.bmp" /></th>
         </tr>
         {foreach from = $saData item = i}
            <tr>
            <td align='center'>{$i[0]}<input type="hidden" name="paData[{$i[0]}][0]" value="{$i[0]}"/><input type="hidden" name="paData[{$i[0]}][1]" value="{$i[1]}"/></td>
            <td>{$i[2]}<input type="hidden" name="paData[{$i[0]}][1]" value="{$i[1]}"/>
                       <input type="hidden" name="paData[{$i[0]}][2]" value="{$i[2]}"/></td>
            <td align='center'><input type="text" name="paData[{$i[0]}][3]" value="{$i[3]}" maxlength="1" size="1" OnKeyup='return cUpper(this)'/></td>
            <td>
               <select name="paData[{$i[0]}][4]">
               {foreach from = $saEstado item = j}
                  <option value="{$j[0]}" {if $i[4] eq $j[0]} selected {/if}> {$j[1]} </option>
               {/foreach}
               </select>
            </td>
            <td align='center'><input type="checkbox" name="paCheck[]" value="{$i[0]}"/></td>
         {/foreach}
      </table>
      <table border=2 style="font-size:11px">
         <td>
            <select style="font-size:11px" name="pcCodPer" align="rigth">
            {foreach from = $saCodPer item = j}
               <option value="{$j[0]}"> {$j[1]} </option>
            {/foreach}
            </select>
         </td>
      </table>
      <table>
         <tr>
         <td><input type="submit" name="Boton2" value="Nuevo" class="boton"/></td>
         <td><input type="submit" name="Boton2" value="Eliminar" class="boton"/></td>
         </tr>
      </table>
      </fieldset>
      <table>
         <tr>
         <td><input type="submit" name="Boton4" value="Grabar"/></td>
         <td><input type="submit" name="Boton4" value="Cancelar"/></td>
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