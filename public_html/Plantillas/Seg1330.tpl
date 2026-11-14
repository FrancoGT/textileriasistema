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
   <title>Mantenimiento Empresas- Areas</title>
   <link href="CSS/Sixavi.css" media="screen" rel="stylesheet" type="text/css" />
</head>
<body>
<form method = "post" action = "Seg1330.php">
	<div class="wrapper">
	   <div id="header">
	   <table>
		  <tr>
		  <td><img src="CSS/Softia.png" width="46" height="38" /></td>
		  <td><p class="titulo">Generar Mantenimiento - Puente Empresa - Area </p></td>
		  </tr>
	   </table>
	   </div>
	   <div id="topnavbar"></div>
       <div id="navigation">
		   <div id="navcontainer">
		   {if $scBehavior eq '0'}
			  <table>
				 <tr>              
				 <td>Empresa:</td>
				 <td>
					 <select name = "pcCodEmp">
						  {foreach from = $saCodEmp item = i}
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
				 <td>Código de la Empresa:</td>
				 <td><input maxlength="6" type="text"  name="pcCodEmp" value='{$scCodEmp}' disabled/></td>
				 </tr><tr>
				 <td>Descripción:</td>
				 <td><input type="text"  name="pcDescri" value='{$scDescri}' disabled/></td>
				 </tr>
			  </table>
			  <div style="overflow:scroll; height:400px; width:800px; overflow:auto; 	border: 1px solid #aaaaaa;" border=1>
			  <table border = 1>
				 <caption>Areas de la Empresa</caption>
				 <tr>
				 <th>Id</th>
				 <th>Area</th>
				 <th>Descripcion</th>
				 <th>Estado</th>
				 <th><img src="CSS/anular.bmp" width="20" height="20"></th>
				 <th style="visibility: hidden"></th>
				 </tr>
				 {foreach from = $saData item = i}               
					<tr>
					<td align = 'center'>{$i[0]}<input type = "hidden" name = "paData[{$i[0]}][0]" value="{$i[0]}"/></td>
					{if $i[4] eq 0}
						<td>{$i[1]}<input type = "hidden" name = "paData[{$i[0]}][1]" value="{$i[1]}"/></td>
					{else}
						<td><input type = "text" name = "paData[{$i[0]}][1]" value="{$i[1]}" size='5'/></td>
					{/if}	
					<td align = 'center'><input type = "text" maxlength = "50" name = "paData[{$i[0]}][2]" size='50' value="{$i[2]}"/></td>
					<td>
					   <select style = 'width: 100px;' name = "paData[{$i[0]}][3]">
						  {foreach from = $saEstado item = j}
							 <option value = "{$j[0]}" {if $j[0] == $i[3]} selected {/if}> {$j[1]}</option>
						  {/foreach}
					   </select>
					</td>            
					<td align = 'center'><input type = "checkbox" name = "paCheck[]" value = "{$i[0]}"/></td>
					<td style="visibility: hidden"><input type = "text" name = "paData[{$i[0]}][4]" value = "{$i[4]}"/></td>
					</tr>
				 {/foreach}
			  </table>
			  </div>
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
