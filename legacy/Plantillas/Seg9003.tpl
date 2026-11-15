<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<html>
<head>
   <link href="CSS/Sixavi.css" media="screen" rel="stylesheet" type="text/css" />
   <script src="CSS/Libreria.js" language="JavaScript"></script>
   <title>Búsqueda de Cursos</title>
</head>
<body>
<form method = "post" action = "Seg9003.php">
   <div class="wrapper">
   <div id="MH">
      <ul>
      <li style="margin-left: 50px;"><h1>Búsqueda de Cursos</h1></li>
      <li style="float:right;margin-right: 100px;"><a onclick="self.location = 'index.php'"> Cerrar Sesión</a></li>
      </ul>
   </div>
      <div id="topnavbar"></div>
       <div id="navigation">
         <div id="navcontainer">
         {if $scBehavior eq '0'}
           <table>
             <tr>              
             <td>Desde:</td>
             <td><input maxlength="10" type="text"  name="pdFecIni" value='{$sdFecIni}' placeholder='aaaa-mm-dd'></td>
			 <td>Hasta:</td>
             <td><input maxlength="10" type="text"  name="pdFecFin" value='{$sdFecFin}' placeholder='aaaa-mm-dd'></td>
			 <td>Tipo:</td>
             <td>
             <select name="pcTipo">
              {foreach from = $saTipo item = i}
               <option value = "{$i[0]}" {if $i[0] eq $scTipo} selected {/if}>{$i[1]}</option>
              {/foreach}
             </select>
             </td>
             </tr>
           </table>
           <table>
             <tr>
             <td><input type="submit" name="Boton1" value="Aplicar"/></td>
             <td><input type="submit" name="Boton1" value="Salir"/></td>
             </tr>
           </table>
         {elseif $scBehavior eq '1'}
		   <div style="overflow:scroll; height:400px; width:800px; overflow:auto; border: 2px;">
           <table border=2 width=550 style="font-size:11px">
           <tr>
           <th width=50 align='center'>Número</th>
           <th width=400 align='center'>Descripción</th>
           <th width=120 align='center'>Fecha</th>
           <th width=30 align='center'><img src="CSS/Check.jpg" height="32" width="32"> </th>
           </tr>
             {foreach from = $saCodCur item = i}               
               <tr>
                  <td align='center'>{$i['0']}</td><td>{$i['1']}</td><td align='center'>{$i['2']}</td>
                  <td align='center'><input type = "radio" name = "pcCodCur" value = "{$i['0']}"/></td>
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
