<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<html>
<head>
   <link href="CSS/Sixavi.css" media="screen" rel="stylesheet" type="text/css" />
   <title>Seguridad Operativa</title>
</head>
<body>
<form method = "post" action = "Seg5130.php#no-back">
   <div class="wrapper">
   <div id="MH">
      <ul>
      <li style="margin-left: 50px;"><h1>Reporte de Normas por Personal</h1></li>
      <li style="float:right;margin-right: 100px;"><a onclick="self.location = 'index.php'"> Cerrar Sesión</a></li>
      </ul>
   </div>
   <div id="topnavbar"></div>
   <div id="navigation">
   <div id="navcontainer">
   {if $scBehavior eq "0"}
      <table>
         <tr>
         <td>Personal:</td>
         <td>
            <select name = "pcCodPer">
            {foreach from = $saCodPer item = i}
               <option value="{$i[0]}">{$i[1]}</option>
            {/foreach}
            </select>
         </td>
         </tr><tr>
         <td>Norma:</td>
         <td>
            <select name = "pcCodNor" style="width:500px">
            {foreach from = $saCodNor item = i}
               <option value="{$i[0]}">{$i[1]}</option>
            {/foreach}
            </select>
         </td>
      </table>
      <table>
         <td><input type="submit" name="Boton1" value="Reporte" /></td>
         <td><input type="submit" name="Boton1" value="Excel" /></td>
         <td><input type="submit" name="Boton1" value="Salir" /></td>
      </table>
   {elseif $scBehavior eq '1'}
      <fieldset style="width:1000px" align="center">
      <legend><font face = "calibri" size = 4 color = blue>Archivos Anexos</font></legend>
      <table border = 0 cellspacing="0" cellpadding="0" style="font-size:11px">
         <a href="{$scFilRep}" target="_blank">Capacitaciones de Personal por Norma</a>
      </table>
      </fieldset>
      <table>
         <tr>
         <td><input type = "submit" name = "Boton2" value = "Cancelar" /></td>
         </tr>
      </table>
   {elseif $scBehavior eq "2"}
         <fieldset style="width:1000px" align="center">
            <legend><font face = "calibri" size = 4 color = blue>Archivos Anexos</font></legend>
            <table border = 0 cellspacing="0" cellpadding="0" style="font-size:11px">
               {foreach from = $saData item = i}
                  <tr>
                  <td align='center'>
                  <a href="{$i[0]}" target="_blank">{$i[1]}</a>                
                  </tr>
               {/foreach}            
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
   <div id="content"></div>
   <div id="footer"></div>
   </div>
</form>
</body>
</html>