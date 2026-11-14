<?php
   // Menu principal. El formulario de inicio de sesion lo atiende App\Controller\LoginController.
   require_once 'Libs/Smarty.class.php';
   require_once 'Clases/CBase.php';
   session_start();
   $loSmarty = new Smarty;
   if (!fxInitSession()) {
      fxCallIndex("NO HA INICIADO SESION");
   } else {
      $loSmarty->assign("scNombre", $_SESSION["gcNombre"]);
      $loSmarty->assign("scCodigo", $_SESSION["gcCodUsu"]);
      $loSmarty->assign("scTipo", $_SESSION["gcTipo"] ?? null);
      $loSmarty->assign("scNivel", @$_GET['Id']);
      $loSmarty->display('Plantillas/Mnu0000.tpl');
   }
?>
