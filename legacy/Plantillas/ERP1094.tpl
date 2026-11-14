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

   <title>TEXTILERIA</title>
   <script src="CSS/Libreria.js" language="JavaScript"></script>
</head>
<body>
<form action="ERP1094.php#no-back" method="post">
   {if isset($maintenanceToken)}<input type="hidden" name="_token" value="{$maintenanceToken}" />{/if}
   <div class="wrapper">
   <div id="MH">
           <ul>
                <li style="margin-left: 50px;"><h1>Mantenimiento de Cuentas</h1></li>
				<li style="float:right;margin-right: 100px;"><a onclick="self.location = 'index.php'"> Cerrar Sesión</a></li>
           </ul>
   </div>
   <div id="topnavbar"></div>
   <div id="navigation">
   <div id="navcontainer">
   {if isset($maintenanceMessage) && $maintenanceMessage ne ''}<p role="status">{$maintenanceMessage}</p>{/if}
   {if $scBehavior eq "0"}
      <table border = 0>
         <tr>
         <td><input type="radio" name="pcTipo" value="1">Cuentas de Proveedores</td>
         </tr><tr>
         <td><input type="radio" name="pcTipo" value="2">Cuentas de Bancos</td>
         </tr>
      </table>
      <table>
         <td><input type="submit" name="Boton1" value="Nuevo" /></td>
         <td><input type="submit" name="Boton1" value="Salir" /></td>
      </table>
   {elseif $scBehavior eq "1"}
      <table>
	  {if {$scTipo} eq "1"}
	  <tr>
         <td>Proveedores:</td>
         <td>
            <select name="pcCodPrv">
            {foreach from = $saCodPrv item = i}
               <option value="{$i[0]}" {if $i[0] eq $scCodPrv} selected {/if}>{$i[1]}</option>
            {/foreach}
            </select>
	     </td>
         </tr>
	  {elseif {$scTipo} eq "2"}
	  <tr>
         <td>Bancos:</td>
         <td>
            <select name="pcCodBan">
            {foreach from = $saCodBan item = i}
               <option value="{$i[0]}" {if $i[0] eq $scCodBan} selected {/if}>{$i[1]}</option>
            {/foreach}
            </select>
	     </td>
         </tr>
	  {/if}
	  <tr>    
         <td>Nro. de Cuenta:</td>
         <td><input type="text" class="input_num" maxlength="14" name="pcNroCta" value="{$scNroCta}" size="14" /></td>
         </tr>
	  </table>
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