<?php
   // Registro y mantenimiento de personal operativo
   require_once 'Libs/Smarty.class.php';
   require_once 'Clases/CMtoAlmacenes.php';
   require_once 'Clases/CMtoProductos.php';
   require_once 'Clases/CMtoPerfiles.php';
   session_cache_limiter();
   session_start();
   $loSmarty = new Smarty;
   if (!fxValidarPermiso('ERP1091', 'Mnu0000.php?Id=1000'))
      return;
   if (!fxInitSession()) {
      fxCallIndex('NO HA INICIADO SESIÓN');
   } elseif ($_POST['Boton0'] == 'Detalle') {
      fxDetalle();
   } elseif ($_POST['Boton0'] == 'Salir') {
      fxHeader('Mnu0000.php?Id=1000');
   } elseif ($_POST['Boton0'] == 'Asignar Insumos') {
      fxAsignar();
   } elseif ($_POST['Boton1'] == 'Agregar') {
      fxAgregar();
   } elseif ($_POST['Boton1'] == 'Cancelar') {
      $loSmarty->assign('scBehavior', '0');
      $loSmarty->assign('saDataA', $_SESSION['paDataA']);
	  $loSmarty->display('Plantillas/ERP1091.tpl');   
   } elseif ($_POST['Boton1'] == 'Grabar') {
      fxGrabar();
   } elseif ($_POST['Boton1'] == 'Eliminar') {
      $_SESSION['paDataI'] = fxEliminarFila($_SESSION['paDataI'], $_REQUEST['paCheck']);
      $loSmarty->assign('scBehavior', '1'); 
	  $loSmarty->assign('scCodIns', $_SESSION['pcCodIns']);
      $loSmarty->assign('saCodIns', $_SESSION['paCodIns']);
	  $loSmarty->assign('scOrigen', $_SESSION['pcOrigen']);
	  $loSmarty->assign('scDestino', $_SESSION['pcDestino']);
	  $loSmarty->assign('saCodAlm', $_SESSION['paCodAlm']);
      $loSmarty->assign('saDataI',$laData);
      $loSmarty->display('Plantillas/ERP1091.tpl');
      } elseif ($_POST['Boton1'] == 'Cancelar') {
         $loSmarty->assign('scBehavior', '0');
		 $loSmarty->assign('saDataP', $_SESSION['paDataP']);
         $loSmarty->display('Plantillas/ERP1091.tpl');
   } else {
      $lo = new CMtoAlmacenes();
      $llOk = $lo->omInitAsignar();
      if (!$llOk) { // Error
         fxHeader('Mnu0000.php?Id=1000', fxXmlError($lo->pcError));
      } else { 
         $_SESSION['paDataA'] = $lo->paDataA;
         $_SESSION['paCodAlm'] = $lo->paCodAlm;
		 $_SESSION['paCodIns'] = $lo->paCodIns;
         fxScreen();
      }
   }
   
   function fxScreen() {
      global $loSmarty;
      $loSmarty->assign('scBehavior', '0');
      $loSmarty->assign('saDataA', $_SESSION['paDataA']);
	  $loSmarty->display('Plantillas/ERP1091.tpl');
   }
   function fxDetalle(){//muestra los insumos del almacen
      global $loSmarty;
	  $lo = new CMtoAlmacenes();
	  $lo->pcCodAlm = $_REQUEST['pcCodAlm'];
      $llOk = $lo->omAplicar();
	  if (!$llOk) { // Error
         fxHeader('ERP1091.php', fxXmlError($lo->pcError));
      } else { 
		 $_SESSION['pcCodAlm']=$lo->pcCodAlm;
         $loSmarty->assign('scBehavior', '2');
         $loSmarty->assign('saDataID', $lo->paDataID);
		 $loSmarty->assign('scCodAlm', $_SESSION['pcCodAlm']);
		 $loSmarty->assign('scNomAlm', $lo->pcNomAlm);
	     $loSmarty->display('Plantillas/ERP1091.tpl');
   
      }
   }
   function fxAsignar(){
      global $loSmarty;
	  $_SESSION['llFirst'] = true;
	  $loSmarty->assign('scBehavior', '1');
	  $loSmarty->assign('saCodAlm', $_SESSION['paCodAlm']);
	  $loSmarty->assign('saCodIns', $_SESSION['paCodIns']);
	  $loSmarty->display('Plantillas/ERP1091.tpl');
   }
   
   function fxAgregar(){
      global $loSmarty;
	  if ($_SESSION['llFirst']){
	     $laData = null;
	     $_SESSION['llFirst'] = false;
	  }else{
         $laData = $_SESSION['paDataI'];
	  }
      $i = count($laData) + 1;
	  $lo = new CMtoProductos();
	  $lo->pcCodIns = $_REQUEST['pcCodIns'];
      $llOk = $lo->omInsumo();
      $laData[$i] = array($lo->paDataI[0][0], $lo->paDataI[0][1], $lo->paDataI[0][2], $lo->paDataI[0][3]);
	  $_SESSION['paDataI']=$laData;
	  $_SESSION['pcOrigen']=$_REQUEST['pcOrigen'];
	  $_SESSION['pcDestino']=$_REQUEST['pcDestino'];
	  $loSmarty->assign('flagn', '1'); 
      $loSmarty->assign('scBehavior', '1'); 
      $loSmarty->assign('saCodIns', $_SESSION['paCodIns']);
	  $loSmarty->assign('scOrigen', $_SESSION['pcOrigen']);
	  $loSmarty->assign('scDestino', $_SESSION['pcDestino']);
	  $loSmarty->assign('saCodAlm', $_SESSION['paCodAlm']);
      $loSmarty->assign('saDataI',$laData);
      $loSmarty->display('Plantillas/ERP1091.tpl');
   }
   
   function fxGrabar() {
      global $loSmarty; 
      $lo = new CMtoAlmacenes();
	  $lo->paDataI=$_REQUEST['paDataI'];
	  $lo->pcOrigen = $_REQUEST['pcOrigen'];
	  $lo->pcDestino = $_REQUEST['pcDestino'];
	  $lo->pcCodUsu = $_SESSION['gcCodUsu'];
	  $llOk = $lo->omGrabarTabla();
      if (!$llOk) {
          fxAlert(fxXmlError($lo->pcError));
		  $loSmarty->assign('scBehavior', '1'); 
		  $loSmarty->assign('saCodIns', $_SESSION['paCodIns']);
	      $loSmarty->assign('scOrigen', $_SESSION['pcOrigen']);
	      $loSmarty->assign('scDestino', $_SESSION['pcDestino']);
     	  $loSmarty->assign('saCodAlm', $_SESSION['paCodAlm']);
          $loSmarty->assign('saDataI',$_SESSION['paDataI']);   
          $loSmarty->display('Plantillas/ERP1091.tpl');		           
      } else {
	     fxAlert('GRABACION CONFORME');
         fxHeader('ERP1091.php');         
      }
   }
?>
