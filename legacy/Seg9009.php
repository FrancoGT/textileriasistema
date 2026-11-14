<?php
   // Consulta de incidentes
   require_once 'Libs/Smarty.class.php';
   require_once 'Clases/CIncidentesFerroviarios.php';
   session_cache_limiter();
   session_start();
   $loSmarty = new Smarty;
   if (!fxInitSession()) {
      fxCallIndex('NO HA INICIADO SESIÓN');
   } else if ((@$_POST['Boton2'] == 'Aceptar')) {
      $lcNumero = $_REQUEST['pcNumero'];
      if (empty($lcNumero)) {
         fxScreen();
         fxAlert('NO HA ELEGIDO NUMERO DE INCIDENTE FERROVIARIO');
      } else {
         $_SESSION['pcNumero'] = $lcNumero;
         fxHeader(''.$_SESSION['pcVolver'].'?Id=Aceptar');
      }
   } else if ((@$_POST['Boton1'] == 'Aplicar')) {
      $_SESSION['pdFecIni'] = $_REQUEST['pdFecIni'];
      $_SESSION['pdFecFin'] = $_REQUEST['pdFecFin'];
      fxAplicar();
   } else if ((@$_POST['Boton1'] == 'Salir')) {
      fxHeader(''.$_SESSION['pcVolver']);
   } else if ((@$_POST['Boton2'] == 'Cancelar')) {
      fxScreen0();
   } else {
      $_SESSION['pdFecFin'] = date('Y-m-d');
      $_SESSION['pdFecIni'] = fxAddDate($_SESSION['pdFecFin'], -30);
      fxScreen0();
   }

   // Funciones auxiliares
   function fxAplicar() {
      global $loSmarty;
      $lo = new CIncidentesFerroviarios();
      $lo->pdFecIni = $_SESSION['pdFecIni'];
      $lo->pdFecFin = $_SESSION['pdFecFin'];
      $llOk = $lo->omConsultar();
      if (!$llOk) {
         fxScreen0();
         fxAlert(fxXmlError($lo->pcError));
      } else {
         $_SESSION['paData'] = $lo->paData;
         fxScreen();
      }
   }
   
   function fxScreen0() {
      global $loSmarty;
      $loSmarty->assign('sdFecIni', $_SESSION['pdFecIni']);
      $loSmarty->assign('sdFecFin', $_SESSION['pdFecFin']);
      $loSmarty->assign('scBehavior', '0');
      $loSmarty->display('Plantillas/Seg9009.tpl');
   }

   function fxScreen() {
      global $loSmarty;
      $loSmarty->assign('sdFecIni', $_SESSION['pdFecIni']);
      $loSmarty->assign('sdFecFin', $_SESSION['pdFecFin']);
      $loSmarty->assign('saData', $_SESSION['paData']);
      $loSmarty->assign('scBehavior', '1');
      $loSmarty->display('Plantillas/Seg9009.tpl');
   }
?>