<!DOCTYPE html>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<html>
<head>
   <link href="CSS/Sixavi.css" media="screen" rel="stylesheet" type="text/css" />
   <link rel="stylesheet" href="CSS/jquery-ui-1.11.0/jquery-ui.css">
   <script type="text/javascript" src="CSS/jquery-1.10.2.min.js"></script> 
   <script type="text/javascript" src="CSS/jquery-ui-1.11.0/jquery-ui.js"></script>
   <script type="text/javascript" src="CSS/jquery.validate.js"></script>
   <script type="text/javascript" src="CSS/jquery.numeric.js"></script>
   <script type="text/javascript" src="CSS/Libgen.js"></script>
   <script>
   $(document).ready(function() {
   $("#pdEmisio").datepicker();
   $("#pnMonto").numeric();
});
   </script>
   <title>TEXTILERIA</title>
</head>
<body>
<form action="ERP1030.php#no-back" method="post">
   <div class="wrapper">
   <div id="MH">
           <ul>
                <li style="margin-left: 50px;"><h1>Mantenimiento de Clientes</h1></li>
				<li style="float:right;margin-right: 100px;"><a onclick="self.location = 'index.php'"> Cerrar Sesión</a></li>
           </ul>
   </div>
   <div id="topnavbar"></div>
   <div id="navigation">
   <div id="navcontainer">
   {if $scBehavior eq "0"}      
       <table>         
           <tr>
         <td>Tipo de Persona:</td>
         <td>
            <select name="pcTipCli">
            {foreach from = $saTipCli item = i}
               <option value="{$i[0]}" {if $i[0] eq $scTipCli} selected {/if}>{$i[1]}</option>
            {/foreach}
            </select>
         </td>
         </tr>
         </table>
      <table>
         <td><input type="submit" name="Aceptar" value="Aceptar"/></td>
		 <td><input type="submit" name="Salir" value="Salir"/></td>
      </table>
   {elseif $scBehavior eq "1"}
       <table>
         <tr>
         <td>Nombre/Razón Social:</td>
         <td><input type="text" maxlength="70" name="pcNombre" size="70" value="" style="text-transform:uppercase;"/></td>
         </tr>
		 
		 {if $scTipo eq 'A'}
		 <tr>		 
         <td>DNI:</td>
         <td><input type="text" class="input_num" name="pcNroDNI" size="8" maxlength="8" value="" /></td>
         </tr>
		 {else}
		 <tr>
         <td>RUC:</td>
         <td><input type="text" class="input_num" name="pcNroRUC" size="11" maxlength="11"value="" /></td>
         </tr>
		 {/if}
		 <tr>
         <td>Estado:</td>
         <td>
            <select name="pcEstado">
            {foreach from = $saEstado item = i}
               <option value="{$i[0]}" {if $i[0] eq $scEstado} selected {/if}>{$i[1]}</option>
            {/foreach}
            </select>
         </td>
         </tr><tr>
         <td>Dirección:</td>
         <td><input type="text" maxlength="70" name="pcDirec" size="51" value="" style="text-transform:uppercase;"/></td>
         </tr>
		 
		 <tr>
         <td>Telefono:</td>
         <td><input type="text" class="input_num" maxlength="70" name="pcTelef" size="15" value="" /></td>
         </tr>

		 {if $scTipo eq 'A'}
		 {else}
		 <tr>		 
         <td>CIUU:</td>
         <td><select name="pcCIIU">
            {foreach from = $saCIIU item = i}
               <option value="{$i[0]}" {if $i[0] eq $scCIIU} selected {/if}>{$i[1]}</option>
            {/foreach}
            </select>
         </td>
         </tr>
		 {/if}

		 {if $scTipo eq 'A'}
		 {else}
		 <tr>
         <td>SIGLAS:</td>
         <td><input type="text" maxlength="4" name="pcSiglas" size="11" value="" style="text-transform:uppercase;"/></td>
         </tr>
		 {/if}
		 <tr>
         <td>PRIORIDAD: </td>
         <td>
            <select name="pcPriori">
            {foreach from = $saPriori item = i}
               <option value="{$i[0]}" {if $i[0] eq $scPriori} selected {/if}>{$i[1]}</option>
            {/foreach}
            </select>
         </td>
         </tr>
      </table>
      <table>
         <tr>
         <td></td>
         <td><input type="submit" name="Grabar" value="Grabar"/></td>
         <td><input type="submit" name="Cancelar" value="Cancelar"/></td>
         </tr>
      </table>
   {elseif $scBehavior eq "2"}
      <table>
         <tr>
         <td>Código:</td>
         <td><input type="text" maxlength="6" name="pcCodEmp" value="" style="text-transform:uppercase;"/></td>
         </tr><tr>
         <td>Descripción:</td>
         <td><input type="text" maxlength="70" name="pcDescri" size="70" value="" style="text-transform:uppercase;"/></td>
         </tr><tr>
         <td>Estado:</td>
         <td>
            <select name="pcEstado">
            {foreach from = $saEstado item = i}
               <option value="{$i[0]}" {if $i[0] eq $scEstado} selected {/if}>{$i[1]}</option>
            {/foreach}
            </select>
         </td>
         </tr><tr>
         <td>Datos varios:</td>
         <td><textarea name="pmDatos" rows="10" cols="50" ></textarea></td>
      </table>
      <table>
         <tr>
         <td></td>
         <td><input type="submit" name="Grabar" value="Grabar"/></td>
         <td><input type="submit" name="Cancelar" value="Cancelar"/></td>
         </tr>
      </table>
    {elseif $scBehavior eq "3"}
      <table>
         <tr>
         <td>Persona Natural:</td>
         <td>
            <select name="pcCodCli">
            {foreach from = $saCodA item = i}
               <option value="{$i[0]}">{$i[1]}</option>
            {/foreach}
            </select>
         </td>
         </tr>
      </table>
      <table>
         <td><input type="submit" name="Aplicar" value="Aplicar" /></td>
         <td><input type="submit" name="Boton" value="Nuevo" /></td>
         <td><input type="submit" name="Boton3" value="Salir" /></td>
      </table>
    {elseif $scBehavior eq "4"}
      <table>
         <tr>
         <td>Persona Natural Juridica sin Fines de Lucro:</td>
         <td>
            <select name="pcCodCli">
            {foreach from = $saCodB item = i}
               <option value="{$i[0]}">{$i[1]}</option>
            {/foreach}
            </select>
         </td>
         </tr>
      </table>
      <table>
         <td><input type="submit" name="Aplicar" value="Aplicar" /></td>
         <td><input type="submit" name="Boton" value="Nuevo" /></td>
         <td><input type="submit" name="Boton4" value="Salir" /></td>
      </table>

	  {elseif $scBehavior eq "5"}
      <table>
         <tr>
         <td>Persona Natural Juridica con Fines de Lucro:</td>
         <td>
            <select name="pcCodCli">
            {foreach from = $saCodC item = i}
               <option value="{$i[0]}">{$i[1]}</option>
            {/foreach}
            </select>
         </td>
         </tr>
      </table>
      <table>
         <td><input type="submit" name="Aplicar" value="Aplicar" /></td>
         <td><input type="submit" name="Boton" value="Nuevo" /></td>
         <td><input type="submit" name="Boton5" value="Salir" /></td>
      </table>
   {elseif $scBehavior eq "6"}
       <table>
	     <tr>
         <td>Código:</td>
            <td><font color=blue>{$scCodCli}</font><input type="hidden" maxlength="6" name="pcCodCli" value="{$scCodCli}"/></td>
         </tr>
         <tr>
         <td>Nombre/Razón Social:</td>
         <td><input type="text" maxlength="70" size="70" value="{$scNombre}" disabled/></td>
		 <td><input type="hidden" maxlength="70" name="pcNombre" size="70" value="{$scNombre}"/></td>
         </tr>
		 
		 {if $scTipo eq 'A'}
		 <tr>		 
         <td>DNI:</td>
         <td><input type="text" maxlength="70"  size="8" lenght="2" value="{$scNroDNI}" disabled/></td>
		 <td><input type="hidden" maxlength="70" name="pcNroDNI" size="8" value="{$scNroDNI}"/></td>
         </tr>
		 {else}
		 <tr>
         <td>RUC:</td>
         <td><input type="text" maxlength="11" size="11" value="{$scNroRUC}" disabled/></td>
		 <td><input type="hidden" maxlength="11" name="pcNroRUC" size="11" value="{$scNroRUC}"/></td>
         </tr>
		 {/if}
		 <tr>
         <td>Estado:</td>
         <td>
            <select name="pcEstado">
            {foreach from = $saEstado item = i}
               <option value="{$i[0]}" {if $i[0] eq $scEstado} selected {/if}>{$i[1]}</option>
            {/foreach}
            </select>
         </td>
         </tr>
		 
		 <tr>
         <td>Dirección:</td>
         <td><input type="text" maxlength="70" name="pcDirec" size="51" value="{$scDirec}" style="text-transform:uppercase;"/></td>
         </tr>
		 
		 <tr>
         <td>Telefono:</td>
         <td><input type="text" class="input_num" maxlength="70" name="pcTelef" size="15" value="{$scTelef}" /></td>
         </tr>

	 {if $scTipo eq 'A'}
	 {else}
         <tr>		 
         <td>CIUU:</td>
         <td><select name="pcCIIU">
            {foreach from = $saCIIU item = i}
               <option value="{$i[0]}" {if $i[0] eq $scCIIU} selected {/if}>{$i[1]}</option>
            {/foreach}
            </select>
         </td>
         </tr>
	 {/if}

	 {if $scTipo eq 'A'}
	 {else}
	 <tr>
         <td>SIGLAS:</td>
         <td><input type="text" maxlength="4" name="pcSiglas" size="11" value="{$scSiglas}" style="text-transform:uppercase;"/></td>
         </tr>
	 {/if}
	 <tr>
         <td>PRIORIDAD: </td>
         <td>
            <select name="pcPriori">
            {foreach from = $saPriori item = i}
               <option value="{$i[0]}" {if $i[0] eq $scPriori} selected {/if}>{$i[1]}</option>
            {/foreach}
            </select>
         </td>
      </tr>

      </table>
      <table>
         <tr>
         <td></td>
         <td><input type="submit" name="Grabar" value="Grabar"/></td>
         <td><input type="submit" name="Cancelar" value="Cancelar"/></td>
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