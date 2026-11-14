<?php
   // Cambio de clave
   require_once 'Libs/Smarty.class.php';
   require_once 'Clases/CLogin.php';
   session_start();
   $loSmarty = new Smarty;
   if (!fxInitSession()) {
      fxCallIndex('NO HA INICIADO SESION');
   } elseif (@$_POST['Boton1'] == 'Grabar') {
      $lo = new CLogin();
      $lo->pcCodUsu = $_SESSION['gcCodUsu'];
      $lo->pcClave  = $_REQUEST['pcClave'];
      $lo->pcClave1 = $_REQUEST['pcClave1'];
      $lo->pcClave2 = $_REQUEST['pcClave2'];
      $llOk = $lo->omCambiarClave();
      if (!$llOk) {
         fxCallIndex(fxXmlError($lo->pcError));
      } else {
         $loSmarty->assign('scNivel', '0000');
         $loSmarty->assign('scNombre', $_SESSION['gcNombre']);
         $loSmarty->assign('scCodigo', $_SESSION['gcCodUsu']);
         $loSmarty->display('Plantillas/Mnu0000.tpl');
      }
   }
?>
