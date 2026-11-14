<?php
   // Registro y mantenimiento de licencias ferroviarias
   require_once 'Libs/Smarty.class.php';
   require_once 'Clases/CMtoLicencias.php';
   require_once 'Clases/CMtoPerfiles.php';
   session_cache_limiter();
   session_start();
   $loSmarty = new Smarty;
   if (!fxValidarPermiso('SEG1420', 'Mnu0000.php?Id=2000'))
      return;
   if (!fxInitSession()) {
      fxCallIndex('NO HA INICIADO SESIÓN');
   } else if (@$_POST['Boton2'] == 'Nuevo') {
      $laTmp = $_REQUEST['paData'];
      $i = fxCount($laTmp) + 1;
      $laTmp[] = array($i, '', '', '', '', '', '', $_SESSION['pcCarDis']);
      $_SESSION['paData'] = $laTmp;
      fxScreen();
   } else if (@$_POST['Boton2'] == 'Eliminar') {   // Elimina fila
      fxEliminar();
   } else if (@$_POST['Boton1'] == 'Aplicar' or @$_GET['Id'] == 'Aceptar') {
      fxAplicar();
   } else if (@$_POST['Boton1'] == 'Salir' or @$_GET['Id'] == 'Aceptar') {
      fxHeader('Mnu0000.php?Id=2000');
   } else if (@$_POST['Boton3'] == 'Grabar') {
      fxGrabarRegistro();
   } else if (@$_POST['Boton3'] == 'Cancelar') {
      $loSmarty->assign('saCodPer', $_SESSION['paCodPer']);
      $loSmarty->assign('scBehavior', '0');
      $loSmarty->display('Plantillas/Seg1420.tpl');
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
      $llOk = $lo->omAplicar();
      if (!$llOk) {
         fxAlert(fxXmlError($lo->pcError));
         $loSmarty->assign('saCodPer', $_SESSION['paCodPer']);
         $loSmarty->assign('scBehavior', '0');
         $loSmarty->display('Plantillas/Seg1420.tpl');
      } else {
         $_SESSION['pcNombre'] = $lo->pcNombre;
         $_SESSION['pcCarDis'] = $lo->pcCarDis;
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
      $llOk = $lo->omInitRegistro('F');
      if (!$llOk) {
         fxHeader('Mnu0000.php?Id=2000', fxXmlError($lo->pcError));
      } else {
         $_SESSION['paCodEmp'] = $lo->paCodEmp;
         $_SESSION['paCodPer'] = $lo->paCodPer;
         $_SESSION['paEstado'] = $lo->paEstado;
         $_SESSION['paTipLic'] = $lo->paTipLic;
         $_SESSION['paCargo']  = $lo->paCargo;
         $loSmarty->assign('saCodPer', $_SESSION['paCodPer']);
         $loSmarty->assign('scBehavior', '0');
         $loSmarty->display('Plantillas/Seg1420.tpl');
      }
   }
   
   function fxGrabarRegistro() {
      global $loSmarty;
      $_SESSION['paData'] = $_REQUEST['paData'];
      $lo = new CMtoLicencias();
      $lo->pcCodPer = $_SESSION['pcCodPer'];
      $lo->paData   = $_SESSION['paData'];
      $lo->pcCodUsu = $_SESSION['gcCodUsu'];
      $llOk = $lo->omGrabarRegistro();
      if (!$llOk) { // Error
         fxScreen();
         fxAlert(fxXmlError($lo->pcError));
      } else {
         $loSmarty->assign('saCodPer', $_SESSION['paCodPer']);
         $loSmarty->assign('scBehavior', '0');
         $loSmarty->display('Plantillas/Seg1420.tpl');
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
      fxscreen();
   }
   
   function fxScreen() {
      global $loSmarty;
      $loSmarty->assign('scCodPer', $_SESSION['pcCodPer']);
      $loSmarty->assign('scDesSec', $_SESSION['pcDesSec']);
      $loSmarty->assign('saCodEmp', $_SESSION['paCodEmp']);
      $loSmarty->assign('scNombre', $_SESSION['pcNombre']);
      $loSmarty->assign('scNroDni', $_SESSION['pcNroDni']);
      $loSmarty->assign('saEstado', $_SESSION['paEstado']);
      $loSmarty->assign('saTipLic', $_SESSION['paTipLic']);
      $loSmarty->assign('saCargo',  $_SESSION['paCargo']);
      $loSmarty->assign('saData',   $_SESSION['paData']);
      $loSmarty->assign('scBehavior', '1');
      $loSmarty->display('Plantillas/Seg1420.tpl');
   }
?>