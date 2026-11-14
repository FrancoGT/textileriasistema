<?php
   // Registro y mantenimiento de historia del empleados
   require_once 'Libs/Smarty.class.php';
   require_once 'Clases/CMtoEmpleados.php';
   require_once 'Clases/CMtoPerfiles.php';
   session_cache_limiter();
   session_start();
   $loSmarty = new Smarty;
   if (!fxValidarPermiso('SEG1410', 'Mnu0000.php?Id=2000'))
      return;
   if (!fxInitSession()) {
      fxCallIndex('NO HA INICIADO SESIÓN');
   } else if (@$_POST['Boton2'] == 'Nuevo') {
      $laTmp = $_REQUEST['paData'];
      $i = fxCount($laTmp) + 1;
      $laTmp[] = array($i, '', '', '', '', '', '', '');
      $_SESSION['paData'] = $laTmp;
      fxScreen();
   } else if (@$_POST['Boton3'] == 'Grabar') {
      fxGrabarHistoria();
   } else if (@$_POST['Boton3'] == 'Cancelar') {
      $loSmarty->assign('saCodPer', $_SESSION['paCodPer']);
      $loSmarty->assign('scBehavior', '0');
      $loSmarty->display('Plantillas/Seg1410.tpl');
   } else if (@$_POST['Boton2'] == 'Eliminar') {
      fxEliminar();
   } else if (@$_POST['Boton1'] == 'Aplicar' or @$_GET['Id'] == 'Aceptar') {
      fxAplicar();
   } else if (@$_POST['Boton1'] == 'Salir') {
      fxHeader('Mnu0000.php?Id=2000');
   } else {
      fxInit();
   }
   
   function fxAplicar() {
      global $loSmarty;
      $lo = new CMtoEmpleados();
      $lo->pcCodPer = @$_REQUEST['pcCodPer'];
      if (@$_GET['Id'] == 'Aceptar')
         $lo->pcCodPer = $_SESSION['pcCodPer'];
      $llOk = $lo->omAplicarHistoria();
      if (!$llOk) { // Error
         fxHeader('Mnu0000.php?Id=2000', fxXmlError($lo->pcError));
      } else {
         $_SESSION['pcNombre'] = $lo->pcNombre;
         $_SESSION['pcCodPer'] = $lo->pcCodPer;
         $_SESSION['paData'] = $lo->paData;
         fxScreen();
      }
   }

   function fxInit() {
      global $loSmarty;
      $lo = new CMtoEmpleados();
      $llOk = $lo->omInitHistoria();
      if (!$llOk) { // Error
         fxHeader('Mnu0000.php?Id=2000', fxXmlError($lo->pcError));
      } else {
         $_SESSION['paCodPry'] = $lo->paCodPry;
         $_SESSION['paCodEmp'] = $lo->paCodEmp;
         $_SESSION['paCodPer'] = $lo->paCodPer;
         $_SESSION['paCargos'] = $lo->paCargos;
         $_SESSION['paCarDis'] = $lo->paCarDis;
         $_SESSION['paSector'] = $lo->paSector;
         $_SESSION['paAreas']  = $lo->paAreas;
         $loSmarty->assign('saCodPer', $_SESSION['paCodPer']);
         $loSmarty->assign('scBehavior', '0');
         $loSmarty->display('Plantillas/Seg1410.tpl');
      }
   }
   
   function fxGrabarHistoria() {
      global $loSmarty;
      $_SESSION['paData'] = $_REQUEST['paData'];
      $lo = new CMtoEmpleados();
      $lo->pcCodPer = $_SESSION['pcCodPer'];
      $lo->paData   = $_SESSION['paData'];
      $lo->pcCodUsu = $_SESSION['gcCodUsu'];
      $llOk = $lo->omGrabarHistoria();
      if (!$llOk) { // Error
         fxScreen();
         fxAlert(fxXmlError($lo->pcError));
      } else {
         $loSmarty->assign('saCodPer', $_SESSION['paCodPer']);
         $loSmarty->assign('scBehavior', '0');
         $loSmarty->display('Plantillas/Seg1410.tpl');
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
      $loSmarty->assign('saSector', $_SESSION['paSector']);
      $loSmarty->assign('scCodPer', $_SESSION['pcCodPer']);
      $loSmarty->assign('scNombre', $_SESSION['pcNombre']);
      $loSmarty->assign('saCarDis', $_SESSION['paCarDis']);
      $loSmarty->assign('saCargos', $_SESSION['paCargos']);
      $loSmarty->assign('saCodEmp', $_SESSION['paCodEmp']);
      $loSmarty->assign('saCodPry', $_SESSION['paCodPry']);
      $loSmarty->assign('saAreas',  $_SESSION['paAreas']);
      $loSmarty->assign('saData',   $_SESSION['paData']);
      $loSmarty->display('Plantillas/Seg1410.tpl');
   }
?>    