<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<html>
<head>
   <script type="text/javascript" src="CSS/Sixavi.js"></script>
   <link href="CSS/Sixavi.css" media="screen" rel="stylesheet" type="text/css" />
   <title>Búsqueda de Evaluaciones</title>
</head>
<body>
<form method = "post" action = "Seg9006.php">
   <div class="wrapper">
   <div id="MH">
      <ul>
      <li style="margin-left: 50px;"><h1>Búsqueda de Evaluaciones</h1></li>
      <li style="float:right;margin-right: 100px;"><a onclick="self.location = 'index.php'"> Cerrar Sesión</a></li>
      </ul>
   </div>
      <div id="topnavbar"></div>
       <div id="navigation">
         <div id="navcontainer">
         {if $scBehavior eq '0'}
           <table>
             <tr>              
             <td>Desde: </td>
             <td><input maxlength="10" type="text"  name="pdFecIni" value='{$sdFecIni}'></td>
             </tr><tr>
             <td>Hasta: </td>
             <td><input maxlength="10" type="text"  name="pdFecFin" value='{$sdFecFin}'></td>
           </table>
           <table>
             <tr>
             <td><input type="submit" name="Boton1" value="Aplicar"/></td>
             <td><input type="submit" name="Boton1" value="Salir"/></td>
             </tr>
           </table>
         {elseif $scBehavior eq '1'}
		 <div style="overflow:scroll; height:500px; width:800px; overflow:auto; border: 1px">
           <table border=2 width=710 style="font-size:11px">
             <tr>
             <th width=90 align='center'>Número</th>
             <th width=110 align='center'>Fecha</th>
             <th width=80 align='center'>Hora</th>
             <th width=400>Maquinista (JT/BR)</th>
             <th width=30 align="center"><img src = "CSS/Check.jpg" width="25" height="25"></th>
             </tr>
             {foreach from = $saData item = i}               
               <tr>
                  <td align='center'>{$i['0']}</td>
                  <td align='center'>{$i['1']}</td>
                  <td align='center'>{$i['2']}</td>
                  <td>{$i['3']}</td>
                  <td align='center'><input type = "radio" name = "pcNroEva" value = "{$i['0']}"/></td>
               </tr>
             {/foreach}
           </table>
		   </div>
           <table>
             <tr>
             <td><input type="submit" name="Boton2" value="Aceptar" /></td>
             <td><input type="submit" name="Boton2" value="Cancelar" /></td>
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
