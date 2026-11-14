<meta http-equiv='Content-Type' content='text/html; charset=UTF-8'>
<html>
<head>
   <link href='CSS/Sixavi.css' media='screen' rel='stylesheet' type='text/css' />
   <title>Seguridad Operativa</title>
</head>
<body>
<form method='post' action='Seg2530.php#no-back'>
   <div class='wrapper'>
   <div id="MH">
      <ul>
      <li style="margin-left: 50px;"><h1>Reporte de Evaluaciones</h1></li>
      <li style="float:right;margin-right: 100px;"><a onclick="self.location = 'index.php'"> Cerrar Sesión</a></li>
      </ul>
   </div>
   <div id='topnavbar'></div>
   <div id='navigation'>
   <div id='navcontainer'>
   {if $scBehavior eq '0'}
      <fieldset style='width:500px' align='center'>
      <table border=0>
      <tr>
      <td>Tipo: </td>
      <td align='center'>
        <select width="200" style="width: 190px; font-size:11px" name="pcTipo">
        {foreach from = $saTipo item = j}
           <option value="{$j[0]}" > {$j[1]} </option>
        {/foreach}
        </select>
      </td>
      </tr><tr>
      <td>Supervisor: </td>
      <td align='center'>
        <select width="200" style="width: 190px; font-size:11px" name="pcSuperv">
        {foreach from = $saSuperv item = j}
           <option value="{$j[0]}" > {$j[1]} </option>
        {/foreach}
        </select>
      </td>
      </tr><tr>
      <td>Personal: </td>
      <td align='center'>
        <select width="200" style="width: 190px; font-size:11px" name="pcCodPer">
        {foreach from = $saCodPer item = j}
           <option value="{$j[0]}" > {$j[1]} </option>
        {/foreach}
        </select>
      </td>
      </tr><tr>
      <td>Desde: </td>
      <td><input type='text' name='pdFecIni' value='{$sdFecIni}'/></td>
      </tr><tr>
      <td>Hasta: </td>
      <td><input type='text' name='pdFecFin' value='{$sdFecFin}'/></td>
      </tr>
      </table>  
      </fieldset>
      <table>
         <tr>
         <td><input type='submit' name='Boton1' value='Buscar'/></td>
         <td><input type='submit' name='Boton1' value='Salir'/></td>
         </tr>
      </table>
   {elseif $scBehavior eq '1'}
      <fieldset style="width:1000px" align="center">
      <div style="overflow:scroll; height:400px; width:1000px; overflow:auto; border:1px solid #aaaaaa;" border=1>
      <table border=1 width=900 style="font-size:11px">
         <tr>
         <th width=50 align='center'>Número</th>
         <th width=100 align='center'>Fecha</th>
         <th width=300>Supervisor 1</th>
         <th width=300 align='center'>Supervisor 2</th>
         <th width=100 align='center'>Subdivisión</th>
         <th width=50 align='center'><img src="CSS/Edit.png" style="width:30%; height:8%;"/></th>
         </tr>
         {foreach from = $saData item = i}
            <tr>
            <td align='center'>{$i[0]}</td>
            <td align='center'>{$i[1]}</td>
            <td>{$i[2]}</td>
            <td>{$i[3]}</td>
            <td align='center'>{$i[4]}</td>
            <td align='center'><input type="radio" name="pcNroEva" value="{$i[0]}"/></td>
            </tr>
         {/foreach}
      </table>
      </div>
      <table>
         <tr>
         <td><input type="submit" name="Boton2" value="Reporte"/></td>
         <td><input type="submit" name="Boton2" value="Cancelar"/></td>
         </tr>
      </table>
      </fieldset>
   {elseif $scBehavior eq "2"}
         <fieldset style="width:1000px" align="center">
            <table border=0 cellspacing="0" cellpadding="0" style="font-size:11px">
                  <tr>
                  <td align='center'>
				  <a href='{$scFilRet}' target="_blank">Reporte de Evaluación</a>
                  </tr>
            </table>
         </fieldset>
         <table>
            <tr>
               <td><input type="submit" name="Boton3" value="Cancelar"/></td>
            </tr>
         </table>
   {/if}
   </div>
   </div>
   <div id='content'></div>
   <div id='footer'></div>
   </div>
</form>
</body> 
</html>