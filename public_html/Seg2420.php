<?php
   require_once 'Clases/CBase.php';
   require_once 'Libs/Smarty.class.php';
   require_once 'Clases/CConsultasXls.php';
   require_once 'Clases/CMtoPerfiles.php';
   session_cache_limiter();
   session_start();
   $loSmarty = new Smarty;
   if (!fxValidarPermiso('SEG2420', 'Mnu0000.php?Id=6000'))
      return;
   $lcEvaTip = 'E';
   if (!fxInitSession()) {
      fxCallIndex('NO HA INICIADO SESIÓN');
   } else if (@$_POST['Buscar'] == 'Buscar') {
      $_SESSION['pcVolver'] = 'Seg2420';
      $_SESSION['pcEvaTip'] = $lcEvaTip;
      fxHeader('Seg9006.php'); 
   } else if (@$_POST['Aplicar'] == 'Aplicar' or @$_GET['Id'] == 'Aceptar') {
      $lo = new CConsultasXls();
      if (@$_GET['Id'] == 'Aceptar') 
         $lo->pcNroEva = $_SESSION['pcNroEva'];
      else  
         $lo->pcNroEva = $lcEvaTip.$_REQUEST['pcNroEva'];
      $llOk = $lo->omPruebaEficienciaDeTerreno();
      if (!$llOk) {
         $lcMessage = fxXmlError($lo->pcError);
         $loSmarty->assign('scBehavior', '0');
         $loSmarty->display('Plantillas/Seg2420.tpl');
         fxAlert($lcMessage);
      }
   } else {
      $loSmarty->assign('scBehavior', '0');
      $loSmarty->assign('scEvaTip', $lcEvaTip);
      $loSmarty->display('Plantillas/Seg2420.tpl');
   }
?>