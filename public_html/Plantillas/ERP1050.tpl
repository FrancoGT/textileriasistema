<!DOCTYPE html>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<html>
<head>
   <link href="CSS/Sixavi.css" media="screen" rel="stylesheet" type="text/css" />
   <link href="CSS/Sixavi.css" media="screen" rel="stylesheet" type="text/css" />
   <link rel="stylesheet" href="CSS/jquery-ui-1.11.0/jquery-ui.css">
   <script type="text/javascript" src="CSS/jquery-1.10.2.min.js"></script> 
   <script type="text/javascript" src="CSS/jquery-ui-1.11.0/jquery-ui.js"></script>
   <script type="text/javascript" src="CSS/jquery.validate.js"></script>
   <script type="text/javascript" src="CSS/jquery.numeric.js"></script>
   <script type="text/javascript" src="CSS/Libgen.js"></script>
   <script>
   $(document).ready(function() {
   $("#pdFecFin").datepicker();
});
   </script>
   <title>TEXTILERIA</title>
</head>
<body>
<form action="ERP1050.php#no-back" method="post">
   <div class="wrapper">
   <div id="MH">
           <ul>
                <li style="margin-left: 50px;"><h1>Mantenimiento de Insumos</h1></li>
				<li style="float:right;margin-right: 100px;"><a onclick="self.location = 'index.php'"> Cerrar Sesión</a></li>
           </ul>
   </div>
   <div id="topnavbar"></div>
   <div id="navigation">
   <div id="navcontainer">
   {if $scBehavior eq '0'}                
         <div style="overflow:scroll; height:300px; width:1000px; overflow:auto; border: 1px">
         <table border = 2 style = "font-size:11px">
         <th bgcolor="#F29818" width="400" align="center">Nombre</th>
         <th bgcolor="#F29818" width="200"align="center">Tipo de Artículo</th>
         <th bgcolor="#F29818" align="center">Unidad de Medida</th>         
         <th bgcolor="#F29818" align="center">Granulado</th> 
         <th bgcolor="#F29818" align="center">Precio por Unidad</th>         
         <th bgcolor="#F29818" align="center"><img src = "CSS/Check.png" width="25" height="25"></th>         
         {foreach from = $saData item = i}               
            <tr>
                <td>{$i['1']}</td><td>{$i['2']}</td><td>{$i['3']}</td>
                <td>{$i['4']}</td><td>{$i['5']}</td>
                <td><input type="radio" name="pcCodArs" value = "{$i['0']}"/></td>
            </tr>            
         {/foreach}            
      </table>
      </div>      
      <table>
         <tr>
         <td><input type="submit" name="Boton1" value="Nuevo" /></td>         
         <td><input type="submit" name="Boton1" value="Editar" /></td>         
         <td><input type="submit" name="Boton1" value="Eliminar" /></td>         
         <td><input type="submit" name="Boton1" value="Salir" /></td>         
         </tr>
      </table>   
       {elseif $scBehavior eq '1'}
      <table>         
         <tr>   
          <td>Subtipo de Producto:</td>
         {if $scUniMed}
         <td>
             <input type="hidden" name="pcUniMed" value="{$scUniMed}">
             <select name="pcSubTip" disabled="true">
            {foreach from = $saSubTip item = i}
               <option value = "{$i[0]}" {if $i[0] eq $scSubTip} selected {/if}>{$i[1]} </option>               
            {/foreach}
            </select>
         </td>     
         {else}
            <td>                
                <select name="pcSubTip">
                {foreach from = $saSubTip item = i}
                    <option value = "{$i[0]}" {if $i[0] eq $scSubTip} selected {/if}>{$i[1]} </option>
                {/foreach}
                </select>
            </td>     
         {/if}
         </tr><tr>
         <td>Descripción:</td>
         {if $scDescri}
             <td>{$scDescri}<input type="hidden" name="pcDescri" value="{$scDescri}"style="text-transform:uppercase;"></td>                 
         {else}
            <td><input type="text" name="pcDescri" value="{$scDescri}"style="text-transform:uppercase;"></td>                 
         {/if}                     
         </tr>         
         <tr>
         <td>Unidad:</td>         
         <td>
             <input type="hidden" name="pcUniMed" value="{$scUniMed}">
            <select name="pcUniMed">
            {foreach from = $saUniMed item = i}
               <option value = "{$i[0]}" {if $i[0] eq $scUniMed} selected {/if}>{$i[1]} </option>               
            {/foreach}
            </select>
         </td>              
         </tr>
         <tr>         
         <td>Granulado:</td>
         <td><input type="text" class="input_num" name="pnCanGra" value="{$snCanGra}"></td> 
         </tr>                 
         <tr>         
         <td>Precio:</td>
         <td><input type="text" class="input_num" name="pnPrecio" value="{$snPrec}"></td>                 
         </tr>

      </table>            
      <table>
         <tr>
         <td><input type="submit" name="Boton2" value="Grabar" /></td>                  
         <td><input type="submit" name="Boton2" value="Cancelar" /></td>         
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