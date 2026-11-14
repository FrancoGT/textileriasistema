<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<html>
<head>
   <script language=JavaScript>
      window.onload = function() {
         document.getElementById("pcCodAfj").focus();
      }      
   </script>
   <style type="text/css">
      .boton {
        font-size:10px;
        font-family:Verdana,Helvetica;
        font-weight:bold;
        color:white;
        background:#638cb5;
        border:0px;
        width:10px;
        height:2px;
      }
      .botonS {
        font-size:10px;
        font-family:Verdana,Helvetica;
        font-weight:bold;
        color:white;
        background:#FF0000;
        border:0px;
        width:10px;
        height:2px;
      }
   </style>
   <link href="CSS/Sixavi.css" media="screen" rel="stylesheet" type="text/css" />
   <title>Registro de Heridos por Incidentes</title>
</head>
<body>
<form method = "post" action = "Seg9901.php">
	<div class="wrapper">
	   <div id="header">
	   <table>
		  <tr>
		  <td><img src="CSS/Softia.png" width="46" height="38" /></td>
		  <td><p class="titulo">Registro de Heridos por Incidentes</p></td>
		  <td><input type="submit" name="Salir" value="Salir" onclick="self.location = 'Mnu0000.php'" class="botonS"/></td>
		  </tr>
	   </table>
	   </div>
	   <div id="topnavbar"></div>
       <div id="navigation">
		   <div id="navcontainer">
		   {if $scBehavior eq '0'}
			  <fieldset style="width:1000px" align="center">
			  <legend><font face = "calibri" size = 4 color = blue>Datos del Incidente</font></legend>
			  <table border = 0 cellspacing="0" cellpadding="0" style="font-size:11px">
				 <tr>
				 <td>Código de Incidente:</td>
				 <td>{$scCodInc}<input type = "hidden" name = "pcCodInc" value="{$scCodInc}" size='6'/></td>
				 <td></td>
			  </table>
			  </fieldset>
			  <fieldset style="width:1000px" align="center">
			  <legend><font face = "calibri" size = 4 color = blue>Personal Herido</font></legend>
			  <table border = 1 cellspacing="0" cellpadding="0" style="font-size:11px">
				 <tr>
				 <th width='50px'>Id</th>
				 <th width='100px'>Personal</th>
				 <th width='50px'>DNI</th>
				 <th width='500px'>Nombre</th>
				 <th width='100px'>X</th>
				 </tr>
				 {foreach from = $saData item = i}
					<tr>
					<td align='center'>{$i[0]}<input type = "hidden" name = "paData[{$i[0]}][0]" value="{$i[0]}"/></td>
					<td align='center'>
					   <select  width="200" style="width: 300px" name = "paData[{$i[0]}][1]" style="font-size:11px">
						  {foreach from = $saCodPer item = j}
							 <option value = "{$j[0]}" {if $i[1] eq $j[0]} selected {/if}> {$j[1]}</option>
						  {/foreach}
					   </select>
					</td>
					<td><input type="text" maxlength="8" name="paData[{$i[0]}][2]" value='{$i[2]}'/></td>
					<td><input type="text" maxlength="50" name="paData[{$i[0]}][3]" value='{$i[3]}' size='50'/></td>
					<td><input type="radio" name="paCheck[]" value='{$i[0]}'/></td>
				 {/foreach}
			  </table>
			  </fieldset>
			  <table>
				 <tr>
				 <td><input type="submit" name="Nuevo" value="Nuevo" class="boton"/></td>
				 <td><input type="submit" name="Eliminar" value="Eliminar" class="boton"/></td>
				 </tr>
			  </table>
			  <table>
				 <tr>
				 <td><input type="submit" name="Grabar" value="Grabar"/></td>
				 <td><input type="submit" name="Cancelar" value="Cancelar"/></td>
				 <td><input type="button" value="Salir" onclick="self.location = 'Mnu0000.php'"/></td>
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

