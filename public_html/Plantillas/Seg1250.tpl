<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<html>
<head>
   <script language=JavaScript>
      window.onload = function() {
         document.getElementById("pcCodAfj").focus();
      }      
      
      function ValidaSoloNumeros() {
         if ((event.keyCode < 48) || (event.keyCode > 57))
            event.returnValue = false;
      }
   </script>
   <title>Asignación Fecha de Uso - Activo Fijo</title>
   <link href="CSS/Sixavi.css" media="screen" rel="stylesheet" type="text/css" />
</head>
<body>
<form method = "post" action = "Seg1250.php">
	<div class="wrapper">
	   <div id="header">
	   <table>
		  <tr>
		  <td><img src="Softia.png" width="46" height="38" /></td>
		  <td><p class="titulo">Generar Mantenimiento - Puente Curso-Norma</p></td>
		  </tr>
	   </table>
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
				 <td><input type="button" value="Salir" onclick="self.location = 'Mnu0000.php'"/></td>
				 </tr>
			  </table>
		   {elseif $scBehavior eq '1'}
			  <table>
				 <tr>              
				 <td>Código del Curso:</td>
				 <td><input maxlength="6" type="text"  name="pcCodCur" value='{$scCodCur}' disabled/></td>
				 </tr><tr>
				 <td>Descripción:</td>
				 <td><input type="text"  name="pcDescri" value='{$scDescri}' disabled/></td>
				 </tr>
			  </table>   
			  <table border = 1>
				 <caption>Normas Contenido del Curso</caption>
				 <tr>
				 <th>Id</th>
				 <th>Norma</th>
				 <th>Datos</th>
				 <th>Eliminar</th>
				 </tr>
				 {foreach from = $saIndex item = i}               
					<tr>
					<td>{$i[0]}<input type = "hidden" name = "paIndex[]" value="{$i[0]}"/></td>
					<td>
					   <select name = "paCodNor[]">
						  {foreach from = $saCodNor item = j    }
							 <option value = "{$j[0]}" {if $j[0] == $i[1]} selected {/if}> {$j[1]}</option>
						  {/foreach}
					   </select>
					</td>
					<td><input type = "text" maxlength = "50" name = "paDescri[]"  value="{$i[2]}"/></td>
					<td><input type = "checkbox" name = "paCheck[]" value = "{$i[0]}"/></td>
					</tr>
				 {/foreach}
			  </table>
			  <table>
				 <tr>
				 <td><input type="submit" name="Nuevo" value="Nuevo" /></td>
				 <td><input type="submit" name="Grabar" value="Grabar" /></td>
				 <td><input type="submit" name="Eliminar" value="Eliminar" /></td>
				 <td><input type="button" value="Salir" onclick="self.location = 'Mnu0000.php'" /></td>
				 </tr>
			  </table> 
		   {elseif $scBehavior eq '2'}
			  <table border=2>
				 {foreach from = $saCodAfj item = i}               
					<tr>
					   <td>{$i['0']}</td><td>{$i['1']}</td>
					   <td><input type = "radio" name = "pcCodAfj" value = "{$i['0']}"/></td>
					</tr>
				 {/foreach}
			  </table>
			  <table>
				 <tr>
				 <td><input type="submit" name="Aceptar" value="Aceptar" /></td>
				 <td><input type="button" value="Salir" onclick="self.location = 'Mnu0000.php'" /></td>
				 </tr>
			  </table>   
		   {elseif $scBehavior eq '3'}
			  <table border=2>
				 {foreach from = $saCodAfj item = i}               
					<tr>
					   <td>{$i['0']}</td><td>{$i['1']}</td>
					   <td><input type = "radio" name = "pcCodAfj" value = "{$i['0']}"/></td>
					</tr>
				 {/foreach}
			  </table>
			  <table>
				 <tr>
				 <td><input type="submit" name="Aceptar" value="Aceptar" /></td>
				 <td><input type="button" value="Salir" onclick="self.location = 'Mnu0000.php'" /></td>
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
