<?php
   // Registro y mantenimiento de personal operativo
   require_once 'Libs/Smarty.class.php';
   require_once 'Clases/CMtoPedidos.php';
   require_once 'Clases/CDocRTF.php';
   require_once 'Clases/CMtoPerfiles.php';
   session_cache_limiter();
   session_start();
   $loSmarty = new Smarty;
   if (!fxValidarPermiso('ERP2020', 'Mnu0000.php?Id=1000'))
      return;
   if (!fxInitSession()) {
      fxCallIndex('NO HA INICIADO SESIÓN');
   } elseif ($_POST['Boton0'] == 'Salir') {
      fxHeader('Mnu0000.php?Id=1000');
   } elseif ($_POST['Boton0'] == 'Detalle') {
      fxDetalle();
   } else {
   //INIT
      $lo = new CMtoPedidos();
      $llOk = $lo->omInitPedidosRep();
      if (!$llOk) { // Error
         fxHeader('Mnu0000.php?Id=1000', fxXmlError($lo->pcError));
      } else {
         $_SESSION['paDataPed'] = $lo->paDataPed;
         fxScreen();
      }
   }
   
   function fxScreen() {
      global $loSmarty;
      $loSmarty->assign('scBehavior', '0');
      $loSmarty->assign('saDataPed', $_SESSION['paDataPed']);
      $loSmarty->display('Plantillas/ERP2020.tpl');
   }
   
   function fxDetalle() {
      global $loSmarty;
      $lo = new CDocRTF();
	  $lo->pcCodUsu=$_SESSION['gcCodUsu'];
	  $lo->pcCodPed=$_REQUEST['pcCodPed'];
      $llOk = $lo->omPedido1();
      if (!$llOk) {            
         fxAlert(fxXmlError($lo->pcError));
		 fxHeader('ERP2020.php');
      }else{
         $loSmarty->assign('scFilRet', $lo->pcFilRet);
		 $loSmarty->assign('scCodPed',$lo->pcCodPed );
         $loSmarty->assign('scBehavior', '1');
         $loSmarty->display('Plantillas/ERP2020.tpl');
     }
   }
 ?>
