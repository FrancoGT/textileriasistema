<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<html>
<head>
   <link href="CSS/Sixavi.css" media="screen" rel="stylesheet" type="text/css" />
   <title>Seguridad Operativa</title>
</head>
<script type="text/javascript">
   function myFunction1(e, f) {
      if ((!e) && (f)) {
         var Flag = confirm("¿Desea borrar los valores ingresados?");
         if (!Flag) {
		    return;
         }
      }
      if (e) {
         document.getElementById('pcDamPro1').disabled = false;
         document.getElementById('pcDamPro2').disabled = false;
         document.getElementById('pcDamPro3').disabled = false;
         document.getElementById('pcDamPro4').disabled = false;
         document.getElementById('pmDamPro').disabled = false;
         document.getElementById('pmDamPro').style.backgroundColor = "#FFFFA0"; 
      } else {
         document.getElementById('pcDamPro1').disabled = true;
         document.getElementById('pcDamPro2').disabled = true;
         document.getElementById('pcDamPro3').disabled = true;
         document.getElementById('pcDamPro4').disabled = true;
         document.getElementById('pmDamPro').disabled = true;
         document.getElementById('pcDamPro1').checked = false;
         document.getElementById('pcDamPro2').checked = false;
         document.getElementById('pcDamPro3').checked = false;
         document.getElementById('pcDamPro4').checked = false;
         document.getElementById('pmDamPro').value = ' ';
         document.getElementById('pmDamPro').style.backgroundColor = "#FFFFFF"; 
      }
   }
    
   function myFunction2(e, f) {
      if ((!e) && (f)) {
         var Flag = confirm("¿Desea borrar los valores ingresados?");
         if (!Flag) {
		    return;
         }
      }
      if (e) {
         document.getElementById('pmLesPer').disabled = false;
		 document.getElementById('pmLesPer').style.backgroundColor = "#FFFFA0"; 
      } else {
         document.getElementById('pmLesPer').disabled = true;
         document.getElementById('pmLesPer').style.backgroundColor = "#FFFFFF";
		 document.getElementById('pmLesPer').value = ' ';
      }
   }
   
   function myFunction3(e, f) {
      if ((!e) && (f)) {
         var Flag = confirm("¿Desea borrar los valores ingresados?");
         if (!Flag) {
		    return;
         }
      }
      if (e) {
         document.getElementById('pcTiePer').disabled = false;
         document.getElementById('pnCosto').disabled = false;
      } else {
         document.getElementById('pcTiePer').disabled = true;
		 document.getElementById('pcTiePer').value = '';
		 document.getElementById('pnCosto').value = '0';
         document.getElementById('pnCosto').disabled = true;
      }
   }
   
   function myFunction4(e, f) {
      if ((!e) && (f)) {
         var Flag = confirm("¿Desea borrar los valores ingresados?");
         if (!Flag) {
		    return;
         }
      }
      if (e) {
         document.getElementById('pmDamAmb').disabled = false;
		 document.getElementById('pmDamAmb').style.backgroundColor = "#FFFFA0"; 
         document.getElementById('pcDamAmb1').disabled = false;
         document.getElementById('pcDamAmb2').disabled = false;
         document.getElementById('pcDamAmb3').disabled = false;
      } else {
	     document.getElementById('pmDamAmb').value = ' ';
         document.getElementById('pmDamAmb').disabled = true;
         document.getElementById('pmDamAmb').style.backgroundColor = "#FFFFFF";
         document.getElementById('pcDamAmb1').disabled = true;
         document.getElementById('pcDamAmb2').disabled = true;
		 document.getElementById('pcDamAmb3').disabled = true;
		 document.getElementById('pcDamAmb1').checked = false;
		 document.getElementById('pcDamAmb2').checked = false;
		 document.getElementById('pcDamAmb3').checked = false;
      }
   }

   function FuncionInit() {
      var Flag = document.getElementById('pcDamnif').checked;
      myFunction1(Flag, false);
      Flag = document.getElementById('pcLesPer').checked;
      myFunction2(Flag, false);
      Flag = document.getElementById('pcPerPro').checked;
      myFunction3(Flag, false);
      Flag = document.getElementById('pcDamAmb').checked;
      myFunction4(Flag, false);
   }
</script>
<body onload="FuncionInit()">
<!--<body>-->
<form method="post" action="Seg2260.php#no-back">
   <div class="wrapper">
   <div id="MH">
      <ul>
      <li style="margin-left: 50px;"><h1>Tipo de Incidente</h1></li>
      <li style="float:right;margin-right: 100px;"><a onclick="self.location = 'index.php'"> Cerrar Sesión</a></li>
      </ul>
   </div>
   <div id="topnavbar"></div>
   <div id="navigation">
   <div id="navcontainer">
   {if $scBehavior eq '0'}
      <fieldset style="width:500px" align="center">
      <table border=0>
         <tr>
         <td>Número Incidente</td>
         <td><input type="text" name="pcNroInc" value="" size='6' autofocus/></td>
         </tr>
      </table>  
      </fieldset>
      <table>
         <tr>
         <td><input type="submit" name="Boton1" value="Aplicar"/></td>
         <td><input type="submit" name="Boton1" value="Buscar"/></td>
         <td><input type="submit" name="Boton1" value="Salir"/></td>
         </tr>
      </table>
   {elseif $scBehavior eq '1'}
      <fieldset style="width:1000px" align="center">
      <legend align="left"><font face="calibri" size=3 color=blue>Incidente</font></legend>
      <table border=0 style="font-size:11px">
         <tr>
         <td>Número de Incidente: <font color=blue>{$scNroInc}</font><input type="hidden" name="pcNroInc" value="{$scNroInc}" onfocus="FuncionPrueba()" autofocus/></td>
         </tr><tr>
         <td><textarea class="memo" name="pmDatos" style="background:#FFFFFF" disabled>{$smDatos}</textarea></td>
         </tr><tr>
         <td><b>¿Lesiones personales?</b><input type="checkbox" name="pcLesPer" id="pcLesPer" value="S"  {if $scLesPer eq 'S'} checked {/if} onClick="myFunction2(this.checked, true)"/></td>
         </tr><tr>
         <td>Descripción de lesiones</td>
         </tr><tr>
         <td><textarea type="text" class="memo" name="pmLesPer" id="pmLesPer">{$smLesPer}</textarea></td>
         </tr>
      </table>
      <table border=0 style="font-size:11px">
         <tr>
         <td><b>Con daño a la propiedad</td>
         <td><input type="checkbox" name="pcDamnif" id="pcDamnif" value='{$scDamnif}' {if $scDamnif eq 'S'} checked {/if} onClick="myFunction1(this.checked, true)"/></td>
         </tr><tr>
         <td>¿Daño propiedad PeruRail?</td>
         <td><input type="checkbox" name="pcDamPro1" id="pcDamPro1" value='S' {if $scDamPro1 eq 'S'} checked {/if}/></td>
         <td>¿Daño propiedad Cliente?</td>
         <td><input type="checkbox" name="pcDamPro2" id="pcDamPro2" value='S' {if $scDamPro2 eq 'S'} checked {/if}/></td>
         </tr><tr>
         <td>¿Daño propiedad Contratista?</td>
         <td><input type="checkbox" name="pcDamPro3" id="pcDamPro3" value='S' {if $scDamPro3 eq 'S'} checked {/if}/></td>
         <td>¿Daño propiedad Terceros?</td>
         <td><input type="checkbox" name="pcDamPro4" id="pcDamPro4" value="S" {if $scDamPro4 eq 'S'} checked {/if}/></td>
         </tr>
      </table>
      <table border=0 style="font-size:11px">
         <tr>
         <td>Descripción de Daño</td>
         </tr><tr>
         <td><textarea type="text" class="memo" name="pmDamPro" id="pmDamPro">{$smDamPro}</textarea></td>
      </table>
      <table border=0 style="font-size:11px">
         <tr>
         <td><b>Pérdidas en el Proceso</td>
         <td><input type="checkbox" name="pcPerPro" id="pcPerPro" value="S" {if $scPerPro eq 'S'} checked {/if} onClick="myFunction3(this.checked, true)"/></td>
         </tr><tr>
         <td>Tiempo Perdido</td>
         <td><input type="text" name="pcTiePer" id="pcTiePer" value="{$scTiePer}" size='6' placeholder="00d00h"/></td>
         <td>Costo (US$)</td>
         <td><input type="text" name="pnCosto" id="pnCosto" value="{$snCosto}" size='12' onkeypress="return justNumbers(event);"/></td>
         </tr>
      </table>
      <table border=0 style="font-size:11px">
         <tr>
         <td><b>Daño al ambiente</td>
         <td><input type="checkbox" name="pcDamAmb" id="pcDamAmb" value="S" {if $scDamAmb eq 'S'} checked {/if} onClick="myFunction4(this.checked, true)"/></td>
         </tr><tr>
         <td>¿Derrame/Fuga?</td>
         <td><input type="checkbox" name="pcDamAmb1" id="pcDamAmb1" value="S" {if $scDamAmb1 eq 'S'} checked {/if} /></td>
         <td>¿Muerte animal?</td>
         <td><input type="checkbox" name="pcDamAmb2" id="pcDamAmb2" value="S" {if $scDamAmb2 eq 'S'} checked {/if} /></td>
         <td>¿Otros?</td>
         <td><input type="checkbox" name="pcDamAmb3" id="pcDamAmb3" value="S" {if $scDamAmb3 eq 'S'} checked {/if}/></td>
         </tr>
      </table>
      <table border=0 style="font-size:11px">
         <td>Descripción de daño al ambiente</td>
         <tr></tr>
         <td><textarea type="text" class="memo" name="pmDamAmb" id="pmDamAmb" >{$smDamAmb}</textarea></td>
      </table>
      </fieldset>
      <table>
         <tr>
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