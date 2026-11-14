<?php
   require_once "Libs/Smarty.class.php";
   require_once "Clases/CMtoProyectos.php";
   session_start();
   $lcVolver = $_SESSION['pcVolver'].'.php';
   $loSmarty = new Smarty;
   if (!fxInitSession()) {
      fxCallIndex('NO HA INICIADO SESIÓN');
   } else if ((@$_POST['Aceptar'] == "Aceptar")) {
      $lcCodPry = $_REQUEST["pcCodPry"];
      if (empty($lcCodPry)) {
         $loSmarty->assign("scAviso", "NO HA ELEGIDO PROYECTO");
         $loSmarty->assign("scVolver", "Seg9001.php");
         $loSmarty->display("Plantillas/SIS1000.tpl");
      } else {
         $_SESSION['pcCodPry'] = $lcCodPry;
         //die("Location: ".$lcVolver."?Id=Aceptar");
         fxHeader($lcVolver.'?Id=Aceptar');
      }
   } else if ((@$_POST['Aplicar'] == "Aplicar")) {
      $lo = new CMtoProyectos();
      $lo->pcDescri = $_REQUEST["pcDescri"];
      $llOk = $lo->omBuscarProyectos();
      if (!$llOk) { // Error
         $loSmarty->assign("scAviso", $lo->pcError);
         $loSmarty->assign("scVolver", "Seg9001.php");
         $loSmarty->display("Plantillas/SIS1000.tpl");
      } else {
         $loSmarty->assign("scBehavior", '1'); 
         
         $loSmarty->assign("saCodPry", $lo->paCodPry);
         $loSmarty->assign("scVolver", $lcVolver);
         $loSmarty->display("Plantillas/Seg9001.tpl");
      }
   } else {
      $loSmarty->assign("scBehavior", '0');
      $loSmarty->assign("scVolver", $lcVolver);
      $loSmarty->display("Plantillas/Seg9001.tpl");
   }
?>
