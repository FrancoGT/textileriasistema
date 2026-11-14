<?php
   // Registro de Incidentes
   require_once 'Libs/Smarty.class.php';
   require_once 'Clases/CMtoTablas.php';
   require_once 'Clases/CMtoPerfiles.php';
   session_cache_limiter();
   session_start();
   $loSmarty = new Smarty;
   if (!fxValidarPermiso('SEG1350', 'Mnu0000.php?Id=1000'))
      return;
   if (!fxInitSession()) {
      fxCallIndex('NO HA INICIADO SESIÓN');
   } else if (@$_POST['Boton1'] == 'Grabar') {
      fxGrabar();
   } else if (@$_POST['Boton1'] == 'Salir') {
      fxHeader('Mnu0000.php?Id=1000');
   } else {
      fxInit();
   }

   // Funciones auxiliares
   function fxInit() {
      global $loSmarty;
      $lo = new CMtoTablas();
      $llOk = $lo->omEditarEmailAreas();
      if (!$llOk) { // Error
         fxAlert(fxXmlError($lo->pcError));
         fxHeader('Mnu0000.php?Id=1000');
      } else {
         $loSmarty->assign('saData', $lo->paData);
         $loSmarty->display('Plantillas/Seg1350.tpl');
      }
   }
      
   function fxGrabar() {
      global $loSmarty;
      $_SESSION['paData'] = $_REQUEST['paData'];
      $lo = new CMtoTablas();
      $lo->paData = $_SESSION['paData'];
      $lo->pcCodUsu = $_SESSION['gcCodUsu'];
      $llOk = $lo->omGrabarEmailAreas();
      if (!$llOk) { // Error
         $loSmarty->assign('saData', $_SESSION['paData']);
         $loSmarty->display('Plantillas/Seg1350.tpl');
         fxAlert(fxXmlError($lo->pcError));
      } else {
         $loSmarty->assign('saData', $_SESSION['paData']);
         $loSmarty->display('Plantillas/Seg1350.tpl');
         fxAlert('GRABACION CONFORME');
      }
   }
?>