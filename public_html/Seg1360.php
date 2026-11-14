<?php
   // Mantenimiento de tipos de licencias
   require_once 'Libs/Smarty.class.php';
   require_once 'Clases/CMtoLicencias.php';
   require_once 'Clases/CMtoPerfiles.php';
   session_cache_limiter();
   session_start();
   $loSmarty = new Smarty;
   if (!fxValidarPermiso('SEG1360', 'Mnu0000.php?Id=1000'))
      return;
   if (!fxInitSession()) {
      fxCallIndex('NO HA INICIADO SESIÓN');
   } elseif (@$_POST['Boton1'] == 'Nuevo') {
      fxNuevo();
   } elseif (@$_POST['Boton1'] == 'Eliminar') {
      fxEliminar();
   } elseif (@$_POST['Boton2'] == 'Grabar') {
      fxGrabar();
   } elseif (@$_POST['Boton2'] == 'Salir') {
      fxHeader('Mnu0000.php?Id=1000');
   } else {
      fxInit();
   }

   // Funciones auxiliares
   function fxInit() {
      $lo = new CMtoLicencias();
      $llOk = $lo->omInitLicencias();
      if (!$llOk) { // Error
         fxAlert(fxXmlError($lo->pcError));
         fxHeader('Mnu0000.php?Id=1000');
      } else {
         $_SESSION['paData'] = $lo->paData;
         $_SESSION['paEstado'] = $lo->paEstado;
         fxScreen();
      }
   }
   
   function fxScreen() {
      global $loSmarty;
      $loSmarty->assign('saData',   $_SESSION['paData']);
      $loSmarty->assign('saEstado', $_SESSION['paEstado']);
      $loSmarty->display('Plantillas/Seg1360.tpl');
   }

   function fxNuevo() {
      $laData = $_REQUEST['paData'];
      $i = count($laData) + 1;
      $laData[$i]= array($i, '', '', 'A', '', 0);
      $_SESSION['paData'] = $laData;
      fxScreen();
   }

   function fxEliminar() {
      $laData = $_REQUEST['paData'];
      if (isset($_REQUEST['pcTipLic'])) {
         $laTipLic = array($_REQUEST['pcTipLic']);
         $laData = fxEliminarFila($laData, $laTipLic);
         $_SESSION['paData'] = $laData;
      }
      fxScreen();
   }
   
   function fxGrabar() {
      global $loSmarty;
      $_SESSION['paData'] = $_REQUEST['paData'];
      $lo = new CMtoLicencias();
      $lo->paData = $_SESSION['paData'];
      $lo->pcCodUsu = $_SESSION['gcCodUsu'];
      $llOk = $lo->omGrabarTipoLicencia();
      if (!$llOk) {
         fxScreen();
         fxAlert(fxXmlError($lo->pcError));
      } else {
         fxHeader('Mnu0000.php?Id=1000', 'GRABACION CONFORME');
      }
   }
?>