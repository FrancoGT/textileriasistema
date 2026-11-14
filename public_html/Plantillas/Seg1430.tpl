<!DOCTYPE html>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<html>
<head>
   <link href="CSS/Sixavi.css" media="screen" rel="stylesheet" type="text/css" />
   <script src="CSS/Libreria.js" language="JavaScript"></script>
   <title>Seguridad Operativa</title>
</head>
<body>
<form action="Seg1430.php#no-back" method="post">
   <div class="wrapper">
   <div id="MH">
      <ul>
      <li style="margin-left: 50px;"><h1>Mantenimiento de Unidades</h1></li>
      <li style="float:right;margin-right: 100px;"><a onclick="self.location = 'index.php'"> Cerrar Sesión</a></li>
      </ul>
   </div>
   <div id="topnavbar"></div>
   <div id="navigation">
   <div id="navcontainer">
   {if $scBehavior eq "0"}
      <div style="overflow:scroll; height:500px; width:800px; overflow:auto; border: 1px">
      <table border=2 width=590 style = "font-size:11px">
         <th width=120 align="center">Código</th>
         <th width=800 align="center">Descripción</th>
         <th width=300 align="center">Estado</th>
         <th width=30 align="center"><img src = "CSS/Check.jpg" width="25" height="25"></th>
         {foreach from = $saCodUni item = i}               
            <tr>
            <td align="center">{$i['0']}</td><td>{$i['1']}</td><td>{$i['2']}</td>
            <td align="center"><input type="radio" name="pcCodUni" value="{$i['0']}"/></td>
            </tr>
         {/foreach}
      </table>
      </div>
      <table>
         <tr>
         <td><input type="submit" name="Boton1" value="Aplicar"/></td>
         <td><input type="submit" name="Boton1" value="Nuevo"/></td>
         <td><input type="submit" name="Boton1" value="Salir"/></td>
         </tr>
      </table>   
   {elseif $scBehavior eq "1"}
      <fieldset>
      <table style="font-size:11px">
         <tr>
         <td>Código:</td>
         {if empty($scCodUni)}
            <td><input type="text" maxlength="12" name="pcCodUni" style="text-transform: uppercase" autofocus/></td>         
         {else}
            <td><font color=blue>{$scCodUni}</font><input type="hidden" name="pcCodUni" value="{$scCodUni}"/></td>
         {/if}           
         </tr><tr>
         <td>Descripción:</td>
         <td><input type="text" maxlength="90" name="pcDescri" value="{$scDescri}" size="60" style="text-transform: uppercase" autofocus/></td>
         </tr><tr>
         <td>Estado:</td>
         <td>
            <select name="pcEstado">
            {foreach from = $saEstado item = i}
               <option value="{$i[0]}" {if $i[0] eq $scEstado} selected {/if}>{$i[1]}</option>
            {/foreach}
            </select>
         </td>
         </tr>
      </table>
      </fieldset>
      <table>
         <tr>
         <td></td>
         <td><input type="submit" name="Boton2" value="Grabar"/></td>
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
