<?php
   // Reporte Incidentes
   require_once 'Libs/Smarty.class.php';
   require_once 'Clases/CIncidentes.rep.php';
   require_once 'Clases/CMtoPerfiles.php';
   session_cache_limiter();
   session_start();
   $loSmarty = new Smarty;
   if (!fxValidarPermiso('SEG2520', 'Mnu0000.php?Id=4000'))
      return;
   if (!fxInitSession()) {
      fxCallIndex('NO HA INICIADO SESIÓN');
   } else if (@$_POST['Boton1'] == 'Buscar') {
      $_SESSION['pcVolver'] = 'Seg2510';
      fxReporte();
   } else if (@$_POST['Boton1'] == 'Salir') {
      fxHeader('Mnu0000.php?Id=4000');
   } else if (@$_POST['Boton1'] == 'Aplicar' or @$_GET['Id'] == 'Aceptar') {
      fxAplicar();
   } else if (@$_POST['Boton2'] == 'Reporte') {
      fxReporte();
   } else if (@$_POST['Boton2'] == 'Cancelar') {
      fxInit();
   } else {
      fxInit();
   }

   function fxAplicar() {
      global $loSmarty;      
      $lo = new CIncidentesRep();
     if (@$_GET['Id'] == 'Aceptar') 
         $lo->pcNroInc = $_SESSION['pcNroInc'];
      else  
         $lo->pcNroInc = $_REQUEST['pcNroInc'];
      $llOk = $lo->omAplicar();
     if (!$llOk) {
         $lcMessage = fxXmlError($lo->pcError);
         $loSmarty->assign('scBehavior', '0');
         $loSmarty->display('Plantillas/Seg2510.tpl');
         fxAlert($lcMessage);
      } else {
         $_SESSION['pmDescri'] = $lo->pmDescri;
         $loSmarty->assign('scBehavior', '1');
         $loSmarty->assign('smDescri', $_SESSION['pmDescri']);
         $loSmarty->assign('scNroInc', $lo->pcNroInc);
         $loSmarty->display('Plantillas/Seg2510.tpl');
      }
   }
   
   function fxReporte() {
      global $loSmarty;
      $lo = new CIncidentesRep();
      //$lo->pcNroInc = $_REQUEST['pcNroInc'];
      $lo->pcNroInc = '000012';
      $llOk = $lo->omInformeInvestigacion();
      if (!$llOk) {
         $lcMessage = fxXmlError($lo->pcError);
         fxInit();
         fxAlert($lcMessage);
      } else {
         fxAlert('Salioooooooo');
         fxInit();
      }
   }

   function fxInit() {
      global $loSmarty;
      $loSmarty->assign('scBehavior', '0');
      $loSmarty->display('Plantillas/Seg2510.tpl');
   }
?>