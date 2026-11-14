<?php
   // Registro y mantenimiento de licencias de conducir vehiculos terrestres
   require_once 'Libs/Smarty.class.php';
   require_once 'Clases/CMtoLicencias.php';
   require_once 'Clases/CMtoPerfiles.php';
   session_cache_limiter();
   session_start();
   $loSmarty = new Smarty;
   if (!fxValidarPermiso('SEG1450', 'Mnu0000.php?Id=2000'))
      return;
   // Verifica inicio de sesion
   if (!fxInitSession()) {
      fxCallIndex('NO HA INICIADO SESIÓN');
   } else if (@$_POST['Nuevo'] == 'Nuevo') {
      $laTmp = $_REQUEST['paData'];
      $i = fxCount($laTmp) + 1;
      $laTmp[] = array($i, '', '', '', '', '', '');
      $_SESSION['paData'] = $laTmp;
      fxScreen();
   } else if (@$_POST['Grabar'] == 'Grabar') {
      fxGrabarRegistro();
   } else if (@$_POST['Eliminar'] == 'Eliminar') {
      fxEliminar();
   } else if (@$_POST['Aplicar'] == 'Aplicar' or @$_GET['Id'] == 'Aceptar') {
      fxAplicar();
   } else {
      fxInit();
   }
   
   // Funciones auxiliares
   function fxAplicar() {
      global $loSmarty;
      $lo = new CMtoLicencias();
      $lo->pcCodPer = @$_REQUEST['pcCodPer'];
      if (@$_GET['Id'] == 'Aceptar')
         $lo->pcCodPer = $_SESSION['pcCodPer'];
      $llOk = $lo->omAplicarVehiculosTerrestres();
      if (!$llOk) {
         $loSmarty->assign('saCodPer', $_SESSION['paCodPer']);
         $loSmarty->assign('scBehavior', '0');
         $loSmarty->display('Plantillas/Seg1450.tpl');
         fxAlert(fxXmlError($lo->pcError));
      } else {
         $_SESSION['pcNombre'] = $lo->pcNombre;
         $_SESSION['pcCodPer'] = $lo->pcCodPer;
         $_SESSION['pcNroDni'] = $lo->pcNroDni;
         $_SESSION['pcDesSec'] = $lo->pcDesSec;
         $_SESSION['paData']   = $lo->paData;
         fxScreen();
      }
   }

   function fxInit() {
      global $loSmarty;
      $lo = new CMtoLicencias();
      $llOk = $lo->omInitRegistro('T');
      if (!$llOk) {
         fxHeader('Mnu0000.php?Id=2000', fxXmlError($lo->pcError));
      } else {
         $_SESSION['paCodPer'] = $lo->paCodPer;
         $_SESSION['paEstado'] = $lo->paEstado;
         $_SESSION['paTipLic'] = $lo->paTipLic;
         $_SESSION['paSector'] = $lo->paSector;
         $_SESSION['paSecAut'] = $lo->paSecAut;
         $_SESSION['paRestri'] = $lo->paRestri;
         $loSmarty->assign('saCodPer', $_SESSION['paCodPer']);
         $loSmarty->assign('scBehavior', '0');
         $loSmarty->display('Plantillas/Seg1450.tpl');
      }
   }
   
   function fxGrabarRegistro() {
      global $loSmarty;
      $_SESSION['paData'] = $_REQUEST['paData'];
      $lo = new CMtoLicencias();
      $lo->pcCodPer = $_SESSION['pcCodPer'];
      $lo->paData   = $_SESSION['paData'];
      $lo->pcCodUsu = $_SESSION['gcCodUsu'];
      $llOk = $lo->omGrabarVehiculosTerrestres();
      if (!$llOk) { // Error
         fxScreen();
         fxAlert(fxXmlError($lo->pcError));
      } else {
         $loSmarty->assign('saCodPer', $_SESSION['paCodPer']);
         $loSmarty->assign('scBehavior', '0');
         $loSmarty->display('Plantillas/Seg1450.tpl');
         fxAlert('GRABACION CONFORME');
      }
   }

   function fxEliminar() {
      global $loSmarty;
      $laTmp = $_REQUEST['paData'];
      if (isset($_REQUEST['paCheck'])) {
         $laTmp = fxEliminarFila($_REQUEST['paData'], $_REQUEST['paCheck']);
      }
      $_SESSION['paData'] = $laTmp;
      fxScreen();
   }
   
   function fxScreen() {
      global $loSmarty;
      $loSmarty->assign('scBehavior', '1');
      $loSmarty->assign('scCodPer', $_SESSION['pcCodPer']);
      $loSmarty->assign('scDesSec', $_SESSION['pcDesSec']);
      $loSmarty->assign('scNroDni', $_SESSION['pcNroDni']);
      $loSmarty->assign('scNombre', $_SESSION['pcNombre']);
      $loSmarty->assign('saEstado', $_SESSION['paEstado']);
      $loSmarty->assign('saTipLic', $_SESSION['paTipLic']);
      $loSmarty->assign('saRestri', $_SESSION['paRestri']);
      $loSmarty->assign('saData',   $_SESSION['paData']);
      $loSmarty->display('Plantillas/Seg1450.tpl');
   }
?>