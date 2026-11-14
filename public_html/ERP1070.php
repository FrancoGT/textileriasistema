<?php
   // Registro y mantenimiento de personal operativo
   require_once 'Libs/Smarty.class.php';
   require_once 'Clases/CMtoProductos.php';
   require_once 'Clases/CMtoPerfiles.php';
   session_cache_limiter();
   session_start();
   $loSmarty = new Smarty;
   if (!fxValidarPermiso('ERP1070', 'Mnu0000.php?Id=1000'))
      return;
   if (!fxInitSession()) {
      fxCallIndex('NO HA INICIADO SESIÓN');
   } elseif ($_POST['Boton0'] == 'Detalle') {
      fxDetalle();
   } elseif ($_POST['Boton0'] == 'Salir') {
      fxHeader('Mnu0000.php?Id=1000');
   } elseif ($_POST['Boton0'] == 'Nuevo') {
      fxNuevo();
   } elseif ($_POST['Boton1'] == 'Agregar') {
      fxAgregar();
   } elseif ($_POST['Boton1'] == 'Cancelar') {
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
   
   } elseif ($_POST['Boton1'] == 'Grabar') {
      fxGrabar();
   } elseif ($_POST['Boton2'] == 'Grabar') {
      fxGrabar1();
   } elseif ($_POST['Boton1'] == 'Eliminar') {
      $_SESSION['paData'] = fxEliminarFila($_SESSION['paData'], $_REQUEST['paCheck']);
      $loSmarty->assign('scBehavior', '1'); 
      $loSmarty->assign('saCodIns', $_SESSION['paCodIns']);
	  $loSmarty->assign('scDescri', $_SESSION['pcDescri']);
	  $loSmarty->assign('scCodProd', $_SESSION['pcCodProd']);
      $loSmarty->assign('saData',   $_SESSION['paData']);
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
      if (isset($_SESSION['paData'])) {
         $laData = $_SESSION['paData'];
      }
      $i = count($laData) + 1;
	  $lo = new CMtoProductos();
	  $lo->pcCodIns = $_REQUEST['pcCodIns1'];
      $llOk = $lo->omInsumo();
      $laData[$i] = array($lo->paDataI[0][0], $lo->paDataI[0][1], $lo->paDataI[0][2], $lo->paDataI[0][3]);
	  $_SESSION['paData']=$laData;
	  $loSmarty->assign('flagn', '1'); 
      $loSmarty->assign('scBehavior', '1'); 
      $loSmarty->assign('saCodIns', $_SESSION['paCodIns']);
	  $loSmarty->assign('scDescri', $_SESSION['pcDescri']);
	  $loSmarty->assign('scCodProd', $_SESSION['pcCodProd']);
      $loSmarty->assign('saData',$laData);
      $loSmarty->display('Plantillas/ERP1070.tpl');
   }
   
   function fxGrabar() {
      global $loSmarty; 
      $lo = new CMtoProductos();
	  $lo->pcDescri=$_REQUEST['pcDescri'];
	  $lo->paData=$_SESSION['paData'];
	  $lo->pcCodProd = $_SESSION['pcCodProd'];
	  $lo->pcCodUsu = $_SESSION['gcCodUsu'];
	  $llOk = $lo->omGrabarTabla();
      if (!$llOk) {
         fxAlert(fxXmlError($lo->pcError));
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
	  
         fxAlert('GRABACION CONFORME');
      }
   }
   function fxNuevo(){
      global $loSmarty;
	  $_SESSION['plNuevo'] = true;
         $loSmarty->assign('scBehavior', '2');
		 $loSmarty->assign('saEstado', $_SESSION['paEstado']);
	     $loSmarty->display('Plantillas/ERP1070.tpl');
   
      }
	  function fxGrabar1(){
      global $loSmarty;
	  $lo = new CMtoProductos();
	  $lo->plNuevo=$_SESSION['plNuevo'];
	  $lo->pcDescri=$_REQUEST['pcDescri'];
	  $lo->pcEstado=$_REQUEST['pcEstado'];
	  $lo->pcCodUsu=$_SESSION['gcCodUsu'];
	  $lo->omNuevoProducto();
	  if (!$llOk) { // Error
          $loSmarty->assign('scBehavior', '0');
          $loSmarty->assign('saDataP', $_SESSION['paDataP']);
	      $loSmarty->display('Plantillas/ERP1070.tpl');
          fxAlert(fxXmlError($lo->pcError));
      }else{
	  fxAlert('GRABACION CONFORME');
	      fxHeader('ERP1070.php');
 	  }
	  
      }

?>
