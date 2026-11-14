<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<html>
<head>
   <link href="CSS/Sixavi.css" media="screen" rel="stylesheet" type="text/css" />
   <script src="CSS/Libreria.js" language="JavaScript"></script>
   <style>
      #navcontainer a { font-size:small; display:block; color:#333; background-color:#E8E8E8; border:#333 1px solid;  width:400px; padding:5px 5px 5px 5px; text-decoration:none; }
      #navcontainer a:hover { background-color: #E8E8E8; color: blue;}
   </style>
   <script>
      function f_FuncionInit() {
         myFunction0();
         myFunction1();
      }

      function myFunction0() {
         var lcEstaci = document.getElementById('pcEstaci').value;
         if (lcEstaci == "99") {
            document.getElementById('pcKilome').disabled = false;
            var s = document.getElementById('pcKilome');
            s.options.length = 0;
         } else {
            document.getElementById('pcKilome').disabled = true;
            document.getElementById('pcKilome').value = lcEstaci;
         }
      }
      
      function myFunction1() {
         var lcEstacf = document.getElementById('pcEstacf').value;
         if (lcEstacf == "99") {
            document.getElementById('pcKilomf').disabled = false;
            var s = document.getElementById('pcKilomf');
            s.options.length = 0;
         } else {
            document.getElementById('pcKilomf').disabled = true;
            document.getElementById('pcKilomf').value = lcEstacf;
        }
      }
   </script>
   <title>Trazabilidad de Comprobantes</title>
</head>
<body onload="f_FuncionInit();">
<form method="post" action="Seg2640.php#no-back" enctype="multipart/form-data">
   <div class="wrapper">
   <div id="MH">
      <ul>
      <li style="margin-left: 50px;"><h1>Sanciones</h1></li>
      <li style="float:right;margin-right: 100px;"><a onclick="self.location = 'index.php'"> Cerrar Sesión</a></li>
      </ul>
   </div>
   <div id="topnavbar"></div>
   <div id="navigation">
   <div id="navcontainer">
   {if $scBehavior eq '0'}  
      <table style="font-size:11px">
         <tr>
         <td>Personal:</td>
         <td>Sanción:</td>
         <td>Norma:</td>
         </tr><tr>
         <td><select name="pcCodPer" autofocus width="300" style="width: 300px">
             {foreach from = $saCodPer item = i}
                <option value="{$i[0]}" {if $scCodPer eq $i[0]} selected {/if} autofocus> {$i[1]}</option>
             {/foreach}
             </select>
         </td>
         <td><select name="pcCodSan">
             {foreach from = $saCodSan item = i}
                <option value="{$i[0]}" {if $scCodSan eq $i[0]} selected {/if}> {$i[1]}</option>
             {/foreach}
             </select>
         </td>
         <td><select name="pcCodNor" width="300px" style="width:300px">
             {foreach from = $saCodNor item = i}
                <option value="{$i[0]}" {if $scCodNor eq $i[0]} selected {/if}> {$i[1]}</option>
             {/foreach}
             </select>
         </td>
         </tr><tr>
         <td>Desde:</td>
         <td>Hasta:</td>
         </tr><tr>
         <td><input type='text' name='pdFecIni' value='{$sdFecIni}' maxlength="10" placeholder="aaaa-mm-dd" onKeyPress="return fDate(event)"/></td>
         <td><input type='text' name='pdFecFin' value='{$sdFecFin}' maxlength="10" placeholder="aaaa-mm-dd" onKeyPress="return fDate(event)"/></td>
         </tr>
      </table>
      <table>
         <td><input type="submit" name="Boton1" value="Generar"/></td>
         <td><input type="submit" name="Boton1" value="Salir"/></td>
         </tr>
      </table>
   {elseif $scBehavior eq "1"}
      <fieldset style="width:1000px" align="center">
      <legend><font face="calibri" size=4 color=blue>Archivos Anexos</font></legend>
      <table border=0 cellspacing="0" cellpadding="0" style="font-size:11px">
         {foreach from = $saData item = i}
            <tr>
            <td align='center'><a href="{$i[0]}" target="_blank">{$i[1]}</a></td>
            </tr>
         {/foreach}            
      </table>
      </fieldset>
      <table>
         <tr>
         <td><input type="submit" name="Boton2" value="Cancelar"/></td>
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
