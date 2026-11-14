<?php
   // Registro y mantenimiento de personal operativo
   require_once 'Libs/Smarty.class.php';
   require_once 'Clases/CMtoProductos.php';
   require_once 'Clases/CMtoPerfiles.php';
   session_cache_limiter();
   session_start();
   $loSmarty = new Smarty;
   if (!fxValidarPermiso('ERP1000', 'Mnu0000.php?Id=1000'))
      return;
   if (!fxInitSession()) {
      fxCallIndex('NO HA INICIADO SESIÓN');
   } elseif ($_POST['Boton0'] == 'Detalle') {
      fxDetalle();
   } elseif ($_POST['Boton0'] == 'Salir') {
      fxHeader('Mnu0000.php?Id=1000');
   } elseif ($_POST['Boton1'] == 'Agregar') {
      fxAgregar();
   } elseif ($_POST['Boton1'] == 'Grabar') {
      fxGrabar();
   } elseif ($_POST['Boton1'] == 'Eliminar') {
      $laData = fxEliminarFila($_REQUEST['paData'], $_REQUEST['paCheck']);
      $loSmarty->assign('scBehavior', '1'); 
      $loSmarty->assign('saCodIns', $_SESSION['paCodIns']);
	  $loSmarty->assign('scDescri', $_SESSION['pcDescri']);
	  $loSmarty->assign('scCodProd', $_SESSION['pcCodProd']);
      $loSmarty->assign('saData',   $laData);
      $loSmarty->display('Plantillas/ERP1070.tpl');
      } elseif ($_POST['Boton1'] == 'Cancelar') {
         $loSmarty->assign('scBehavior', '0');
		 $loSmarty->assign('saDataP', $_SESSION['paDataP']);
         $loSmarty->display('Plantillas/ERP1070.tpl');
   } else {
      $lo = new CMtoProductos();
      $llOk = $lo->omCargarMtoProductos();
      if (!$llOk) { // Error
         fxHeader('Mnu0000.php?Id=1000', fxXmlError($lo->pcError));
      } else { 
         $_SESSION['paDataP'] = $lo->paDataP;
         $_SESSION['paEstado'] = $lo->paEstado;
		 $_SESSION['paCodIns'] = $lo->paCodIns;
         fxScreen();
      }
   }
   
   function fxScreen() {
      global $loSmarty;
      $loSmarty->assign('scBehavior', '0');
      $loSmarty->assign('saDataP', $_SESSION['paDataP']);
	  $loSmarty->display('Plantillas/ERP1070.tpl');
   }
   function fxDetalle(){
      global $loSmarty;
	  $lo = new CMtoProductos();
	  $lo->pcCodProd = $_REQUEST['pcCodProd'];
      $llOk = $lo->omAplicar();
	  if (!$llOk) { // Error
         fxHeader('ERP1070.php', fxXmlError($lo->pcError));
      } else { 
         $_SESSION['paDataP'] = $lo->paDataP;
         $_SESSION['paEstado'] = $lo->paEstado;
		 $_SESSION['paData']=$lo->paData;
		 $_SESSION['pcDescri']=$lo->pcDescri;
		 $_SESSION['pcCodProd']=$lo->pcCodProd;
         $loSmarty->assign('scBehavior', '1');
         $loSmarty->assign('saData', $_SESSION['paData']);
		 $loSmarty->assign('saCodIns', $_SESSION['paCodIns']);
		 $loSmarty->assign('scDescri', $_SESSION['pcDescri']);
		 $loSmarty->assign('scCodProd', $_SESSION['pcCodProd']);
	     $loSmarty->display('Plantillas/ERP1070.tpl');
   
      }
   }
   
   function fxAgregar(){
      global $loSmarty;      
      $laData = null;
      if (isset($_REQUEST['paData'])) {
         $laData = $_REQUEST['paData'];
      }
      $i = count($laData) + 1;
	  $lo = new CMtoProductos();
	  $lo->pcCodIns = $_REQUEST['pcCodIns1'];
      $llOk = $lo->omInsumo();
	  print_r($lo->paDataI);
	  echo '***'.($lo->paDataI[0]);
      $laData[$i] = array($i, $lo->paDataI[0][0], $lo->paDataI[0][1], $lo->paDataI[0][2], $lo->paDataI[0][3]);
      $loSmarty->assign('flagn', '1'); 
      $loSmarty->assign('scBehavior', '1'); 
      $loSmarty->assign('saCodIns', $_SESSION['paCodIns']);
	  $loSmarty->assign('scDescri', $_SESSION['pcDescri']);
	  $loSmarty->assign('scCodProd', $_SESSION['pcCodProd']);
      $loSmarty->assign('saData',   $laData);
      $loSmarty->display('Plantillas/ERP1070.tpl');
   }
   
   function fxGrabar() {
      global $loSmarty;      
      $lo = new CMtoEmpleados();
      $lo->plNuevo  = $_SESSION['plNuevo'];
      $lo->pcCodPer = $_REQUEST['pcCodPer'];
      $lo->pcNombre = $_REQUEST['pcNombre'];
      $lo->pcDirecc = $_REQUEST['pcDirecc'];
      $lo->pcEmail =  $_REQUEST['pcEmail'];
      $lo->pcNroDni = $_REQUEST['pcNroDni'];
      $lo->pcEstado = $_REQUEST['pcEstado'];
      $lo->pcSexo   = $_REQUEST['pcSexo'];
      $lo->pcEstCiv = $_REQUEST['pcEstCiv'];
      $lo->pcCodUsu = $_SESSION['gcCodUsu'];
      $llOk = $lo->omGrabarEmpleado();
      if (!$llOk) {
         $lcMensaje = fxXmlError($lo->pcError);
         $loSmarty->assign('saSexo',   $_SESSION['paSexo']);
         $loSmarty->assign('saEstCiv', $_SESSION['paEstCiv']);
         $loSmarty->assign('saEstado', $_SESSION['paEstado']);
         $loSmarty->assign('scCodPer', $lo->pcCodPer);
         $loSmarty->assign('scNombre', $lo->pcNombre);
         $loSmarty->assign('scDirecc', $lo->pcDirecc);
         $loSmarty->assign('scEmail',  $lo->pcEmail);
         $loSmarty->assign('scNroDni', $lo->pcNroDni);
         $loSmarty->assign('scSexo',   $lo->pcSexo);
         $loSmarty->assign('scEstCiv', $lo->pcEstCiv);
         $loSmarty->assign('scEstado', $lo->pcEstado);
         $loSmarty->assign('scCodEmp', $lo->paData['CDESCRI']);
         $loSmarty->assign('scPuesto', $_SESSION['pcPuesto']);
         $loSmarty->assign('scCargo',  $_SESSION['pcCargo']);
         $loSmarty->assign('scCarDis', $_SESSION['pcCarDis']);
         $loSmarty->assign('scSector', $_SESSION['pcSector']);
         $loSmarty->assign('scDesPry', $_SESSION['pcDesPry']);
         if ($_SESSION['plNuevo'])
            $loSmarty->assign('scBehavior', '2');
         else
         $loSmarty->assign('scBehavior', '1');
         $loSmarty->display('Plantillas/ERP1000.tpl');
         fxAlert($lcMensaje);
      } else {
         if ($lo->plNuevo) {
            $_SESSION['paCodPer'] = $lo->paCodPer;
         }
          $lo = new CMtoEmpleados();
          $lo->omCargarMtoEmpleados();
          $_SESSION['paCodPer'] = $lo->paCodPer;
         fxScreen();
         fxAlert('GRABACION CONFORME');
      }
   }
?>
