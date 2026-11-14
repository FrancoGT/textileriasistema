<?php
   // Registro y mantenimiento de personal operativo
   require_once 'Libs/Smarty.class.php';
   require_once 'Clases/CMtoCtasXCobrar.php';
   
   session_cache_limiter();
   session_start();
   $loSmarty = new Smarty;
   /*if (!fxValidarPermiso('ERP6030', 'Mnu0000.php?Id=1000'))
      return;*/
   if (!fxInitSession()) {
      fxCallIndex('NO HA INICIADO SESIÓN');
   } elseif ($_POST['Boton0'] == 'Salir') {
      fxHeader('Mnu0000.php?Id=1000');
   } elseif ($_POST['Boton0'] == 'Aplicar') {
      fxAplicar();
   } elseif ($_POST['Boton1'] == 'Asignar') {
      fxAsignar();
   } elseif ($_POST['Boton1'] == 'Cancelar') {
      fxScreen();
   } elseif ($_POST['Boton1'] == 'Grabar') {
      fxGrabar();
   } elseif ($_POST['Boton2'] == 'Cancelar') {
      fxDetalle();
   } else {
   //INIT
      $lo = new CMtoCtasXCobrar();
      echo "**";
      $llOk = $lo->omInitPedidosRepEje();
      echo "**";
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
      $loSmarty->display('Plantillas/ERP6030.tpl');
   }
   
   function fxAplicar() {
      global $loSmarty;
      $lo = new CMtoCtasXCobrar();
      $lo->pcCodPed = $_REQUEST['pcCodPed'];
      $_SESSION['pcCodPed'] = $lo->pcCodPed;
      $llOk = $lo->omInitPedidosCtaCobrar();  
      if (!$llOk) { // Error
	     fxAlert(fxXmlError($lo->pcError));
         fxScreen();
      } else {
         
         $loSmarty->assign('scBehavior', '1');
         $loSmarty->assign('scCodPed', $_SESSION['pcCodPed']);
         $loSmarty->assign('snTotal', $lo->pnTotal);
         $loSmarty->display('Plantillas/ERP6030.tpl');
      }
       
   }
   
   function fxAsignar() {
      global $loSmarty;
      $lo = new CMtoCtasXCobrar();
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
       $loSmarty->display('Plantillas/ERP6030.tpl');
      }
   }
   function fxGrabar() {
      global $loSmarty;
      $lo = new CMtoCtasXCobrar();
      $lo->pcCodPed = $_SESSION['pcCodPed'];
      
      $lo->pcCodUsu = $_SESSION['gcCodUsu'];      
      $lo->pnLetra  = $_REQUEST['pnLetra'];
      $lo->pnTotal  = $_REQUEST['pnTotal'];
      $llOk = $lo->omGrabarLetra();
      if (!$llOk) { // Error
	     fxAlert(fxXmlError($lo->pcError));
         fxDetalle();
      } else {
        fxAlert('GRABACION CONFORME');
        fxHeader('ERP6030.php');      
      }
   }
?>