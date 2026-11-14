<?php
   // Registro y mantenimiento BANCOS
   require_once 'Libs/Smarty.class.php';
   require_once 'Clases/CMtoPagos.php';
   require_once 'Clases/CMtoPerfiles.php';
   session_cache_limiter();
   session_start();
   $loSmarty = new Smarty;
   if (!fxValidarPermiso('ERP7020', 'Mnu0000.php?Id=1000'))
      return;
   if (!fxInitSession()) {
      fxCallIndex('NO HA INICIADO SESIÓN');
   } else if (@$_POST['Boton2'] == 'Cargar') {
      fxCargar();
   } else if (@$_POST['Boton2'] == 'Pagar') {
      fxPagarF();
   } else if (@$_POST['Boton2'] == 'Cancelar') {
      fxHeader('ERP7020.php');
   } else if (@$_POST['Boton1'] == 'Pagar') {
      fxPagar();
   } else if (@$_POST['Boton1'] == 'Cancelar') {
      fxHeader('ERP7020.php');
   } else if (@$_POST['Boton0'] == 'Detalle') {
      fxDetalle();
   } else if (@$_POST['Boton0'] == 'Salir') {
      fxHeader('Mnu0000.php?Id=1000');
   } else {
      $lo = new CMtoPagos();
      $llOk = $lo->omInitCobrar1();
      if (!$llOk) { // Error
         fxHeader('Mnu0000.php?Id=1000', fxXmlError($lo->pcError));
      } else { 
         $_SESSION['paDataFac'] = $lo->paDataFac;
         fxScreen();
      }
   }
   
   function fxScreen() {
      global $loSmarty;
      $loSmarty->assign('scBehavior', '0');
	  $loSmarty->assign('saDataFac', $_SESSION['paDataFac']);
      $loSmarty->display('Plantillas/ERP7020.tpl');
   }
   
   function fxDetalle(){
      global $loSmarty;
	  $lo = new CMtoPagos();
      $lo->pcCodFac = $_REQUEST['pcCodFac'];
	  $_SESSION['pcCodFac']=$lo->pcCodFac;
      $llOk = $lo->omDetalleFactura1();
      if (!$llOk) {
         fxAlert(fxXmlError($lo->pcError));
		 fxHeader('ERP7020.php');
        } else {
        $loSmarty->assign('scBehavior', '1');
	    $loSmarty->assign('scNombre', $lo->pcNombre);
		$loSmarty->assign('scTipo', $lo->pcTipo);
		$loSmarty->assign('scTipPer', $lo->pcTipPer);
		$loSmarty->assign('scNroDNI', $lo->pcNroDNI);
		$loSmarty->assign('sdFecLis', $lo->pdFecLis);
		$loSmarty->assign('scNroRUC', $lo->pcNroRUC);
		$loSmarty->assign('scDirecc', $lo->pcDirecc);
		$loSmarty->assign('saData', $lo->paData);
		$loSmarty->assign('VV', $lo->VV);
		$loSmarty->assign('IGV', $lo->IGV);
		$loSmarty->assign('Total', $lo->Total);
        $loSmarty->display('Plantillas/ERP7020.tpl');   
      }
   }
   function fxPagar(){
      global $loSmarty;
	  $lo = new CMtoPagos();
	  $lo->pcCodFac = $_SESSION['pcCodFac'];
	  $llOk=$lo->omInitCuentas();
      if (!$llOk) {
         fxAlert(fxXmlError($lo->pcError));
		 fxHeader('ERP7020.php');
        } else {
		$loSmarty->assign('scBehavior', '2');
		$_SESSION['pcNombre']=$lo->pcNombre;
		$_SESSION['paCtaPrv']=$lo->paCtaPrv;
		$_SESSION['paCodBan']=$lo->paCodBan;
	    $loSmarty->assign('scNombre', $lo->pcNombre);
		$loSmarty->assign('saCtaPrv', $lo->paCtaPrv);
		$loSmarty->assign('saCodBan', $lo->paCodBan);
		$loSmarty->assign('saCtaBan', $lo->paCtaBan); // que la primera te llene ************
        $loSmarty->display('Plantillas/ERP7020.tpl');   
      }
   }
   function fxPagarF(){
      global $loSmarty;
	  $lo = new CMtoPagos();
	  $lo->pcCodFac = $_SESSION['pcCodFac'];
	  $lo->pcCtaBan = $_REQUEST['pcCtaBan'];
	  $lo->pcCodBan = $_REQUEST['pcCodBan'];
	  $llOk = $lo->omGrabarPago();
	  if (!$llOk) {
         fxAlert(fxXmlError($lo->pcError));
		 $loSmarty->assign('scBehavior', '2');
		 $loSmarty->assign('scNombre', $_SESSION['pcNombre']);
		 $loSmarty->assign('saCtaPrv', $_SESSION['paCtaPrv']);
		 $loSmarty->assign('scCtaPrv', $lo->pcCtaPrv);
		 $loSmarty->assign('saCodBan', $_SESSION['paCodBan']);
		 $loSmarty->assign('scCodBan', $lo->pcCodBan);
		 $loSmarty->display('Plantillas/ERP7020.tpl');
        } else {
		 fxAlert("GRABACION CONFORME");
		 fxHeader('ERP7020.php');
      }
   }
   
   function fxCargar(){
      global $loSmarty;
	  $lo = new CMtoPagos();
	  $lo->pcCodBan = $_REQUEST['pcCodBan'];
	  $lo->pcCtaPrv = $_REQUEST['pcCtaPrv'];
	  $llOk = $lo->omCargarCtaBan();
	  if (!$llOk) {
         fxAlert(fxXmlError($lo->pcError));
		 fxHeader('ERP7020.php');
        } else {
		 $loSmarty->assign('scBehavior', '2');
	     $loSmarty->assign('scNombre', $_SESSION['pcNombre']);
		 $loSmarty->assign('saCtaPrv', $_SESSION['paCtaPrv']);
		 $loSmarty->assign('scCtaPrv', $lo->pcCtaPrv);
		 $loSmarty->assign('saCodBan', $_SESSION['paCodBan']);
		 $loSmarty->assign('scCodBan', $lo->pcCodBan);
		 $loSmarty->assign('saCtaBan', $lo->paCtaBan); 
         $loSmarty->display('Plantillas/ERP7020.tpl');   
      }
   }
?>
