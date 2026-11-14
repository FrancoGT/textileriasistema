<?php
   // Registro y mantenimiento de personal operativo
   require_once 'Libs/Smarty.class.php';
   require_once 'Clases/CMtoPedidos.php';
   require_once 'Clases/CMtoPerfiles.php';
   session_cache_limiter();
   session_start();
   $loSmarty = new Smarty;
   if (!fxValidarPermiso('ERP4040', 'Mnu0000.php?Id=1000'))
      return;
   if (!fxInitSession()) {
      fxCallIndex('NO HA INICIADO SESIÓN');
   } elseif ($_POST['Boton0'] == 'Salir') {
      fxHeader('Mnu0000.php?Id=1000');
   } elseif ($_POST['Boton0'] == 'Detalle') {
      fxDetalle();
   } elseif ($_POST['Boton1'] == 'Asignar') {
      fxAsignar();
   } elseif ($_POST['Boton1'] == 'Cancelar') {
fxHeader('Mnu0000.php?Id=1000');
   } elseif ($_POST['Boton2'] == 'Grabar') {
      fxGrabar();
   } elseif ($_POST['Boton2'] == 'Cancelar') {
      fxDetalle();
   } else {
   //INIT
      $lo = new CMtoPedidos();
      $llOk = $lo->omInitPedidosRep4040();
      if (!$llOk) { // Error
         fxHeader('Mnu0000.php?Id=1000', fxXmlError($lo->pcError));
      } else {
         $_SESSION['paDataProd'] = $lo->paDataProd;
         fxScreen();
      }
   }
   
   function fxScreen() {
      global $loSmarty;
      $loSmarty->assign('scBehavior', '0');
      $loSmarty->assign('saDataProd', $_SESSION['paDataProd']);
      $loSmarty->display('Plantillas/ERP4040.tpl');
   }
   
   function fxDetalle() {
      global $loSmarty;
      $lo = new CMtoPedidos();
      $lo->pcCodPed = $_REQUEST['pcCodPed'];
      $_SESSION['pcCodPed'] = $lo->pcCodPed;
      $llOk = $lo->omInitPedidosRepProd1();	  
      if (!$llOk) { // Error
	     fxAlert(fxXmlError($lo->pcError));
         fxScreen();
      } else {
         $_SESSION['paDataProd'] = $lo->paDataProd;
         $loSmarty->assign('scBehavior', '1');
         $loSmarty->assign('saDataProd', $_SESSION['paDataProd']);
         $loSmarty->display('Plantillas/ERP4040.tpl');
      }
   }
   
   function fxAsignar() {
      global $loSmarty;
      $lo = new CMtoPedidos();
      $lo->pcCodPed  = $_SESSION['pcCodPed'];
      $lo->pcCodProd = $_REQUEST['pcCodProd'];
      $_SESSION['pcCodProd'] = $lo->pcCodProd;
      $llOk = $lo->omInitPedidosAsignar1();
      if (!$llOk) { // Error
	     fxAlert(fxXmlError($lo->pcError));
         fxScreen();
      } else {
       $_SESSION['paCodMaq'] = $lo->paCodMaq;
       $_SESSION['paCodPer'] = $lo->paCodPer;
	   $_SESSION['pcDescri'] = $lo->pcDescri;
	   $_SESSION['pnCant'] = $lo->pnCant;
       $loSmarty->assign('scBehavior', '2');
       $loSmarty->assign('scCodPed', $lo->pcCodPed);
       $loSmarty->assign('scCodProd', $lo->pcCodProd);
	   $loSmarty->assign('saCodMaq', $lo->paCodMaq);
	   $loSmarty->assign('saCodPer', $lo->paCodPer);
       $loSmarty->assign('scDescri', $lo->pcDescri);
	   $loSmarty->assign('scProc', $lo->pcProc);
       $loSmarty->assign('snCant', $lo->pnCant);
       $loSmarty->display('Plantillas/ERP4040.tpl');
      }
   }
   function fxGrabar() {
      global $loSmarty;
      $lo = new CMtoPedidos();
      $lo->pcCodPed = $_SESSION['pcCodPed'];
      $lo->pcCodProd = $_SESSION['pcCodProd'];
	  $lo->pcCodUsu = $_SESSION['gcCodUsu'];
	  $lo->pcCodMaq = $_REQUEST['pcCodMaq'];
	  $lo->pcCodPer = $_REQUEST['pcCodPer'];
      $llOk = $lo->omGrabarAsignado1();
      if (!$llOk) { // Error
	     fxAlert(fxXmlError($lo->pcError));
         fxDetalle();
      } else {
        fxAlert('GRABACION CONFORME');
        fxHeader('ERP4040.php');      
      }
   }
?>