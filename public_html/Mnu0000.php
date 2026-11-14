<?php
   require_once 'Libs/Smarty.class.php';
   require_once "Clases/CLogin.php";
   session_start();
   $loSmarty = new Smarty;
   if (!isset($_REQUEST["pcCodigo"]) || !isset($_REQUEST["pcClave"])) {
      if (!fxInitSession()) {
         fxCallIndex("NO HA INICIADO SESION");
      } else {
         $loSmarty->assign("scNombre", $_SESSION["gcNombre"]);
         $loSmarty->assign("scCodigo", $_SESSION["gcCodUsu"]);
         $loSmarty->assign("scTipo", $_SESSION["gcTipo"]);
         $loSmarty->assign("scNivel", @$_GET['Id']);
         $loSmarty->display("Plantillas/Mnu0000.tpl");
      }
   } else {      
      $lcCodigo = $_REQUEST["pcCodigo"];
      $lcClave = $_REQUEST["pcClave"];
      $lo = new CLogin();
      $lo->pcCodigo = $lcCodigo;
      $lo->pcClave = $lcClave;
      $llOk = $lo->omLogin();
      if (!$llOk) { // Error                                        
         $loSmarty->assign("scNivel", "0000");
         $loSmarty->display("Plantillas/Index.tpl");
         fxAlert($lo->pcError);
      } else {         
         $_SESSION['gcCodUsu'] = $lo->pcCodigo;
         $_SESSION['gcNombre'] = $lo->pcNombre; 
         if ($lo->pnCambio == 1){
            $loSmarty->assign('scNombre', $lo->pcNombre);
            $loSmarty->display("Plantillas/Mnu1000.tpl");
         } else {
            $loSmarty->assign('scNivel', '0000');
            $loSmarty->assign('scCodigo', $lo->pcCodigo);
            $loSmarty->assign('scNombre', $lo->pcNombre);
            $loSmarty->display("Plantillas/Mnu0000.tpl");
         }
      }
   }
?>