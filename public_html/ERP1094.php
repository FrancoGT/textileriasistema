<?php
   // Registro y mantenimiento BANCOS
   require_once 'Libs/Smarty.class.php';
   require_once 'Clases/CMtoBancos.php';
   require_once 'Clases/CMtoPerfiles.php';
   session_cache_limiter();
   session_start();
   $loSmarty = new Smarty;
   if (!fxValidarPermiso('ERP1094', 'Mnu0000.php?Id=1000'))
      return;
   if (!fxInitSession()) {
      fxCallIndex('NO HA INICIADO SESIÓN');
   } else if (@$_POST['Boton2'] == 'Grabar') {
      fxGrabar();
   } else if (@$_POST['Boton2'] == 'Cancelar') {
      fxHeader('ERP1094.php');
   } else if (@$_POST['Boton1'] == 'Nuevo') {
      fxNuevo();
   } else if (@$_POST['Boton1'] == 'Salir') {
fxHeader('Mnu0000.php?Id=1000');
   } else if (@$_POST['Boton2'] == 'Salir') {
      fxHeader('Mnu0000.php?Id=1000');
   } else {
      $lo = new CMtoBancos();
      $llOk = $lo->omInitCuentas();
      if (!$llOk) { // Error
         fxHeader('Mnu0000.php?Id=1000', fxXmlError($lo->pcError));
      } else { 
         $_SESSION['paCodPrv'] = $lo->paCodPrv;
         $_SESSION['paCodBan'] = $lo->paCodBan;
         fxScreen();
      }
   }
   
   function fxScreen() {
      global $loSmarty;
      $loSmarty->assign('scBehavior', '0');
      $loSmarty->display('Plantillas/ERP1094.tpl');
   }
   function fxNuevo() {
      global $loSmarty;
	  $_SESSION['pcTipo']=$_REQUEST['pcTipo'];
	  $loSmarty->assign('scTipo', $_SESSION['pcTipo']);
	  if($_SESSION['pcTipo']=='1')
	     $loSmarty->assign('saCodPrv', $_SESSION['paCodPrv']);
	  else 
	     $loSmarty->assign('saCodBan', $_SESSION['paCodBan']);
      $loSmarty->assign('scBehavior', '1');
      $loSmarty->display('Plantillas/ERP1094.tpl');
   }
   
   function fxGrabar() {
      global $loSmarty;      
      $lo = new CMtoBancos();
	  if($_SESSION['pcTipo']=='1'){
	     $lo->pcCodPrv=$_REQUEST['pcCodPrv'];
		 $lo->pcCodBan='999999';
	  }elseif($_SESSION['pcTipo']=='2'){
	     $lo->pcCodBan=$_REQUEST['pcCodBan'];
		 $lo->pcCodPrv='999999';
	  }
      $lo->pcNroCta = $_REQUEST['pcNroCta'];
	  $lo->pcTipo = $_SESSION['pcTipo'];
      $llOk = $lo->omGrabarCuentas();
      if (!$llOk) {
         fxAlert(fxXmlError($lo->pcError));
		 fxHeader('ERP1094.php');
        } else {
         fxAlert('GRABACION CONFORME');
		 fxHeader('Mnu0000.php?Id=1000');
      }
   }
?>