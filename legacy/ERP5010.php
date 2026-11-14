<?php
   // Registro y mantenimiento de personal operativo
   require_once 'Libs/Smarty.class.php';
   require_once 'Clases/CMtoLogistica.php';
   require_once 'Clases/CMtoProductos.php';
   require_once 'Clases/CMtoPerfiles.php';
   session_cache_limiter();
   session_start();
   $loSmarty = new Smarty;
   if (!fxValidarPermiso('ERP5010', 'Mnu0000.php?Id=1000'))
      return;
   if (!fxInitSession()) {
      fxCallIndex('NO HA INICIADO SESIÓN');
   } elseif ($_POST['Boton1'] == 'Grabar') {
      fxGrabar();
   } elseif ($_POST['Boton1'] == 'Cancelar') {
      fxHeader('Mnu0000.php?Id=1000');
   } elseif ($_POST['Boton1'] == 'Agregar') {
      fxAgregar();
   } else {
      $lo = new CMtoLogistica();
      $llOk = $lo->omInit();
      if (!$llOk) { // Error
         fxHeader('Mnu0000.php?Id=1000', fxXmlError($lo->pcError));
      } else { 
         $_SESSION['paCodPrv'] = $lo->paCodPrv;
		 $_SESSION['paCodIns'] = $lo->paCodIns;
         fxScreen();
      }
   }
   
   function fxScreen() {
      global $loSmarty;
      $loSmarty->assign('scBehavior', '0');
      $loSmarty->assign('saCodPrv', $_SESSION['paCodPrv']);
	  $loSmarty->assign('saCodIns', $_SESSION['paCodIns']);
	  $loSmarty->display('Plantillas/ERP5010.tpl');
   }
   function fxGrabar(){//muestra los insumos del almacen
      global $loSmarty;
	  $lo = new CMtoLogistica();
	  $lo->paDataI=$_REQUEST['paDataI'];
	  $lo->pcCodPrv=$_REQUEST['pcCodPrv'];
      $llOk = $lo->omGrabar();
	  if (!$llOk) { // Error
         fxHeader('ERP5010.php', fxXmlError($lo->pcError));
      } else { 
	     fxAlert("GRABACION CONFORME");
         fxHeader('Mnu0000.php?Id=1000');   
      }
   }
    
	function fxAgregar(){
      global $loSmarty;
	  $_SESSION['pcCodPrv']=$_REQUEST['pcCodPrv'];
	  if ($_SESSION['llFirst']){
	     $laData = null;
	     $_SESSION['llFirst'] = false;
	  }else{
         $laData = $_SESSION['paDataI'];
	  }
      $i = fxCount($laData) + 1;
	  $lo = new CMtoProductos();
	  $lo->pcCodIns = $_REQUEST['pcCodIns'];
      $llOk = $lo->omInsumo();
      $laData[$i] = array($lo->paDataI[0][0], $lo->paDataI[0][1], $lo->paDataI[0][2], $lo->paDataI[0][3]);
	  $_SESSION['paDataI']=$laData;
	  $_SESSION['pcOrigen']=$_REQUEST['pcOrigen'];
	  $_SESSION['pcDestino']=$_REQUEST['pcDestino'];
	  $loSmarty->assign('flagn', '1'); 
      $loSmarty->assign('scBehavior', '0'); 
      $loSmarty->assign('saCodIns', $_SESSION['paCodIns']);
	  $loSmarty->assign('scCodPrv', $_SESSION['pcCodPrv']);
	  $loSmarty->assign('saCodPrv', $_SESSION['paCodPrv']);
	  $loSmarty->assign('scOrigen', $_SESSION['pcOrigen']);
	  $loSmarty->assign('scDestino', $_SESSION['pcDestino']);
	  $loSmarty->assign('saCodAlm', $_SESSION['paCodAlm']);
      $loSmarty->assign('saDataI',$laData);
      $loSmarty->display('Plantillas/ERP5010.tpl');
   } 
   
   ?>
