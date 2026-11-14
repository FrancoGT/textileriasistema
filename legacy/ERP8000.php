<?php
   // Registro y mantenimiento de personal operativo
   require_once 'Libs/Smarty.class.php';
   require_once 'Clases/CMtoPedidos.php';
   require_once 'Clases/CMtoPerfiles.php';
   session_cache_limiter();
   session_start();
   $loSmarty = new Smarty;
   if (!fxValidarPermiso('ERP8000', 'Mnu0000.php?Id=1000'))
      return;
   if (!fxInitSession()) {
      fxCallIndex('NO HA INICIADO SESIÓN');
   } elseif ($_POST['Boton0'] == 'Salir') {
      fxHeader('Mnu0000.php?Id=1000');
   } elseif ($_POST['Boton0'] == 'Hecho') {
      fxHecho();
   } else {
   //INIT
      $lo = new CMtoPedidos();
	  $lo->pcCodUsu=$_SESSION['gcCodUsu'];
      $llOk = $lo->omInitTrabajo();
      if (!$llOk) { // Error
         fxHeader('Mnu0000.php?Id=1000', fxXmlError($lo->pcError));
      } else {
	    $_SESSION['HcCodProd']=$lo->pcCodProd;
		$_SESSION['HcCodPed']=$lo->pcCodPed;
		$_SESSION['HcCodMaq']=$lo->pcCodMaq;
		$_SESSION['HcCodPer']=$lo->pcCodPer;
		$loSmarty->assign('scBehavior', '0');
        $loSmarty->assign('scNombre', $lo->pcNombre);
		$loSmarty->assign('scCodPed', $lo->pcCodPed);
		$loSmarty->assign('scCodProd', $lo->pcCodProd);
		$loSmarty->assign('scDescri', $lo->pcDescri);
		$loSmarty->assign('scProc', $lo->pcProc);
		$loSmarty->assign('snCant', $lo->pnCant);
		$loSmarty->assign('scDescriM', $lo->pcDescriM);
		$loSmarty->assign('scTipo', $lo->pcTipo);
        $loSmarty->display('Plantillas/ERP8000.tpl');
	  }
   }
   
   function fxHecho() {
      global $loSmarty;
	  $lo = new CMtoPedidos();
	  $lo->pcCodProd=$_SESSION['HcCodProd'];
	  $lo->pcCodPed=$_SESSION['HcCodPed'];
	  $lo->pcCodMaq=$_SESSION['HcCodMaq'];
	  $lo->pcCodPer=$_SESSION['HcCodPer'];
	  $lo->pcCodUsu=$_SESSION['gcCodUsu'];
	  $llOk = $lo->omHecho();
      if (!$llOk) { // Error
         fxHeader('Mnu0000.php?Id=1000', fxXmlError($lo->pcError));
      } else {
	    fxAlert("GRABACION CONFORME");
        fxHeader('Mnu0000.php?Id=1000');		
	  }
   }
   
?>
