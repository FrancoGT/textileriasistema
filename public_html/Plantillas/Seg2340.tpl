<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<html>
<head>
   <link href="CSS/Sixavi.css" media="screen" rel="stylesheet" type="text/css" />
   <style>
      #navcontainer a { font-size:small; display:block; color:#333; background-color:#E8E8E8; border:#333 1px solid;  width:400px; padding:5px 5px 5px 5px; text-decoration:none; }
      #navcontainer a:hover { background-color: #E8E8E8; color: blue;}
   </style>
   <title>Seguridad Operativa</title>
</head>
<body>
<form method = "post" action = "Seg2340.php" enctype="multipart/form-data">
   <div class="wrapper">
   <div id="header">
   <table>
      <tr>
      <td><img src="CSS/Softia.png" width="46" height="38" /></td>
      <td><p class="titulo">Licencias</p></td>
      </tr>
   </table>
   </div>
   <div id="topnavbar"></div>
   <div id="navigation">
   <div id="navcontainer">
   {if $scBehavior eq '0'}  
      <table>
         <tr>         
         <td><input type="submit" name="Generar" value="Generar"/></td>
         <td><input type="button" value="Salir" onclick="self.location = 'Mnu0000.php?Id=6000'"/></td>
         </tr>
      </table>
   {elseif $scBehavior eq "1"}
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
               <td><input type = "submit" name = "Cancelar" value = "Cancelar" /></td>
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
