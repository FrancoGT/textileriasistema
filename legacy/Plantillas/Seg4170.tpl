<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<html>
<head>
   <link href="CSS/Sixavi.css" media="screen" rel="stylesheet" type="text/css" />
   <title>Gestión de Seguridad</title>
</head>
<body>
<form method = "post" action = "Seg4170.php#no-back">
   <div class="wrapper">
   <div id="MH">
      <ul>
      <li style="margin-left: 50px;"><h1>Reporte de Cursos por Norma</h1></li>
      <li style="float:right;margin-right: 100px;"><a onclick="self.location = 'index.php'"> Cerrar Sesión</a></li>
      </ul>
   </div>
   <div id="topnavbar"></div>
   <div id="navigation">
   <div id="navcontainer">
   {if $scBehavior eq '0'}
      <table border = 0>
         <tr>
         <td>Norma:</td>
         <td>
            <select name = "pcCodNor" style="width:500px">
            {foreach from = $saCodNor item = i}
               <option value = "{$i[0]}"> {$i[1]}</option>
            {/foreach}
            </select>
         </td>
         </tr>
      </table>
      <table>
         <tr>
         <td><input type="submit" name="Aplicar" value="Aplicar"/></td>
         <td><input type="button" value="Salir" onclick="self.location = 'Mnu0000.php?Id=6001'"/></td>
         </tr>
      </table>
   {elseif $scBehavior eq '1'}
      <table border = 0>
         <tr>
         <td>Curso:</td>
         <td>
            <select name = "pcCodCur">
            {foreach from = $saCodCur item = i}
               <option value = "{$i[0]}"> {$i[1]}</option>
            {/foreach}
            </select>
         </td>
         </tr>
      </table>
      <table>
         <tr>
         <td><input type="submit" name="Consultar" value="Consultar"/></td>
         <td><input type="submit" name="Cancelar" value="Cancelar"/></td>
         </tr>
      </table>
   {elseif $scBehavior eq '2'}
      <table border = 0>
         <tr>
         <td>Curso:</td>
         <td>{$scDescri}</td>
         <tr></tr>
         <td>Docente:</td>
         <td>{$scNomDoc}</td>
         <tr></tr>
         <td>Fecha:</td>
         <td>{$sdFecha}</td>
         <tr></tr>
         <td>Duración:</td>
         <td>{$scTiempo}</td>
      </table>
      <table border = 1 cellspacing="0" cellpadding="0" style="font-size:11px">
         <tr>
         <th width='50px' align='center'>Norma</th>
         <th width='300px'>Descripción</th>
         </tr>
         {foreach from = $saData item = i}
            <tr>
            <td align='center'>{$i[0]}</td>
            <td>{$i[1]}</td>
         {/foreach}
         </td></tr>
      </table>
      <table>
         <tr>
         <td><input type="submit" name="Volver" value="Volver"/></td>
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

