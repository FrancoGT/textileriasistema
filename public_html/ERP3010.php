<?php
   // Registro y mantenimiento de personal operativo
   require_once 'Libs/Smarty.class.php';
   require_once 'Clases/CMtoPedidos.php';
   require_once 'Clases/CMtoPerfiles.php';
   session_cache_limiter();
   session_start();
   $loSmarty = new Smarty;
   if (!fxValidarPermiso('ERP3010', 'Mnu0000.php?Id=1000'))
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
      fxScreen();
   } elseif ($_POST['Boton2'] == 'Grabar') {
      fxGrabar();
   } elseif ($_POST['Boton2'] == 'Cancelar') {
      fxHeader('ERP3010.php');
   } elseif ($_POST['BotonM'] == 'Agregar') {
      fxAgregarM();
   } elseif ($_POST['BotonP'] == 'Agregar') {
      fxAgregarP();
   } else {
   //INIT
      $lo = new CMtoPedidos();
      $llOk = $lo->omInitPedidosRep3010();
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
      $loSmarty->display('Plantillas/ERP3010.tpl');
   }
   
   function fxDetalle() {
      global $loSmarty;
      $lo = new CMtoPedidos();
      $lo->pcCodPed = $_REQUEST['pcCodPed'];
      $_SESSION['pcCodPed'] = $lo->pcCodPed;
      $llOk = $lo->omInitPedidosRepProd();
      if (!$llOk) { // Error
	     fxAlert(fxXmlError($lo->pcError));
         fxScreen();
      } else {
         $_SESSION['paDataProd'] = $lo->paDataProd;
         $loSmarty->assign('scBehavior', '1');
         $loSmarty->assign('saDataProd', $_SESSION['paDataProd']);
         $loSmarty->display('Plantillas/ERP3010.tpl');
      }
   }
   
   function fxAsignar() {
      global $loSmarty;
      $lo = new CMtoPedidos();
      $lo->pcCodPed  = $_SESSION['pcCodPed'];
      $lo->pcCodProd = $_REQUEST['pcCodProd'];
      $_SESSION['pcCodProd'] = $lo->pcCodProd;
      $llOk = $lo->omInitPedidosAsignar();
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
       $loSmarty->assign('snCant', $lo->pnCant);
       $loSmarty->display('Plantillas/ERP3010.tpl');
      }
   }
   function fxAgregarM() {
      global $loSmarty;
	  $laData=$_REQUEST['paDataM'];
	  $laData1=$_REQUEST['paDataP'];
      $j = count($laData) + 1;
	  $lo = new CMtoPedidos();
	  $lo->pcCodMaq = $_REQUEST['pcCodMaq'];
      $lo->omAgregarM();
      $laData[$j] = array($lo->paDataMaq[0][0], $lo->paDataMaq[0][1], $lo->paDataMaq[0][2]);
	  $_SESSION['paDataM']=$laData;
	  $_SESSION['paDataP']=$laData1;
      $loSmarty->assign('scBehavior', '2'); 
       $loSmarty->assign('scCodPed', $_SESSION['pcCodPed']);
       $loSmarty->assign('scCodProd', $_SESSION['pcCodProd']);
	   $loSmarty->assign('saCodMaq', $_SESSION['paCodMaq']);
	   $loSmarty->assign('saCodPer', $_SESSION['paCodPer']);
       $loSmarty->assign('scDescri', $_SESSION['pcDescri']);
       $loSmarty->assign('snCant', $_SESSION['pnCant']);
	   $loSmarty->assign('saDataM', $_SESSION['paDataM']);
	   $loSmarty->assign('saDataP', $_SESSION['paDataP']);
       $loSmarty->display('Plantillas/ERP3010.tpl');
}
   
   function fxAgregarP() {
      global $loSmarty;
	  $laData=$_REQUEST['paDataM'];
	  $laData1=$_REQUEST['paDataP'];
      $i = count($laData1) + 1;
	  $lo = new CMtoPedidos();
	  $lo->pcCodPer = $_REQUEST['pcCodPer'];
      $lo->omAgregarP();
      $laData1[$i] = array($lo->paDataPer[0][0], $lo->paDataPer[0][1]);
	  $_SESSION['paDataM']=$laData;
	  $_SESSION['paDataP']=$laData1;
      $loSmarty->assign('scBehavior', '2'); 
       $loSmarty->assign('scCodPed', $_SESSION['pcCodPed']);
       $loSmarty->assign('scCodProd', $_SESSION['pcCodProd']);
	   $loSmarty->assign('saCodMaq', $_SESSION['paCodMaq']);
	   $loSmarty->assign('saCodPer', $_SESSION['paCodPer']);
       $loSmarty->assign('scDescri', $_SESSION['pcDescri']);
       $loSmarty->assign('snCant', $_SESSION['pnCant']);
	   $loSmarty->assign('saDataM', $_SESSION['paDataM']);
	   $loSmarty->assign('saDataP', $_SESSION['paDataP']);
       $loSmarty->display('Plantillas/ERP3010.tpl');
   }
   function fxGrabar() {
      global $loSmarty;
      $lo = new CMtoPedidos();
      $lo->paDataM  = $_REQUEST['paDataM'];
      $lo->paDataP  = $_REQUEST['paDataP'];
      $lo->pcCodPed = $_SESSION['pcCodPed'];
      $lo->pcCodProd = $_SESSION['pcCodProd'];
	  $lo->pcCodUsu = $_SESSION['gcCodUsu'];
      $llOk = $lo->omGrabarAsignado();
      if (!$llOk) { // Error
	     fxAlert(fxXmlError($lo->pcError));
         fxDetalle();
      } else {
        fxAlert('GRABACION CONFORME');
        fxHeader('ERP3010.php');      
      }
   }
?>
