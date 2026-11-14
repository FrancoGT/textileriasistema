<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<html>
<head>
   <link href="CSS/Sixavi.css" media="screen" rel="stylesheet" type="text/css" />
   <title>Gestión de Seguridad</title>
</head>
<body>
<form method = "post" action = "Seg4180.php#no-back">
   <div class="wrapper">
   <div id="MH">
      <ul>
      <li style="margin-left: 50px;"><h1>Consulta de Evaluaciones</h1></li>
      <li style="float:right;margin-right: 100px;"><a onclick="self.location = 'index.php'"> Cerrar Sesión</a></li>
      </ul>
   </div>
   <div id="topnavbar"></div>
   <div id="navigation">
   <div id="navcontainer">
   {if $scBehavior eq '0'}
      <table border = 0>
         <tr>
         <td>Tipo de Evaluación:</td>
         <td>
            <select name = "pcTipEva">
            {foreach from = $saTipEva item = i}
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
      <table border = 1 cellspacing="0" cellpadding="0" style="font-size:11px">
         <tr>
         <th width='50px'>Evaluación</th>
         <th width='300px'>Tren/Tracto</th>
         <th width='100px'>Fecha</th>
         <th width='5px' align="center"><img src="CSS/Edit.png" style="width: 30%; height: 7%;"/></th>
         </tr>
            {foreach from = $saNroEva item = i}
               <tr>
               <td align='center'>{$i[0]}</td>
               <td>{$i[1]}</td>
               <td>{$i[2]}</td>
               <td align='center'><input type = "radio" name = "pcNroEva" value = "{$i[0]}"/></td>
               </tr>
            {/foreach}
            </td></tr>
         </table>
      <table>
         <tr>
         <td><input type="submit" name="Consultar" value="Consultar"/></td>
         <td><input type="submit" name="Cancelar" value="Cancelar"/></td>
         </tr>
      </table>
   {elseif $scBehavior eq '2'}
      <table border = 1 style="font-size:11px">
         <tr>
         <td>Número</td>
         <td>Vehículo</td>
         <td>Fecha</td>
         <td>Hora</td>
         <td>Kilometraje</td>
         <td>Subdivisión</td>
         <td>Est.Inicio</td>
         <td>Est.Final</td>
         </tr><tr>
         <td>{$scNroEva}</td>
         <td>{$scTren}</td>
         <td>{$sdFecEva}</td>
         <td>{$scHorEva}</td>
         <td>{$scKilome}</td>
         <td>{$scDesSub}</td>
         <td>{$scDesEsi}</td>
         <td>{$scDesEsf}</td>
         </tr><tr>
         <td>Supervisor 1</td>
         <td>Supervisor 2</td>
         <td>Empresa</td>
         <td>Proyecto</td>
         <td>Resultado</td>
         <td></td>
         <td></td>
         <td></td>
         </tr><tr>
         <td>{$scNomSp1}</td>
         <td>{$scNomSp2}</td>
         <td>{$scDescri}</td>
         <td>{$scDesPry}</td>
         <td>{$scResult}</td>
         <td></td>
         <td></td>
         <td></td>
         </tr><tr>
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

