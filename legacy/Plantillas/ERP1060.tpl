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
   <script type="text/javascript" src="CSS/Libgen.js"></script>   <script language="JavaScript">
	function f_Desactivar(){
	
           var seleccion = document.getElementById('jcTipPer');           
           if(seleccion.selectedIndex=="2"){               document.getElementById('jcRuc').disabled=true;              
           }else{
               document.getElementById('jcRuc').disabled=false;
           }
       }
	$(document).ready(function() {
   $("#pdFecNac").datepicker();
   });
   </script>   
   <title>TEXTILERIA</title>
</head>
<body onload=f_Desactivar()>
<form action="ERP1060.php#no-back" method="post">
   {if isset($maintenanceToken)}<input type="hidden" name="_token" value="{$maintenanceToken}" />{/if}
   <div class="wrapper">
   <div id="MH">
           <ul>
                <li style="margin-left: 50px;"><h1>Mantenimiento de Proveedores</h1></li>
				<li style="float:right;margin-right: 100px;"><a onclick="self.location = 'index.php'"> Cerrar Sesión</a></li>
           </ul>
   </div>
   <div id="topnavbar"></div>
   <div id="navigation">
   <div id="navcontainer">
   {if isset($maintenanceMessage) && $maintenanceMessage ne ''}<p role="status">{$maintenanceMessage}</p>{/if}
   {if $scBehavior eq '0'}         
      <div style="overflow:scroll; height:200px; width:800px; overflow:auto; border: 1px">
      <table border = 2 style = "font-size:11px">
         <th bgcolor="#F29818"width="100" align="center">RUC</th>
         <th bgcolor="#F29818"align="center" width="300">Razón Social</th>
         <th bgcolor="#F29818"align="center">Estado</th>         
         <th bgcolor="#F29818"align="center"><img src = "CSS/Check.jpg" width="25" height="25"></th>         
         {foreach from = $saData item = i}               
            <tr>
                <td>{$i['1']}</td><td>{$i['2']}</td><td>{$i['3']}</td>                
                <td><input type="radio" name="pcCodPrv" value = "{$i['0']}"/></td>
            </tr>            
         {/foreach}            
      </table>
      </div>         
      <table>
         <tr>
         <td><input type="submit" name="Boton0" value="Nuevo" /></td>         
         <td><input type="submit" name="Boton0" value="Editar" /></td>                  
         <td><input type="submit" name="Boton0" value="Salir" /></td>         
         </tr>
      </table>   
      {elseif $scBehavior eq '1'}      
      <table>
         <tr>
         <td>Nombre/Razón Social:</td>
         {if $flag eq 'nuevo'}
         <td><input type="text" name="pcNombre" value="{$scNombre}" size="80"style="text-transform:uppercase;"></td> 
         {else}      
         <td>{$scNombre}</td> 
		 <td><input type="hidden" maxlength="70" name="pcNombre" size="70" value="{$scNombre}"/></td>
         {/if}
         <input type="hidden" name="pcCodPrv" value="{$scCodPrv}" size="80"style="text-transform:uppercase;">
         </tr><tr>                           
         <td>Tipo de Persona:</td>
         <td>
             {if $flag eq 'nuevo'}
                <select name="pcTipPer" id="jcTipPer" onchange="f_Desactivar()">
               {foreach from = $saTipPer item = i}
                   <option value = "{$i[0]}" {if isset($scTipPer) && $i[0] eq $scTipPer}selected{/if}>{$i[1]} </option>
               {/foreach}
               </select>
               {else}
               <select name="pcTipPer" id="jcTipPer" disabled='true'>
               {foreach from = $saTipPer item = i}
                   <option value = "{$i[0]}" {if isset($scTipPer) && $i[0] eq $scTipPer}selected{/if}>{$i[1]} </option>
               {/foreach}
               </select>
               
            {/if}
         </td>
         </tr><tr>
         <td>DNI:</td>
         {if $flag eq 'nuevo'}
         <td><input type="text" class="input_num"name="pcNroDni" value="{$scNroDni}" maxlength="8" id="jcDni" size="8"></td>
         {else}
         <td>{$scNroDni}</td>
         {/if}
         </tr><tr>
         <td>RUC:</td>
         {if $flag eq 'nuevo'}
         <td><input type="text" class="input_num"name="pcNroRuc" value="{$scNroRuc}" maxlength="11" size="11" id="jcRuc"></td> 
         {else}
         <td>{$scNroRuc}</td> 
         {/if}
         </tr><tr>
         <td>Fecha de Surgimiento:</td>
         {if $flag eq 'nuevo'}
         <td><input type="text" name="pdFecNac" id='pdFecNac'value="{$sdFecNac}" maxlength="11" placeholder="aaaa-mm-dd" size="11"></td> 
         {else}
         <td>{$sdFecNac}</td> 
         {/if}
         </tr><tr>         
         <td>Dirección:</td>
         <td><input type="text" name="pcDirecc" value="{$scDirecc}" size="80" style="text-transform:uppercase;"></td>                          
         </tr><tr>                      
         <td>Otros Datos:</td>                           
         <td><textarea name="pcTexCon" id="pcTexCon" rows="10" cols="80" style="text-transform:uppercase;"></textarea></td> 
         </tr><tr>
         <td>Línea de Producción:</td>  
         <td>
            {foreach from = $saTipos item = i}                            
                <input type="checkbox" name ="paTipArs[]" value = "{$i[0]}" id="jcArsTip"
                       {foreach from = $saArsTip item = a}                           
                       {if $i[0] eq $a[0]} checked {/if}{/foreach}/>{$i[1]}<br /> 
            {/foreach}          
         </td>         
         </tr>
      </table>                  
      <table>
         <tr>
         <td><input type="submit" name="Boton1" value="Grabar" /></td>                  
         <td><input type="submit" name="Boton1" value="Cancelar" /></td>         
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
