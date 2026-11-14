<?php
   require_once "Libs/Smarty.class.php";
   require_once "Clases/CMtoEmpleados.php";
   session_cache_limiter();
   session_start();
   $lcVolver = $_SESSION['pcVolver'].'.php';
   $loSmarty = new Smarty;
   if (!fxInitSession()) {
      fxCallIndex('NO HA INICIADO SESIÓN');
   } else if ((@$_POST['Aceptar'] == "Aceptar")) {
      $lcCodEmp = $_REQUEST["pcCodPer"];
      if (empty($lcCodEmp)) {
         fxInit();
         fxAlert("NO HA ELEGIDO EMPLEADO");
      } else {
         $_SESSION['pcCodPer'] = $lcCodEmp;
         fxHeader($lcVolver.'?Id=Aceptar');
      }
   } else if ((@$_POST['Aplicar'] == "Aplicar")) {
      $lo = new CMtoEmpleados();
      $lo->pcNombre = $_REQUEST["pcNombre"];
      $llOk = $lo->omBuscarEmpleados();
      if (!$llOk) { // Error
         $lcMensaje = fxXmlError($lo->pcError);
         fxInit();
         fxAlert($lcMensaje);
      } else {
         $loSmarty->assign("scBehavior", '1'); 
         $loSmarty->assign("saCodPer", $lo->paCodPer);
         $loSmarty->assign("scVolver", $lcVolver);
         $loSmarty->display("Plantillas/Seg9005.tpl");
      }
   } else {
      fxInit();
   }
   
   function fxInit() {
      global $loSmarty, $lcVolver;
      $loSmarty->assign("scBehavior", '0');
      $loSmarty->assign("scVolver", $lcVolver);
      $loSmarty->display("Plantillas/Seg9005.tpl");
   }
?>