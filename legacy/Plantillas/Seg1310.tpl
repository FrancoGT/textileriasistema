<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<html>
<head>
   <script language=JavaScript>
      window.onload = function() {
         document.getElementById("pcCodAfj").focus();
      }      
   </script>
   <title>Asignación Fecha de Uso - Activo Fijo</title>
   <link href="CSS/Sixavi.css" media="screen" rel="stylesheet" type="text/css" />
</head>
<body>
<form method = "post" action = "Seg1310.php">
	<div class="wrapper">
	   <div id="header">
	   <table>
		  <tr>
		  <td><img src="CSS/Softia.png" width="46" height="38" /></td>
		  <td><p class="titulo">Registro y Mantenimiento Licencias</p></td>
		  </tr>
	   </table>
	   </div>
	   <div id="topnavbar"></div>
       <div id="navigation">
		   <div id="navcontainer">
		   {if $scBehavior eq '0'}
			  <fieldset style="width:600px" align="center">
			  <legend><font face="calibri" size=5 color=blue>Licencias</font></legend>
			  <div style="overflow:scroll; height:400px; width:600px; overflow:auto; border: 1px">
			  <table border = -1 width = 500>
				 <tr>
				 <th width='12'>Id</th>
				 <th >Código</th>
				 <th >Descripción</th>
				 <th >Estado</th>
				 <th ><img src="CSS/anular.bmp" width="20" height="20"></th>
				 </tr>
				 {foreach from = $saData item = i}               
					<tr>
					<td align = 'center'>{$i[0]}<input type = "hidden" name = "paData[{$i[0]}][0]" value="{$i[0]}"/></td>
					<td align = 'center'><input type = "text" maxlength = "3" name = "paData[{$i[0]}][1]" size='3' value="{$i[1]}"/></td>
					<td align = 'center'><input type = "text" maxlength = "50" name = "paData[{$i[0]}][2]" size='50' value="{$i[2]}"/></td>
					<td>
					   <select style = 'width: 100px;' name = "paData[{$i[0]}][3]">
						  {foreach from = $saEstado item = j}
							 <option value = "{$j[0]}" {if $j[0] == $i[3]} selected {/if}> {$j[1]}</option>
						  {/foreach}
					   </select>
					</td>
					<td align = 'center'><input type = "checkbox" name = "paCheck[]" value = "{$i[0]}"/></td>
					</tr>
				 {/foreach}
			  </table>
			  </div>
			  </fieldset>
			  <table>
				 <tr>
				 <td><input type="submit" name="Nuevo" value="Nuevo" /></td>
				 <td><input type="submit" name="Grabar" value="Grabar" /></td>
				 <td><input type="submit" name="Eliminar" value="Eliminar" /></td>
				 <td><input type="button" value="Salir" onclick="self.location = 'Mnu0000.php'" /></td>
				 </tr>
			  </table> 
		   {elseif $scBehavior eq '1'}
			  
		   {elseif $scBehavior eq '2'}
				
		   {elseif $scBehavior eq '3'}
				  
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
