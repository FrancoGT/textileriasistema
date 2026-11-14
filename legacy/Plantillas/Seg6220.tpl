<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<html>
<head>
   <title>Seguridad Operativa</title>
   <link href="CSS/Sixavi.css" media="screen" rel="stylesheet" type="text/css"/>
   <script src="CSS/Libreria.js" language="JavaScript"></script>
</head>
<body>
<form method="post" action="Seg6220.php#no-back">
   <div class="wrapper">
   <div id="MH">
      <ul>
      <li style="margin-left: 50px;"><h1>Mantenimiento de Procesos de Proyectos</h1></li>
      <li style="float:right;margin-right: 100px;"><a onclick="self.location = 'index.php'"> Cerrar Sesión</a></li>
      </ul>
   </div>
   <div id="topnavbar"></div>
   <div id="navigation">
   <div id="navcontainer">
   {if $scBehavior eq '0'}
      <table>
         <tr>              
         <td>Proyecto:</td>
         <td>
            <select name="pcCodPry">
            {foreach from = $saCodPry item = i}
               <option value="{$i[0]}" > {$i[1]}</option>
            {/foreach}
            </select>
         </td></tr>
      </table>
      <table>
         <tr>
         <td><input type="submit" name="Boton1" value="Aplicar"/></td>
         <td><input type="submit" name="Boton1" value="Salir"/></td>
         </tr>
      </table>
   {elseif $scBehavior eq '1'}
      <table style="font-size:11px">
         <tr>              
         <td>Proyecto:</td>
         <td><font color=blue>{$scDesPry}</font></td>
         </tr>
      </table>   
      <table border=1 width=400 style="font-size:11px">
         <tr>
         <th width=350>Descripción</th>
         <th width=50>Eliminar</th>
         </tr>
         {foreach from = $saData item = i}               
            <tr>
            <td><input type="hidden" name="paData[{$i[0]}][0]" value="{$i[0]}"/>
                <input type="hidden" name="paData[{$i[0]}][1]" value="{$i[1]}"/>
                <input type="hidden" name="paData[{$i[0]}][3]" value="{$i[3]}"/>
                <input type="text" name="paData[{$i[0]}][2]" value="{$i[2]}" maxlength=100 size=100 style="text-transform: uppercase"/></td>
            <td align="center"><input type="checkbox" name="paCheck[]" value="{$i[0]}" {if $i[3] eq 0} disabled {/if}/></td>
            </tr>
         {/foreach}
      </table>
      <table>
         <tr>
         <td><input type="submit" name="Boton2" value="Nuevo"/></td>
         <td><input type="submit" name="Boton2" value="Grabar"/></td>
         <td><input type="submit" name="Boton2" value="Eliminar"/></td>
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
