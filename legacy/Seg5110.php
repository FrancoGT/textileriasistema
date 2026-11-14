<?php
   require_once 'Libs/Smarty.class.php';
   require_once 'Clases/CServicios.php';
   require_once 'Clases/CMtoPerfiles.php';
   session_cache_limiter();
   session_start();
   $loSmarty = new Smarty;
//   if (!fxValidarPermiso('SEG5110', 'Mnu0000.php?Id=9000'))
//      return;
   if (!fxInitSession()) {
      fxCallIndex('NO HA INICIADO SESIÓN');
   } else if ((@$_POST['Boton1'] == 'Enviar')) {
      fxEnviar();
   } else if (@$_POST['Boton1'] == 'Salir') {
      fxHeader('Mnu0000.php?Id=9000');
   } else {
      $loSmarty->assign('scBehavior', '0');
      $loSmarty->display('Plantillas/Seg5110.tpl');
   }

   // Funciones auxiliares
   function fxEnviar() {
      global $loSmarty;
      $lo = new CServicios();
      $llOk = $lo->omEmailRecodatorio();
      if (!$llOk) {
         fxAlert(fxXmlError($lo->pcError));
      } else {
         fxAlert('ENVIO DE EMAILS TERMINADO SATISFACTORIAMENTE');
      }
      $loSmarty->assign('scBehavior', '0');
      $loSmarty->display('Plantillas/Seg5110.tpl');
   }
?>
