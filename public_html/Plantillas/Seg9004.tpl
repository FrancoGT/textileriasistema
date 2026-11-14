<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<html>
<head>
   <script type="text/javascript" src="CSS/Sixavi.js"></script>
   <link href="CSS/Sixavi.css" media="screen" rel="stylesheet" type="text/css" />
   <title>Búsqueda de Normas</title>
</head>
<body>
<form method = "post" action = "Seg9004.php">
	<div class="wrapper">
	   <div id="header">
	   <table>
		  <tr>
		  <td><img src="CSS/Softia.png" width="46" height="38" /></td>
		  <td><p class="titulo">Búsqueda de Normas</p></td>
		  </tr>
	   </table>
	   </div>
	   <div id="topnavbar"></div>
       <div id="navigation">
		   <div id="navcontainer">
		   {if $scBehavior eq '0'}
			  <table>
				 <tr>              
				 <td>Descripción:</td>
				 <td><input maxlength="15" type="text"  name="pcDescri" value=''></td>
				 </tr>
			  </table>
			  <table>
				 <tr>
				 <td><input type="submit" name="Aplicar" value="Aplicar"/></td>
				 <td><input type="button" value="Salir" onclick="self.location = '{$scVolver}'" /></td>
				 </tr>
			  </table>
		   {elseif $scBehavior eq '1'}
			  <table border=2>
				 {foreach from = $saCodNor item = i}               
					<tr>
					   <td>{$i['0']}</td><td>{$i['1']}</td>
					   <td><input type = "radio" name = "pcCodNor" value = "{$i['0']}"/></td>
					</tr>
				 {/foreach}
			  </table>
			  <table>
				 <tr>
				 <td><input type="submit" name="Aceptar" value="Aceptar" /></td>
				 <td><input type="submit" name="Cancelar" value="Cancelar" /></td>
				 <td><input type="button" value="Salir" onclick="self.location = '{$scVolver}'" /></td>
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
