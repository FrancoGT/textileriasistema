<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<html>
<head>
   <title>Seguridad Operativa</title>
   <link href="CSS/Sixavi.css" media="screen" rel="stylesheet" type="text/css" />
   <script src="CSS/jquery.min.js"></script>
   <script type="text/javascript">
      function setCookie(cname, cvalue, exdays)   {
         var d = new Date();
         d.setTime(d.getTime()+(exdays*24*60*60*1000));
         var expires = "expires=" + d.toGMTString();
         document.cookie = cname + "=" + cvalue + "; " + expires;
      } 
   
      function getCookie(cname) {
         var name = cname + "=";
         var ca = document.cookie.split(';');
         for (var i = 0; i < ca.length; i++) {
             var c = ca[i].trim();
             if (c.indexOf(name) == 0) 
                return c.substring(name.length,c.length);
         }
         return "";
      } 
   </script>
   <script type="text/javascript">
      function htable() {
         var tabl_hight = parseInt($("#content-subt input[type=radio]:checked").closest("tr").offset().top - 100);
         document.cookie="htable=" + tabl_hight;
         document.cookie="tablcheck=" + $("#content-subt input[type=radio]:checked").val();
         return true;
      }
   </script>
</head>
<body>
<form method="post" action="Seg1320.php#no-back" onsubmit="return htable();">
   <div class="wrapper">
   <div id="MH">
      <ul>
      <li style="margin-left: 50px;"><h1>Registro y Mantenimiento de Sub-Tablas</h1></li>
      <li style="float:right;margin-right: 100px;"><a onclick="self.location = 'index.php'"> Cerrar Sesión</a></li>
      </ul>
   </div>
   <div id="topnavbar"></div>
   <div id="navigation">
   <div id="navcontainer">
   {if $scBehavior eq '0'}
      <fieldset style="width:500px">
      <legend align="left"><font face="calibri" size=3 color=blue>Sub-Tablas</font></legend>
      <div id="content-subt" style="overflow:scroll; height:500px; width:650px; overflow:auto; border: 1px;">
      <table border=1 style="font-size:11px">
         <tr>
         <th width='50' align="center">Tabla</th>
         <th width='490' align="center">Descripción</th>
         <th width='25' align="center"><img src="CSS/Edit.png" style="width: 100%; height: 10%;"/></th>
         </tr>
         {foreach from = $saCodTab item = i}
            <tr>
            <td align="center">{$i[0]}<input type="hidden" name="paData[{$i[0]}][0]" value="{$i[0]}"/></td>
            <td>{$i[1]}</td>
            <td align="center"><input type="radio" name="paCheck[]" value="{$i[0]}"/></td>
            </tr>
         {/foreach}
      </table>
      </div>
      </fieldset>
      <table>
         <tr>
         <td><input type="submit" name="Boton1" value="Editar"/></td>
         <td><input type="submit" name="Boton1" value="Salir"/></td>
         </tr>
      </table>
   {elseif $scBehavior eq '1'}
      <fieldset style="width:800px">
      <table style="font-size:11px">
         <tr>
         <td>Tabla:</td>
         <td><font color=blue>{$scCodTab} - {$scDescri}</font></td>
         </tr>
      </table>
      </fieldset>
      <fieldset style="width:500px">
      <legend align="left"><font face="calibri" size=3 color=blue>Detalle de Tabla</font></legend>
      <div id="content-table" style="overflow:scroll; height:400px; width:800px; overflow:auto; border: 1px;">
      <table border=1 style="font-size:11px">
         <tr>
         <th width='20' align="center">Id</th>
         <th width='30' align="center">Orden</th>
         {if $scCodTab eq '021'}
            <th width='80' align="center">PK</th>
         {else}
            <th width='80' align="center">Código</th>
         {/if}
         <th width='100' align="center">Descripción</th>
         <th width='5px' align="center"><img src="CSS/anular.bmp" style="width: 100%; height: 100%;"/></th>
         <th style="visibility: hidden"></th>
         </tr>
         {foreach from = $saData item = i}
            <tr>
            <td align="center">{$i[0]}<input type="hidden" name="paData[{$i[0]}][0]" value="{$i[0]}"/></td>
            <td><input type="text" name="paData[{$i[0]}][1]" value="{$i[1]}" size='5' /></td>
            {if ($i[4] eq 0) and ($scCodTab != '021')}
               <td align="center">{$i[2]}<input type="hidden" name="paData[{$i[0]}][2]" value="{$i[2]}" /></td>
            {else}
               <td align="center"><input type="text" name="paData[{$i[0]}][2]" value="{$i[2]}" maxlength="10" size='15' align='center' style="text-transform: uppercase"/></td>
            {/if}
            <td align="center"><input type="text" name="paData[{$i[0]}][3]" value="{$i[3]}" size='50' maxlength="98"/></td>
            <td align="center"><input type="radio" name="paCheck[]" value="{$i[0]}"/></td>
            <td style="visibility: hidden"><input type="text" name="paData[{$i[0]}][4]" value="{$i[4]}"/></td>
            </tr>
         {/foreach}
      </table>
      </div>
      </fieldset>
      <table>
         <tr>
         <td><input type="submit" name="Grabar" value="Grabar" /></td>
         <td><input type="submit" name="Eliminar" value="Eliminar" /></td>
         <td><input type="submit" name="Nuevo" value="Nuevo" /></td>
         <td><input type="submit" value="Cancelar" value="Cancelar" /></td>
         </tr>
      </table>
   {/if}
   </div>
   </div>
   <div id="content"></div>
   <div id="footer"></div>
   </div>
</form>
{if $flagn eq '1'}
   <script type="text/javascript">
      document.getElementById("content-table").scrollTop = 4000;
   </script>
{/if}
</body>
</html>
