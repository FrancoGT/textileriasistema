<?php
   require_once "Libs/Smarty.class.php";
   require_once "Clases/CMtoEmpresas.php";
   session_start();
   $lcVolver = $_SESSION['pcVolver'].'.php';
   $loSmarty = new Smarty;
   if (!fxInitSession()) {
      fxCallIndex('NO HA INICIADO SESIÓN');
   } else if ((@$_POST['Aceptar'] == "Aceptar")) {
      $lcCodEmp = $_REQUEST["pcCodEmp"];
      if (empty($lcCodEmp)) {
         $loSmarty->assign("scAviso", "NO HA ELEGIDO EMPRESA");
         $loSmarty->assign("scVolver", "Seg9002.php");
         $loSmarty->display("Plantillas/SIS1000.tpl");
      } else {
         $_SESSION['pcCodEmp'] = $lcCodEmp;
         fxHeader($lcVolver.'?Id=Aceptar');
      }
   } else if ((@$_POST['Aplicar'] == "Aplicar")) {
      $lo = new CMtoEmpresas();
      $lo->pcDescri = $_REQUEST["pcDescri"];
      $llOk = $lo->omBuscarEmpresas();
      if (!$llOk) { // Error
         $loSmarty->assign("scAviso", $lo->pcError);
         $loSmarty->assign("scVolver", "Seg9002.php");
         $loSmarty->display("Plantillas/SIS1000.tpl");
      } else {
         $loSmarty->assign("scBehavior", '1'); 
         $loSmarty->assign("saCodEmp", $lo->paCodEmp);
         $loSmarty->assign("scVolver", $lcVolver);
         $loSmarty->display("Plantillas/Seg9002.tpl");
      }
   } else {
      $loSmarty->assign("scBehavior", '0');
      $loSmarty->assign("scVolver", $lcVolver);
      $loSmarty->display("Plantillas/Seg9002.tpl");
   }
?>
