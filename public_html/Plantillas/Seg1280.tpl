<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<html>
<head>
   <script language=JavaScript>
      function ValidaSoloNumeros() {
         if ((event.keyCode < 48) || (event.keyCode > 57))
            event.returnValue = false;
      }
   </script>
   <title>Registro de Cursos por Empleado</title>
   <link href="CSS/Sixavi.css" media="screen" rel="stylesheet" type="text/css" />
</head>
<body>
<form method = "post" action = "Seg1280.php#no-back">
   <div class="wrapper">
   <div id="MH">
      <ul>
      <li style="margin-left: 50px;"><h1>Registro de Cursos por Empleado</h1></li>
      <li style="float:right;margin-right: 100px;"><a onclick="self.location = 'index.php'"> Cerrar Sesión</a></li>
      </ul>
   </div>
   <div id="topnavbar"></div>
   <div id="navigation">
   <div id="navcontainer">
   {if $scBehavior eq '0'}
      <table>
         <tr>              
         <td>Curso:</td>
         <td>
            <select name = "pcCodCur">
               {foreach from = $saCodCur item = i}
                  <option value = "{$i[0]}" > {$i[1]}</option>
               {/foreach}
            </select>
         </td>
         </tr>
      </table>
      <table>
         <tr>
         <td><input type="submit" name="Aplicar" value="Aplicar"/></td>
         <td><input type="button" value="Salir" onclick="self.location = 'Mnu0000.php?Id=2000'"/></td>
         </tr>
      </table>
   {elseif $scBehavior eq '1'}
      <table style="font-size:11px">
         <tr>              
         <td>Curso:</td>
         <td><p class="datos">{$scCodCur} - {$scDescri}</p></td>
         </tr><tr>
         <td>Fecha:</td>
         <td><p class="datos">{$sdFecha}</p></td>
         </tr>
      </table>
      <fieldset style="width:600px" align="center">
      <legend><font face = "calibri" size = 4 color = blue>Personal en el Curso</font></legend>
      <table border = 1 cellspacing="0" cellpadding="0" style="font-size:11px">
         <tr>
         <th width="40px" align='center'>Id</th>
         <th width="350px">Empleado</th>
         <th width="50px" align='center'>Nota</th>
         <th width='40px' align='center'><img src="CSS/anular.bmp"/></th>
         </tr>
         {foreach from = $saData item = i}
            <tr>
            <td align='center'>{$i[0]}<input type = "hidden" name = "paData[{$i[0]}][0]" value="{$i[0]}"/></td>
            <td align='center'>
               <select style="width: 345px" name = "paData[{$i[0]}][1]">
                  {foreach from = $saCodPer item = j}
                     <option value = "{$j[0]}" {if $i[1] eq $j[0]} selected {/if}> {$j[1]}</option>
                  {/foreach}
               </select>
            </td>
            <td align='center'><input type = "text" maxlength = "3" name = "paData[{$i[0]}][2]"  value="{$i[2]}" style="text-align: right;" size="4"/></td>
            <td align='center'><input type = "checkbox" name = "paCheck[]" value = "{$i[0]}"/></td>
            </tr>
         {/foreach}
      </table>
      <table>
         <tr>
         <td><input type="submit" name="Nuevo" value="Nuevo" class="boton"/></td>
         <td><input type="submit" name="Eliminar" value="Eliminar" class="boton"/></td>
         </tr>
      </table>
      </fieldset>
      <table>
         <tr>
         <td><input type="submit" name="Grabar" value="Grabar" /></td>
         <td><input type="submit" name="Cancelar" value="Cancelar" /></td>
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

